<?php

/**
 * Classe ComentarisBlog - Gestió de comentaris del blog amb suport multilingüe
 *
 * Aquesta classe gestiona els comentaris del blog amb funcionalitats
 * de moderació, jerarquia (respostes) i traducció automàtica.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class ComentarisBlog {
    private $conn;
    private $taula = 'comentaris_blog';
    private $taulaLikes = 'comentaris_likes';
    
    // Constants del sistema
    const ESTATS_VALIDS = ['pendent', 'aprovar', 'publicat', 'eliminat', 'spam'];
    const TIPUS_LIKE = ['like', 'dislike'];
    const ESTAT_DEFECTE = 'pendent';
    const IDIOMA_DEFECTE = 'ca';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nou comentari
     */
    public function crear($dades) {
        $errors = $this->validar($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "INSERT INTO {$this->taula} (
                        uuid, entrada_blog_id, usuari_id, comentari_pare_id, idioma_codi,
                        contingut, contingut_traduit, nom_usuari, email_usuari, web_usuari,
                        ip_address, agent_usuari, estat
                    ) VALUES (
                        UUID(), :entrada_blog_id, :usuari_id, :comentari_pare_id, :idioma_codi,
                        :contingut, :contingut_traduit, :nom_usuari, :email_usuari, :web_usuari,
                        :ip_address, :agent_usuari, :estat
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_blog_id', $dades['entrada_blog_id']);
            $stmt->bindParam(':usuari_id', $dades['usuari_id'] ?? null);
            $stmt->bindParam(':comentari_pare_id', $dades['comentari_pare_id'] ?? null);
            $stmt->bindParam(':idioma_codi', $dades['idioma_codi'] ?? self::IDIOMA_DEFECTE);
            $stmt->bindParam(':contingut', $dades['contingut']);
            $stmt->bindParam(':contingut_traduit', isset($dades['contingut_traduit']) ? json_encode($dades['contingut_traduit']) : null);
            $stmt->bindParam(':nom_usuari', $dades['nom_usuari'] ?? null);
            $stmt->bindParam(':email_usuari', $dades['email_usuari'] ?? null);
            $stmt->bindParam(':web_usuari', $dades['web_usuari'] ?? null);
            $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->bindParam(':agent_usuari', $_SERVER['HTTP_USER_AGENT'] ?? null);
            $stmt->bindParam(':estat', $dades['estat'] ?? self::ESTAT_DEFECTE);
            
            $stmt->execute();
            $comentariId = $this->conn->lastInsertId();
            
            // Actualitzar comptador de comentaris de l'entrada si està aprovat
            if (($dades['estat'] ?? self::ESTAT_DEFECTE) === 'publicat') {
                $this->actualitzarComptadorEntrada($dades['entrada_blog_id'], 1);
            }
            
            return ['success' => true, 'id' => $comentariId];
            
        } catch (PDOException $e) {
            error_log("Error creant comentari: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Actualitzar comentari
     */
    public function actualitzar($id, $dades) {
        try {
            $camps = [];
            $parametres = [':id' => $id];
            
            $campsPermesos = [
                'contingut', 'estat', 'data_aprovacio', 'usuari_aprovacio', 'flags'
            ];
            
            foreach ($campsPermesos as $camp) {
                if (isset($dades[$camp])) {
                    $camps[] = "{$camp} = :{$camp}";
                    $parametres[":{$camp}"] = $dades[$camp];
                }
            }
            
            // Camp JSON especial
            if (isset($dades['contingut_traduit'])) {
                $camps[] = "contingut_traduit = :contingut_traduit";
                $parametres[':contingut_traduit'] = json_encode($dades['contingut_traduit']);
            }
            
            if (empty($camps)) {
                return ['success' => false, 'errors' => ['No hi ha camps per actualitzar']];
            }
            
            $sql = "UPDATE {$this->taula} SET " . implode(', ', $camps) . " WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error actualitzant comentari: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Obtenir comentari per ID
     */
    public function obtenirPerId($id) {
        try {
            $sql = "SELECT c.*, u.nom as usuari_nom, u.avatar_url as usuari_avatar
                    FROM {$this->taula} c
                    LEFT JOIN usuaris_blog u ON c.usuari_id = u.id
                    WHERE c.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $comentari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($comentari) {
                $comentari = $this->procesarCampsJSON($comentari);
            }
            
            return $comentari;
        } catch (PDOException $e) {
            error_log("Error en obtenirPerId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir comentaris d'una entrada
     */
    public function obtenirPerEntrada($entradaId, $opcions = []) {
        try {
            $sql = "SELECT c.*, 
                           u.nom as usuari_nom, u.avatar_url as usuari_avatar,
                           (SELECT COUNT(*) FROM {$this->taula} cc WHERE cc.comentari_pare_id = c.id AND cc.estat = 'publicat') as total_respostes
                    FROM {$this->taula} c
                    LEFT JOIN usuaris_blog u ON c.usuari_id = u.id
                    WHERE c.entrada_blog_id = :entrada_id";
            
            $parametres = [':entrada_id' => $entradaId];
            
            // Filtrar per estat (per defecte només publicats)
            $estat = $opcions['estat'] ?? 'publicat';
            $sql .= " AND c.estat = :estat";
            $parametres[':estat'] = $estat;
            
            // Filtrar per comentari pare (per defecte només comentaris principals)
            if (!isset($opcions['incloure_respostes']) || !$opcions['incloure_respostes']) {
                $sql .= " AND c.comentari_pare_id IS NULL";
            }
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 'c.data_creacio';
            $direccio = $opcions['direccio'] ?? 'ASC';
            $sql .= " ORDER BY {$ordenar} {$direccio}";
            
            // Limitació
            if (!empty($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            $comentaris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Processar camps JSON
            foreach ($comentaris as &$comentari) {
                $comentari = $this->procesarCampsJSON($comentari);
                
                // Carregar respostes si s'especifica
                if (!empty($opcions['incloure_respostes']) && $comentari['total_respostes'] > 0) {
                    $comentari['respostes'] = $this->obtenirRespostes($comentari['id'], $opcions);
                }
            }
            
            return $comentaris;
            
        } catch (PDOException $e) {
            error_log("Error en obtenirPerEntrada: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir respostes d'un comentari
     */
    public function obtenirRespostes($comentariPareId, $opcions = []) {
        try {
            $sql = "SELECT c.*, 
                           u.nom as usuari_nom, u.avatar_url as usuari_avatar
                    FROM {$this->taula} c
                    LEFT JOIN usuaris_blog u ON c.usuari_id = u.id
                    WHERE c.comentari_pare_id = :comentari_pare_id";
            
            $parametres = [':comentari_pare_id' => $comentariPareId];
            
            // Filtrar per estat
            $estat = $opcions['estat'] ?? 'publicat';
            $sql .= " AND c.estat = :estat";
            $parametres[':estat'] = $estat;
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 'c.data_creacio';
            $direccio = $opcions['direccio'] ?? 'ASC';
            $sql .= " ORDER BY {$ordenar} {$direccio}";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            $respostes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Processar camps JSON
            foreach ($respostes as &$resposta) {
                $resposta = $this->procesarCampsJSON($resposta);
            }
            
            return $respostes;
            
        } catch (PDOException $e) {
            error_log("Error en obtenirRespostes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Aprovar comentari
     */
    public function aprovar($id, $usuariAprovacio = null) {
        try {
            $sql = "UPDATE {$this->taula} SET 
                    estat = 'publicat', 
                    data_aprovacio = NOW(), 
                    usuari_aprovacio = :usuari_aprovacio 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':usuari_aprovacio', $usuariAprovacio);
            $stmt->execute();
            
            // Obtenir dades del comentari per actualitzar comptador
            $comentari = $this->obtenirPerId($id);
            if ($comentari) {
                $this->actualitzarComptadorEntrada($comentari['entrada_blog_id'], 1);
            }
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error aprovant comentari: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Marcar comentari com spam
     */
    public function marcarSpam($id, $usuariModerador = null) {
        try {
            $sql = "UPDATE {$this->taula} SET 
                    estat = 'spam', 
                    usuari_aprovacio = :usuari_moderador 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':usuari_moderador', $usuariModerador);
            $stmt->execute();
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error marcant spam: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Afegir/treure like a comentari
     */
    public function toggleLike($comentariId, $usuariId, $tipus = 'like') {
        try {
            // Verificar si ja existeix
            $sql = "SELECT tipus FROM {$this->taulaLikes} WHERE comentari_id = :comentari_id AND usuari_id = :usuari_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':comentari_id', $comentariId);
            $stmt->bindParam(':usuari_id', $usuariId);
            $stmt->execute();
            
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['tipus'] === $tipus) {
                    // Eliminar like/dislike existent
                    $sql = "DELETE FROM {$this->taulaLikes} WHERE comentari_id = :comentari_id AND usuari_id = :usuari_id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':comentari_id', $comentariId);
                    $stmt->bindParam(':usuari_id', $usuariId);
                    $stmt->execute();
                    
                    $action = 'removed';
                } else {
                    // Canviar tipus de like/dislike
                    $sql = "UPDATE {$this->taulaLikes} SET tipus = :tipus WHERE comentari_id = :comentari_id AND usuari_id = :usuari_id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':comentari_id', $comentariId);
                    $stmt->bindParam(':usuari_id', $usuariId);
                    $stmt->bindParam(':tipus', $tipus);
                    $stmt->execute();
                    
                    $action = 'changed';
                }
            } else {
                // Crear nou like/dislike
                $sql = "INSERT INTO {$this->taulaLikes} (comentari_id, usuari_id, tipus, ip_address) 
                        VALUES (:comentari_id, :usuari_id, :tipus, :ip_address)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':comentari_id', $comentariId);
                $stmt->bindParam(':usuari_id', $usuariId);
                $stmt->bindParam(':tipus', $tipus);
                $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
                $stmt->execute();
                
                $action = 'added';
            }
            
            // Actualitzar comptadors al comentari
            $this->actualitzarComptadorsLike($comentariId);
            
            return ['success' => true, 'action' => $action];
            
        } catch (PDOException $e) {
            error_log("Error en toggleLike: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Actualitzar comptadors de likes/dislikes
     */
    private function actualitzarComptadorsLike($comentariId) {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN tipus = 'like' THEN 1 ELSE 0 END) as total_likes,
                        SUM(CASE WHEN tipus = 'dislike' THEN 1 ELSE 0 END) as total_dislikes
                    FROM {$this->taulaLikes} 
                    WHERE comentari_id = :comentari_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':comentari_id', $comentariId);
            $stmt->execute();
            $comptadors = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $sql = "UPDATE {$this->taula} SET 
                    likes = :likes, 
                    dislikes = :dislikes 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':likes', $comptadors['total_likes'] ?? 0);
            $stmt->bindParam(':dislikes', $comptadors['total_dislikes'] ?? 0);
            $stmt->bindParam(':id', $comentariId);
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error actualitzant comptadors: " . $e->getMessage());
        }
    }
    
    /**
     * Actualitzar comptador de comentaris de l'entrada
     */
    private function actualitzarComptadorEntrada($entradaId, $increment = 1) {
        try {
            $sql = "UPDATE entrades_blog SET comptador_comentaris = comptador_comentaris + :increment WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $entradaId);
            $stmt->bindParam(':increment', $increment);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualitzant comptador entrada: " . $e->getMessage());
        }
    }
    
    /**
     * Processar camps JSON
     */
    private function procesarCampsJSON($comentari) {
        $campsJSON = ['contingut_traduit', 'flags'];
        
        foreach ($campsJSON as $camp) {
            if (!empty($comentari[$camp])) {
                $decoded = json_decode($comentari[$camp], true);
                $comentari[$camp] = $decoded !== null ? $decoded : [];
            } else {
                $comentari[$camp] = [];
            }
        }
        
        return $comentari;
    }
    
    /**
     * Validar dades de comentari
     */
    private function validar($dades) {
        $errors = [];
        
        if (empty($dades['entrada_blog_id'])) {
            $errors[] = "L'entrada del blog és obligatòria";
        }
        
        if (empty($dades['contingut'])) {
            $errors[] = "El contingut del comentari és obligatori";
        } elseif (strlen($dades['contingut']) > 5000) {
            $errors[] = "El comentari no pot superar els 5000 caràcters";
        }
        
        // Si no hi ha usuari registrat, validar dades d'usuari anònim
        if (empty($dades['usuari_id'])) {
            if (empty($dades['nom_usuari'])) {
                $errors[] = "El nom d'usuari és obligatori";
            }
            if (empty($dades['email_usuari'])) {
                $errors[] = "L'email és obligatori";
            } elseif (!filter_var($dades['email_usuari'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email no té un format vàlid";
            }
        }
        
        if (isset($dades['estat']) && !in_array($dades['estat'], self::ESTATS_VALIDS)) {
            $errors[] = "L'estat especificat no és vàlid";
        }
        
        return $errors;
    }
    
    /**
     * Obtenir comentaris pendents de moderació
     */
    public function obtenirPendents($opcions = []) {
        try {
            $sql = "SELECT c.*, 
                           u.nom as usuari_nom,
                           e.slug_base as entrada_slug,
                           et.titol as entrada_titol
                    FROM {$this->taula} c
                    LEFT JOIN usuaris_blog u ON c.usuari_id = u.id
                    LEFT JOIN entrades_blog e ON c.entrada_blog_id = e.id
                    LEFT JOIN entrades_traduccions et ON e.id = et.entrada_id AND et.idioma_codi = 'ca'
                    WHERE c.estat = 'pendent'
                    ORDER BY c.data_creacio DESC";
            
            if (!empty($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $comentaris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($comentaris as &$comentari) {
                $comentari = $this->procesarCampsJSON($comentari);
            }
            
            return $comentaris;
            
        } catch (PDOException $e) {
            error_log("Error obtenint pendents: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir estadístiques de comentaris
     */
    public function obtenirEstadistiques() {
        try {
            $sql = "SELECT 
                        estat,
                        COUNT(*) as total
                    FROM {$this->taula}
                    GROUP BY estat";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $estadistiques = [];
            foreach ($resultats as $resultat) {
                $estadistiques[$resultat['estat']] = $resultat['total'];
            }
            
            return $estadistiques;
            
        } catch (PDOException $e) {
            error_log("Error obtenint estadístiques: " . $e->getMessage());
            return [];
        }
    }
}
?>
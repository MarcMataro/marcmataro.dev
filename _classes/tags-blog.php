<?php

/**
 * Classe TagsBlog - Gestió de tags del blog amb suport multilingüe
 *
 * Aquesta classe gestiona els tags/etiquetes del blog amb funcionalitats
 * de traduccions i sistema de fallback d'idiomes.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class TagsBlog {
    private $conn;
    private $taula = 'tags_blog';
    private $taulaTraduccions = 'tags_traduccions';
    
    // Constants del sistema
    const IDIOMA_DEFECTE = 'ca';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nou tag
     */
    public function crear($dades) {
        $errors = $this->validar($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Crear tag base
            $sql = "INSERT INTO {$this->taula} (slug_base) VALUES (:slug_base)";
            $stmt = $this->conn->prepare($sql);
            
            // Assignar valor a variable per bindParam
            $slugBase = $dades['slug_base'];
            $stmt->bindParam(':slug_base', $slugBase);
            $stmt->execute();
            
            $tagId = $this->conn->lastInsertId();
            
            // Crear traduccions
            if (isset($dades['traduccions'])) {
                foreach ($dades['traduccions'] as $idioma => $traduccio) {
                    $this->crearTraduccio($tagId, $idioma, $traduccio);
                }
            }
            
            $this->conn->commit();
            return ['success' => true, 'id' => $tagId];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creant tag: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Actualitzar tag
     */
    public function actualitzar($id, $dades) {
        $errors = $this->validar($dades, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Actualitzar traduccions
            if (isset($dades['traduccions'])) {
                foreach ($dades['traduccions'] as $idioma => $traduccio) {
                    $this->actualitzarTraduccio($id, $idioma, $traduccio);
                }
            }
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error actualitzant tag: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Obtenir tag per ID amb traduccions
     */
    public function obtenirPerId($id, $idioma = self::IDIOMA_DEFECTE) {
        try {
            $sql = "SELECT t.*, 
                           tt.nom, tt.slug, tt.descripcio
                    FROM {$this->taula} t
                    LEFT JOIN {$this->taulaTraduccions} tt ON t.id = tt.tag_id AND tt.idioma_codi = :idioma
                    WHERE t.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':idioma', $idioma);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenirPerId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir tags amb traduccions
     */
    public function obtenirAmbTraducio($idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        try {
            $sql = "SELECT t.*, 
                           COALESCE(tt.nom, tt_ca.nom) as nom,
                           COALESCE(tt.slug, tt_ca.slug) as slug,
                           COALESCE(tt.descripcio, tt_ca.descripcio) as descripcio
                    FROM {$this->taula} t
                    LEFT JOIN {$this->taulaTraduccions} tt ON t.id = tt.tag_id AND tt.idioma_codi = :idioma
                    LEFT JOIN {$this->taulaTraduccions} tt_ca ON t.id = tt_ca.tag_id AND tt_ca.idioma_codi = 'ca'";
            
            return $this->executarConsultaAmbOpcions($sql, $opcions, [':idioma' => $idioma]);
        } catch (PDOException $e) {
            error_log("Error en obtenirAmbTraducio: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir tags amb totes les traduccions per edició
     */
    public function obtenirAmbTotesLesTraduccions() {
        try {
            // Obtenir tags base
            $sql = "SELECT * FROM {$this->taula} ORDER BY id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Afegir traduccions a cada tag
            foreach ($tags as &$tag) {
                $sqlTrad = "SELECT idioma_codi, nom, slug, descripcio 
                           FROM {$this->taulaTraduccions} 
                           WHERE tag_id = :id";
                $stmtTrad = $this->conn->prepare($sqlTrad);
                $stmtTrad->bindParam(':id', $tag['id']);
                $stmtTrad->execute();
                $traduccions = $stmtTrad->fetchAll(PDO::FETCH_ASSOC);
                
                // Organitzar traduccions per idioma
                $tag['traduccions'] = [];
                foreach ($traduccions as $trad) {
                    $tag['traduccions'][$trad['idioma_codi']] = [
                        'nom' => $trad['nom'],
                        'slug' => $trad['slug'],
                        'descripcio' => $trad['descripcio']
                    ];
                }
                
                // Afegir nom per defecte per mostrar
                $tag['nom'] = $tag['traduccions']['ca']['nom'] ?? 
                             $tag['traduccions']['es']['nom'] ?? 
                             $tag['traduccions']['en']['nom'] ?? 
                             'Sense nom';
                                  
                // Afegir descripció per defecte
                $tag['descripcio'] = $tag['traduccions']['ca']['descripcio'] ?? 
                                    $tag['traduccions']['es']['descripcio'] ?? 
                                    $tag['traduccions']['en']['descripcio'] ?? '';
            }
            
            return $tags;
        } catch (PDOException $e) {
            error_log("Error en obtenirAmbTotesLesTraduccions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir tags populars
     */
    public function obtenirPopulars($idioma = self::IDIOMA_DEFECTE, $limit = 20) {
        return $this->obtenirAmbTraducio($idioma, [
            'ordenar' => 't.total_entrades',
            'direccio' => 'DESC',
            'limit' => $limit
        ]);
    }
    
    /**
     * Cercar tags per nom
     */
    public function cercarPerNom($nom, $idioma = self::IDIOMA_DEFECTE, $limit = 10) {
        return $this->obtenirAmbTraducio($idioma, [
            'cercar' => $nom,
            'limit' => $limit,
            'ordenar' => 't.total_entrades',
            'direccio' => 'DESC'
        ]);
    }
    
    /**
     * Crear o obtenir tag per nom (útil per autocompletats)
     */
    public function crearOObtenir($nom, $idioma = self::IDIOMA_DEFECTE) {
        try {
            // Primer intentar trobar un tag existent
            $sql = "SELECT t.id FROM {$this->taula} t
                    JOIN {$this->taulaTraduccions} tt ON t.id = tt.tag_id
                    WHERE tt.nom = :nom AND tt.idioma_codi = :idioma";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':idioma', $idioma);
            $stmt->execute();
            
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                return ['success' => true, 'id' => $existing['id'], 'created' => false];
            }
            
            // Crear nou tag
            $slug_base = $this->generarSlug($nom);
            $resultat = $this->crear([
                'slug_base' => $slug_base,
                'traduccions' => [
                    $idioma => [
                        'nom' => $nom,
                        'slug' => $slug_base
                    ]
                ]
            ]);
            
            if ($resultat['success']) {
                $resultat['created'] = true;
            }
            
            return $resultat;
            
        } catch (PDOException $e) {
            error_log("Error en crearOObtenir: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Crear traducció de tag
     */
    private function crearTraduccio($tagId, $idioma, $traduccio) {
        try {
            $sql = "INSERT INTO {$this->taulaTraduccions} (
                        tag_id, idioma_codi, nom, slug, descripcio
                    ) VALUES (
                        :tag_id, :idioma_codi, :nom, :slug, :descripcio
                    )";
            
            $stmt = $this->conn->prepare($sql);
            
            // Assignar valors a variables per bindParam
            $nom = $traduccio['nom'];
            $slug = $traduccio['slug'];
            $descripcio = $traduccio['descripcio'] ?? null;
            
            $stmt->bindParam(':tag_id', $tagId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':descripcio', $descripcio);
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error creant traducció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualitzar traducció de tag
     */
    private function actualitzarTraduccio($tagId, $idioma, $traduccio) {
        try {
            // Verificar si ja existeix la traducció
            $sql = "SELECT id FROM {$this->taulaTraduccions} WHERE tag_id = :tag_id AND idioma_codi = :idioma_codi";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tag_id', $tagId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // Actualitzar
                $sql = "UPDATE {$this->taulaTraduccions} SET 
                        nom = :nom, slug = :slug, descripcio = :descripcio
                        WHERE tag_id = :tag_id AND idioma_codi = :idioma_codi";
            } else {
                // Crear nova
                return $this->crearTraduccio($tagId, $idioma, $traduccio);
            }
            
            $stmt = $this->conn->prepare($sql);
            
            // Assignar valors a variables per bindParam
            $nom = $traduccio['nom'];
            $slug = $traduccio['slug'];
            $descripcio = $traduccio['descripcio'] ?? null;
            
            $stmt->bindParam(':tag_id', $tagId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':descripcio', $descripcio);
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error actualitzant traducció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Executar consulta amb opcions
     */
    private function executarConsultaAmbOpcions($sql, $opcions = [], $parametresExtra = []) {
        try {
            $parametres = $parametresExtra;
            $wheres = [];
            
            // Aplicar filtres
            if (!empty($opcions['cercar'])) {
                $wheres[] = "(tt.nom LIKE :cercar OR tt_ca.nom LIKE :cercar)";
                $parametres[':cercar'] = '%' . $opcions['cercar'] . '%';
            }
            
            if (!empty($wheres)) {
                $sql .= " WHERE " . implode(' AND ', $wheres);
            }
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 't.total_entrades';
            $direccio = $opcions['direccio'] ?? 'DESC';
            $sql .= " ORDER BY {$ordenar} {$direccio}";
            
            // Limitació
            if (!empty($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error executant consulta: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar dades de tag
     */
    private function validar($dades, $id = null) {
        $errors = [];
        
        if (empty($dades['slug_base'])) {
            $errors[] = "El slug base és obligatori";
        } elseif (strlen($dades['slug_base']) > 50) {
            $errors[] = "El slug base no pot superar els 50 caràcters";
        }
        
        // Verificar slug_base únic
        if (!empty($dades['slug_base'])) {
            try {
                $sql = "SELECT id FROM {$this->taula} WHERE slug_base = :slug_base" . ($id ? " AND id != :id" : "");
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':slug_base', $dades['slug_base']);
                if ($id) {
                    $stmt->bindParam(':id', $id);
                }
                $stmt->execute();
                if ($stmt->fetch()) {
                    $errors[] = "Aquest slug base ja existeix";
                }
            } catch (PDOException $e) {
                $errors[] = "Error validant slug: " . $e->getMessage();
            }
        }
        
        // Validar traduccions
        if (isset($dades['traduccions'])) {
            foreach ($dades['traduccions'] as $idioma => $traduccio) {
                if (empty($traduccio['nom'])) {
                    $errors[] = "El nom en {$idioma} és obligatori";
                }
                if (empty($traduccio['slug'])) {
                    $errors[] = "El slug en {$idioma} és obligatori";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Generar slug automàtic a partir del nom
     */
    private function generarSlug($nom) {
        $slug = strtolower($nom);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return substr($slug, 0, 50); // Limitar a 50 caràcters
    }
    
    /**
     * Actualitzar comptador d'entrades
     */
    public function actualitzarComptadorEntrades($tagId, $increment = 1) {
        try {
            $sql = "UPDATE {$this->taula} SET total_entrades = total_entrades + :increment WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $tagId);
            $stmt->bindParam(':increment', $increment);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error actualitzant comptador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar tag (només si no té entrades associades)
     */
    public function eliminar($id) {
        try {
            // Verificar que no tingui entrades associades
            $sql = "SELECT COUNT(*) as total FROM entrades_tags WHERE tag_blog_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return ['success' => false, 'errors' => ['No es pot eliminar un tag amb entrades associades']];
            }
            
            $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error eliminant tag: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
}
?>
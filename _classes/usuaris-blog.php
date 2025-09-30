<?php

/**
 * Classe UsuarisBlog - Gestió d'usuaris/autors del blog
 *
 * Aquesta classe gestiona els usuaris del sistema de blog, incloent
 * autors, editors, traductors i altres rols del sistema.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class UsuarisBlog {
    private $conn;
    private $taula = 'usuaris_blog';
    
    // Constants del sistema
    const ROLS_VALIDS = ['administrador', 'editor', 'autor', 'col·laborador', 'traductor', 'lector'];
    const ESTATS_VALIDS = ['actiu', 'inactiu', 'suspes', 'pendent_validacio'];
    const ROL_DEFECTE = 'lector';
    const ESTAT_DEFECTE = 'pendent_validacio';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nou usuari
     */
    public function crear($dades) {
        $errors = $this->validar($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "INSERT INTO {$this->taula} (
                        uuid, nom, email, username, password_hash, avatar_url, bio, 
                        bio_traduccions, titol, xarxes_socials, idioma_per_defecte, 
                        idiomes_suportats, rol, estat, email_verificat
                    ) VALUES (
                        UUID(), :nom, :email, :username, :password_hash, :avatar_url, :bio,
                        :bio_traduccions, :titol, :xarxes_socials, :idioma_per_defecte,
                        :idiomes_suportats, :rol, :estat, :email_verificat
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nom', $dades['nom']);
            $stmt->bindParam(':email', $dades['email']);
            $stmt->bindParam(':username', $dades['username']);
            $stmt->bindParam(':password_hash', password_hash($dades['password'], PASSWORD_DEFAULT));
            $stmt->bindParam(':avatar_url', $dades['avatar_url'] ?? null);
            $stmt->bindParam(':bio', $dades['bio'] ?? null);
            $stmt->bindParam(':bio_traduccions', isset($dades['bio_traduccions']) ? json_encode($dades['bio_traduccions']) : null);
            $stmt->bindParam(':titol', $dades['titol'] ?? null);
            $stmt->bindParam(':xarxes_socials', isset($dades['xarxes_socials']) ? json_encode($dades['xarxes_socials']) : null);
            $stmt->bindParam(':idioma_per_defecte', $dades['idioma_per_defecte'] ?? 'ca');
            $stmt->bindParam(':idiomes_suportats', isset($dades['idiomes_suportats']) ? json_encode($dades['idiomes_suportats']) : json_encode(['ca']));
            $stmt->bindParam(':rol', $dades['rol'] ?? self::ROL_DEFECTE);
            $stmt->bindParam(':estat', $dades['estat'] ?? self::ESTAT_DEFECTE);
            $stmt->bindParam(':email_verificat', $dades['email_verificat'] ?? false, PDO::PARAM_BOOL);
            
            $stmt->execute();
            return ['success' => true, 'id' => $this->conn->lastInsertId()];
            
        } catch (PDOException $e) {
            error_log("Error creant usuari: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Actualitzar usuari
     */
    public function actualitzar($id, $dades) {
        $errors = $this->validar($dades, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $camps = [];
            $parametres = [':id' => $id];
            
            // Camps que es poden actualitzar
            $campsPermesos = [
                'nom', 'email', 'username', 'avatar_url', 'bio', 'titol', 
                'idioma_per_defecte', 'rol', 'estat', 'email_verificat'
            ];
            
            foreach ($campsPermesos as $camp) {
                if (isset($dades[$camp])) {
                    $camps[] = "{$camp} = :{$camp}";
                    $parametres[":{$camp}"] = $dades[$camp];
                }
            }
            
            // Camps JSON especials
            if (isset($dades['bio_traduccions'])) {
                $camps[] = "bio_traduccions = :bio_traduccions";
                $parametres[':bio_traduccions'] = json_encode($dades['bio_traduccions']);
            }
            
            if (isset($dades['xarxes_socials'])) {
                $camps[] = "xarxes_socials = :xarxes_socials";
                $parametres[':xarxes_socials'] = json_encode($dades['xarxes_socials']);
            }
            
            if (isset($dades['idiomes_suportats'])) {
                $camps[] = "idiomes_suportats = :idiomes_suportats";
                $parametres[':idiomes_suportats'] = json_encode($dades['idiomes_suportats']);
            }
            
            // Password si s'actualitza
            if (!empty($dades['password'])) {
                $camps[] = "password_hash = :password_hash";
                $parametres[':password_hash'] = password_hash($dades['password'], PASSWORD_DEFAULT);
            }
            
            if (empty($camps)) {
                return ['success' => false, 'errors' => ['No hi ha camps per actualitzar']];
            }
            
            $sql = "UPDATE {$this->taula} SET " . implode(', ', $camps) . " WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error actualitzant usuari: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Obtenir usuari per ID
     */
    public function obtenirPerId($id) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuari) {
                $usuari = $this->procesarCampsJSON($usuari);
            }
            
            return $usuari;
        } catch (PDOException $e) {
            error_log("Error en obtenirPerId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir usuari per email
     */
    public function obtenirPerEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuari) {
                $usuari = $this->procesarCampsJSON($usuari);
            }
            
            return $usuari;
        } catch (PDOException $e) {
            error_log("Error en obtenirPerEmail: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir usuari per username
     */
    public function obtenirPerUsername($username) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE username = :username";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuari) {
                $usuari = $this->procesarCampsJSON($usuari);
            }
            
            return $usuari;
        } catch (PDOException $e) {
            error_log("Error en obtenirPerUsername: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Autenticar usuari
     */
    public function autenticar($login, $password) {
        try {
            // El login pot ser email o username
            $sql = "SELECT * FROM {$this->taula} WHERE (email = :login OR username = :login) AND estat = 'actiu'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':login', $login);
            $stmt->execute();
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuari && password_verify($password, $usuari['password_hash'])) {
                return $this->procesarCampsJSON($usuari);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error en autenticar: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir usuaris amb filtres
     */
    public function obtenirAmbFiltres($opcions = []) {
        try {
            $sql = "SELECT * FROM {$this->taula}";
            $parametres = [];
            $wheres = [];
            
            // Aplicar filtres
            if (!empty($opcions['rol'])) {
                $wheres[] = "rol = :rol";
                $parametres[':rol'] = $opcions['rol'];
            }
            
            if (!empty($opcions['estat'])) {
                $wheres[] = "estat = :estat";
                $parametres[':estat'] = $opcions['estat'];
            }
            
            if (!empty($opcions['cercar'])) {
                $wheres[] = "(nom LIKE :cercar OR email LIKE :cercar OR username LIKE :cercar)";
                $parametres[':cercar'] = '%' . $opcions['cercar'] . '%';
            }
            
            if (!empty($wheres)) {
                $sql .= " WHERE " . implode(' AND ', $wheres);
            }
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 'data_creacio';
            $direccio = $opcions['direccio'] ?? 'DESC';
            $sql .= " ORDER BY {$ordenar} {$direccio}";
            
            // Limitació
            if (!empty($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            $usuaris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Processar camps JSON
            foreach ($usuaris as &$usuari) {
                $usuari = $this->procesarCampsJSON($usuari);
            }
            
            return $usuaris;
            
        } catch (PDOException $e) {
            error_log("Error en obtenirAmbFiltres: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Processar camps JSON dels usuaris
     */
    private function procesarCampsJSON($usuari) {
        $campsJSON = ['bio_traduccions', 'xarxes_socials', 'idiomes_suportats'];
        
        foreach ($campsJSON as $camp) {
            if (!empty($usuari[$camp])) {
                $decoded = json_decode($usuari[$camp], true);
                $usuari[$camp] = $decoded !== null ? $decoded : [];
            } else {
                $usuari[$camp] = [];
            }
        }
        
        return $usuari;
    }
    
    /**
     * Validar dades d'usuari
     */
    private function validar($dades, $id = null) {
        $errors = [];
        
        if (empty($dades['nom'])) {
            $errors[] = "El nom és obligatori";
        } elseif (strlen($dades['nom']) > 100) {
            $errors[] = "El nom no pot superar els 100 caràcters";
        }
        
        if (empty($dades['email'])) {
            $errors[] = "L'email és obligatori";
        } elseif (!filter_var($dades['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email no té un format vàlid";
        } elseif (strlen($dades['email']) > 191) {
            $errors[] = "L'email no pot superar els 191 caràcters";
        }
        
        if (empty($dades['username'])) {
            $errors[] = "El nom d'usuari és obligatori";
        } elseif (strlen($dades['username']) > 50) {
            $errors[] = "El nom d'usuari no pot superar els 50 caràcters";
        }
        
        if (!$id && empty($dades['password'])) {
            $errors[] = "La contrasenya és obligatòria";
        }
        
        if (isset($dades['rol']) && !in_array($dades['rol'], self::ROLS_VALIDS)) {
            $errors[] = "El rol especificat no és vàlid";
        }
        
        if (isset($dades['estat']) && !in_array($dades['estat'], self::ESTATS_VALIDS)) {
            $errors[] = "L'estat especificat no és vàlid";
        }
        
        // Verificar email i username únics
        if (!empty($dades['email'])) {
            try {
                $sql = "SELECT id FROM {$this->taula} WHERE email = :email" . ($id ? " AND id != :id" : "");
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':email', $dades['email']);
                if ($id) {
                    $stmt->bindParam(':id', $id);
                }
                $stmt->execute();
                if ($stmt->fetch()) {
                    $errors[] = "Aquest email ja està registrat";
                }
            } catch (PDOException $e) {
                $errors[] = "Error validant email: " . $e->getMessage();
            }
        }
        
        if (!empty($dades['username'])) {
            try {
                $sql = "SELECT id FROM {$this->taula} WHERE username = :username" . ($id ? " AND id != :id" : "");
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':username', $dades['username']);
                if ($id) {
                    $stmt->bindParam(':id', $id);
                }
                $stmt->execute();
                if ($stmt->fetch()) {
                    $errors[] = "Aquest nom d'usuari ja existeix";
                }
            } catch (PDOException $e) {
                $errors[] = "Error validant username: " . $e->getMessage();
            }
        }
        
        return $errors;
    }
    
    /**
     * Actualitzar estadístiques d'usuari
     */
    public function actualitzarEstadistiques($id, $tipus, $increment = 1) {
        try {
            if ($tipus === 'entrades') {
                $sql = "UPDATE {$this->taula} SET total_entrades = total_entrades + :increment WHERE id = :id";
            } elseif ($tipus === 'comentaris') {
                $sql = "UPDATE {$this->taula} SET total_comentaris = total_comentaris + :increment WHERE id = :id";
            } else {
                return false;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':increment', $increment);
            $stmt->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error actualitzant estadístiques: " . $e->getMessage());
            return false;
        }
    }
}
?>
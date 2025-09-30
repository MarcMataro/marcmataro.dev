<?php

/**
 * Classe Idiomes - Gestió d'idiomes suportats pel blog
 *
 * Aquesta classe gestiona els idiomes disponibles al sistema de blog multilingüe,
 * incloent la configuració, ordre i estat de cada idioma.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class IdiomasBlog {
    private $conn;
    private $taula = 'idiomes';
    
    // Constants del sistema
    const IDIOMA_DEFECTE = 'ca';
    const IDIOMES_SISTEMA = ['ca', 'es', 'en'];
    const ESTATS_VALIDS = ['actiu', 'inactiu'];
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtenir tots els idiomes actius ordenats per ordre
     */
    public function obtenirActius() {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE estat = 'actiu' ORDER BY ordre ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenirActius: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir idioma per codi
     */
    public function obtenirPerCodi($codi) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE codi = :codi";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':codi', $codi);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenirPerCodi: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nou idioma
     */
    public function crear($dades) {
        $errors = $this->validar($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "INSERT INTO {$this->taula} (codi, nom, nom_natiu, estat, ordre, bandera_url) 
                    VALUES (:codi, :nom, :nom_natiu, :estat, :ordre, :bandera_url)";
            
            // Assignar variables per bindParam
            $codi = $dades['codi'];
            $nom = $dades['nom'];
            $nom_natiu = $dades['nom_natiu'];
            $estat = $dades['estat'] ?? 'actiu';
            $ordre = $dades['ordre'] ?? 0;
            $bandera_url = $dades['bandera_url'] ?? null;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':codi', $codi);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':nom_natiu', $nom_natiu);
            $stmt->bindParam(':estat', $estat);
            $stmt->bindParam(':ordre', $ordre);
            $stmt->bindParam(':bandera_url', $bandera_url);
            
            $stmt->execute();
            return ['success' => true, 'id' => $this->conn->lastInsertId()];
            
        } catch (PDOException $e) {
            error_log("Error creant idioma: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Actualitzar idioma
     */
    public function actualitzar($id, $dades) {
        $errors = $this->validar($dades, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "UPDATE {$this->taula} SET 
                    nom = :nom, 
                    nom_natiu = :nom_natiu, 
                    estat = :estat, 
                    ordre = :ordre, 
                    bandera_url = :bandera_url
                    WHERE id = :id";
            
            // Assignar variables per bindParam
            $nom = $dades['nom'] ?? '';
            $nom_natiu = $dades['nom_natiu'] ?? '';
            $estat = $dades['estat'] ?? 'actiu';
            $ordre = $dades['ordre'] ?? 0;
            $bandera_url = $dades['bandera_url'] ?? null;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':nom_natiu', $nom_natiu);
            $stmt->bindParam(':estat', $estat);
            $stmt->bindParam(':ordre', $ordre);
            $stmt->bindParam(':bandera_url', $bandera_url);
            
            $stmt->execute();
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error actualitzant idioma: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Eliminar idioma
     */
    public function eliminar($id) {
        try {
            // Verificar que no sigui l'idioma per defecte
            $idioma = $this->obtenirPerId($id);
            if ($idioma && $idioma['codi'] === self::IDIOMA_DEFECTE) {
                return ['success' => false, 'errors' => ['No es pot eliminar l\'idioma per defecte']];
            }
            
            $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error eliminant idioma: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Obtenir idioma per ID
     */
    public function obtenirPerId($id) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenirPerId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validar dades d'idioma
     */
    private function validar($dades, $id = null) {
        $errors = [];
        
        if (empty($dades['codi'])) {
            $errors[] = "El codi d'idioma és obligatori";
        } elseif (strlen($dades['codi']) > 5) {
            $errors[] = "El codi d'idioma no pot superar els 5 caràcters";
        }
        
        if (empty($dades['nom'])) {
            $errors[] = "El nom de l'idioma és obligatori";
        } elseif (strlen($dades['nom']) > 50) {
            $errors[] = "El nom no pot superar els 50 caràcters";
        }
        
        if (empty($dades['nom_natiu'])) {
            $errors[] = "El nom natiu és obligatori";
        } elseif (strlen($dades['nom_natiu']) > 50) {
            $errors[] = "El nom natiu no pot superar els 50 caràcters";
        }
        
        if (isset($dades['estat']) && !in_array($dades['estat'], self::ESTATS_VALIDS)) {
            $errors[] = "L'estat ha de ser 'actiu' o 'inactiu'";
        }
        
        // Verificar codi únic
        if (!empty($dades['codi'])) {
            try {
                $sql = "SELECT id FROM {$this->taula} WHERE codi = :codi" . ($id ? " AND id != :id" : "");
                $codi = $dades['codi'];
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':codi', $codi);
                if ($id) {
                    $stmt->bindParam(':id', $id);
                }
                $stmt->execute();
                if ($stmt->fetch()) {
                    $errors[] = "Aquest codi d'idioma ja existeix";
                }
            } catch (PDOException $e) {
                $errors[] = "Error validant codi: " . $e->getMessage();
            }
        }
        
        return $errors;
    }
    
    /**
     * Obtenir tots els idiomes amb paginació
     */
    public function obtenirTots($opcions = []) {
        try {
            $sql = "SELECT * FROM {$this->taula}";
            $parametres = [];
            $wheres = [];
            
            // Aplicar filtres
            if (!empty($opcions['estat'])) {
                $wheres[] = "estat = :estat";
                $parametres[':estat'] = $opcions['estat'];
            }
            
            if (!empty($wheres)) {
                $sql .= " WHERE " . implode(' AND ', $wheres);
            }
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 'ordre';
            $direccio = $opcions['direccio'] ?? 'ASC';
            $sql .= " ORDER BY {$ordenar} {$direccio}";
            
            // Limitació
            if (!empty($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenirTots: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualitzar l'ordre dels idiomes
     */
    public function actualitzarOrdres($ordres) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($ordres as $id => $ordre) {
                $sql = "UPDATE {$this->taula} SET ordre = :ordre WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                // Assignar variables per bindParam
                $ordreVar = $ordre;
                $idVar = $id;
                $stmt->bindParam(':ordre', $ordreVar);
                $stmt->bindParam(':id', $idVar);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error actualitzant ordres: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error actualitzant ordres']];
        }
    }
}
?>
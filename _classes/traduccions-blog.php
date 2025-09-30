<?php

/**
 * Classe TraduccionsBlog - Gestió de traduccions automàtiques del blog
 *
 * Aquesta classe proporciona funcionalitats per gestionar traduccions automàtiques
 * mitjançant diferents proveïdors i sistema de cache per optimitzar les consultes.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class TraduccionsBlog {
    private $conn;
    private $taula = 'traduccions_automatiques';
    
    // Constants del sistema
    const PROVEÏDORS_VALIDS = ['google', 'deepl', 'azure', 'manual'];
    const PROVEÏDOR_DEFECTE = 'google';
    const CONFIANÇA_MINIMA = 0.7;
    const MAX_LONGITUD_TEXT = 5000;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Traduir text automàticament
     */
    public function traduir($textOriginal, $idiomaOriginal, $idiomaDesti, $proveidor = self::PROVEÏDOR_DEFECTE) {
        try {
            // Generar hash del text original
            $hashOriginal = hash('sha256', $textOriginal);
            
            // Verificar si ja existeix la traducció en cache
            $traduccioExistent = $this->obtenirTraduccio($hashOriginal, $idiomaOriginal, $idiomaDesti);
            
            if ($traduccioExistent) {
                // Incrementar comptador d'usos
                $this->incrementarUsos($traduccioExistent['id']);
                return [
                    'success' => true,
                    'text_traduit' => $traduccioExistent['text_traduit'],
                    'confianca' => $traduccioExistent['confianca'],
                    'proveidor' => $traduccioExistent['proveidor_traduccio'],
                    'cached' => true
                ];
            }
            
            // Realitzar nova traducció
            $resultatTraduccio = $this->executarTraduccio($textOriginal, $idiomaOriginal, $idiomaDesti, $proveidor);
            
            if ($resultatTraduccio['success']) {
                // Guardar traducció en cache
                $this->guardarTraduccio(
                    $textOriginal,
                    $hashOriginal,
                    $idiomaOriginal,
                    $idiomaDesti,
                    $resultatTraduccio['text_traduit'],
                    $proveidor,
                    $resultatTraduccio['confianca']
                );
                
                $resultatTraduccio['cached'] = false;
            }
            
            return $resultatTraduccio;
            
        } catch (Exception $e) {
            error_log("Error en traduir: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error intern de traducció'];
        }
    }
    
    /**
     * Traduir múltiples textos de cop
     */
    public function traduirMultiple($textos, $idiomaOriginal, $idiomaDesti, $proveidor = self::PROVEÏDOR_DEFECTE) {
        $resultats = [];
        
        foreach ($textos as $clau => $text) {
            $resultats[$clau] = $this->traduir($text, $idiomaOriginal, $idiomaDesti, $proveidor);
        }
        
        return $resultats;
    }
    
    /**
     * Obtenir traducció existent del cache
     */
    private function obtenirTraduccio($hashOriginal, $idiomaOriginal, $idiomaDesti) {
        try {
            $sql = "SELECT * FROM {$this->taula} 
                    WHERE hash_original = :hash_original 
                    AND idioma_original = :idioma_original 
                    AND idioma_desti = :idioma_desti
                    ORDER BY data_actualitzacio DESC
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':hash_original', $hashOriginal);
            $stmt->bindParam(':idioma_original', $idiomaOriginal);
            $stmt->bindParam(':idioma_desti', $idiomaDesti);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtenint traducció: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Guardar nova traducció al cache
     */
    private function guardarTraduccio($textOriginal, $hashOriginal, $idiomaOriginal, $idiomaDesti, $textTraduit, $proveidor, $confianca) {
        try {
            $sql = "INSERT INTO {$this->taula} (
                        text_original, hash_original, idioma_original, idioma_desti,
                        text_traduit, proveidor_traduccio, confianca
                    ) VALUES (
                        :text_original, :hash_original, :idioma_original, :idioma_desti,
                        :text_traduit, :proveidor_traduccio, :confianca
                    )
                    ON DUPLICATE KEY UPDATE
                        text_traduit = VALUES(text_traduit),
                        proveidor_traduccio = VALUES(proveidor_traduccio),
                        confianca = VALUES(confianca),
                        usos = usos + 1,
                        data_actualitzacio = CURRENT_TIMESTAMP";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':text_original', $textOriginal);
            $stmt->bindParam(':hash_original', $hashOriginal);
            $stmt->bindParam(':idioma_original', $idiomaOriginal);
            $stmt->bindParam(':idioma_desti', $idiomaDesti);
            $stmt->bindParam(':text_traduit', $textTraduit);
            $stmt->bindParam(':proveidor_traduccio', $proveidor);
            $stmt->bindParam(':confianca', $confianca);
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error guardant traducció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Incrementar comptador d'usos
     */
    private function incrementarUsos($id) {
        try {
            $sql = "UPDATE {$this->taula} SET usos = usos + 1, data_actualitzacio = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error incrementant usos: " . $e->getMessage());
        }
    }
    
    /**
     * Executar traducció amb proveïdor específic
     */
    private function executarTraduccio($text, $idiomaOriginal, $idiomaDesti, $proveidor) {
        // Validar longitud del text
        if (strlen($text) > self::MAX_LONGITUD_TEXT) {
            return [
                'success' => false,
                'error' => 'Text massa llarg per traduir automàticament'
            ];
        }
        
        switch ($proveidor) {
            case 'google':
                return $this->traduirAmbGoogle($text, $idiomaOriginal, $idiomaDesti);
            case 'deepl':
                return $this->traduirAmbDeepL($text, $idiomaOriginal, $idiomaDesti);
            case 'azure':
                return $this->traduirAmbAzure($text, $idiomaOriginal, $idiomaDesti);
            case 'manual':
                return $this->processarTraduccioManual($text);
            default:
                return [
                    'success' => false,
                    'error' => 'Proveïdor de traducció no vàlid'
                ];
        }
    }
    
    /**
     * Traducció amb Google Translate API (placeholder)
     */
    private function traduirAmbGoogle($text, $idiomaOriginal, $idiomaDesti) {
        // NOTA: Aquí s'implementaria la integració real amb Google Translate API
        // Per ara retorna una traducció de placeholder
        
        return [
            'success' => true,
            'text_traduit' => "[GOOGLE] " . $text, // Placeholder
            'confianca' => 0.85,
            'proveidor' => 'google'
        ];
    }
    
    /**
     * Traducció amb DeepL API (placeholder)
     */
    private function traduirAmbDeepL($text, $idiomaOriginal, $idiomaDesti) {
        // NOTA: Aquí s'implementaria la integració real amb DeepL API
        // Per ara retorna una traducció de placeholder
        
        return [
            'success' => true,
            'text_traduit' => "[DEEPL] " . $text, // Placeholder
            'confianca' => 0.90,
            'proveidor' => 'deepl'
        ];
    }
    
    /**
     * Traducció amb Azure Translator (placeholder)
     */
    private function traduirAmbAzure($text, $idiomaOriginal, $idiomaDesti) {
        // NOTA: Aquí s'implementaria la integració real amb Azure Translator
        // Per ara retorna una traducció de placeholder
        
        return [
            'success' => true,
            'text_traduit' => "[AZURE] " . $text, // Placeholder
            'confianca' => 0.80,
            'proveidor' => 'azure'
        ];
    }
    
    /**
     * Processar traducció manual
     */
    private function processarTraduccioManual($text) {
        return [
            'success' => true,
            'text_traduit' => $text, // Sense traducció automàtica
            'confianca' => 1.0,
            'proveidor' => 'manual'
        ];
    }
    
    /**
     * Obtenir traduccions recents
     */
    public function obtenirRecents($limit = 50) {
        try {
            $sql = "SELECT * FROM {$this->taula} 
                    ORDER BY data_actualitzacio DESC 
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtenint recents: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir traduccions per idioma
     */
    public function obtenirPerIdioma($idiomaOriginal, $idiomaDesti, $opcions = []) {
        try {
            $sql = "SELECT * FROM {$this->taula} 
                    WHERE idioma_original = :idioma_original 
                    AND idioma_desti = :idioma_desti";
            
            $parametres = [
                ':idioma_original' => $idiomaOriginal,
                ':idioma_desti' => $idiomaDesti
            ];
            
            // Filtrar per proveïdor
            if (!empty($opcions['proveidor'])) {
                $sql .= " AND proveidor_traduccio = :proveidor";
                $parametres[':proveidor'] = $opcions['proveidor'];
            }
            
            // Filtrar per confiança mínima
            if (!empty($opcions['confianca_minima'])) {
                $sql .= " AND confianca >= :confianca_minima";
                $parametres[':confianca_minima'] = $opcions['confianca_minima'];
            }
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 'data_actualitzacio';
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
            error_log("Error obtenint per idioma: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Netejar cache antic
     */
    public function netejarCacheAntic($diesAntics = 30) {
        try {
            $sql = "DELETE FROM {$this->taula} 
                    WHERE data_actualitzacio < DATE_SUB(NOW(), INTERVAL :dies_antics DAY)
                    AND usos < 2"; // Mantenir traduccions utilitzades
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':dies_antics', $diesAntics, PDO::PARAM_INT);
            $stmt->execute();
            
            $eliminades = $stmt->rowCount();
            return ['success' => true, 'eliminades' => $eliminades];
        } catch (PDOException $e) {
            error_log("Error netejant cache: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error netejant cache'];
        }
    }
    
    /**
     * Obtenir estadístiques de traduccions
     */
    public function obtenirEstadistiques() {
        try {
            $sql = "SELECT 
                        proveidor_traduccio,
                        COUNT(*) as total_traduccions,
                        AVG(confianca) as confianca_mitjana,
                        SUM(usos) as total_usos
                    FROM {$this->taula}
                    GROUP BY proveidor_traduccio";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $proveïdors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Estadístiques generals
            $sql = "SELECT 
                        COUNT(*) as total_traduccions,
                        COUNT(DISTINCT CONCAT(idioma_original, '-', idioma_desti)) as parells_idiomes,
                        AVG(confianca) as confiança_mitjana_global
                    FROM {$this->taula}";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $general = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'general' => $general,
                'per_proveidor' => $proveïdors
            ];
        } catch (PDOException $e) {
            error_log("Error obtenint estadístiques: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar idiomes suportats
     */
    public function validarIdiomes($idiomaOriginal, $idiomaDesti) {
        // Llista d'idiomes suportats (es pot ampliar)
        $idiomesSuportats = ['ca', 'es', 'en', 'fr', 'de', 'it', 'pt'];
        
        if (!in_array($idiomaOriginal, $idiomesSuportats)) {
            return ['valid' => false, 'error' => 'Idioma original no suportat'];
        }
        
        if (!in_array($idiomaDesti, $idiomesSuportats)) {
            return ['valid' => false, 'error' => 'Idioma destí no suportat'];
        }
        
        if ($idiomaOriginal === $idiomaDesti) {
            return ['valid' => false, 'error' => 'Els idiomes original i destí no poden ser iguals'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Eliminar traducció específica
     */
    public function eliminarTraduccio($id) {
        try {
            $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Error eliminant traducció: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error eliminant traducció'];
        }
    }
}
?>
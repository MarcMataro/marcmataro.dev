<?php
/**
 * Classe Projectes - Gestió completa de projectes
 * 
 * Aquesta classe gestiona totes les operacions CRUD per als projectes,
 * incloent validacions, conversions de dades i mètodes d'utilitat.
 * 
 * @author Marc Mataró
 * @version 1.0
 */

class Projectes {
    private $connexio;
    private $taula = 'projectes';
    
    // Estats vàlids per a la validació
    const ESTATS_VALIDS = ['desenvolupament', 'actiu', 'aturat', 'archivat'];
    
    /**
     * Constructor
     * 
     * @param PDO $connexio Connexió a la base de dades
     */
    public function __construct($connexio) {
        $this->connexio = $connexio;
    }
    
    /**
     * Crear un nou projecte
     * 
     * @param array $dades Dades del projecte
     * @return int|false ID del projecte creat o false si hi ha error
     */
    public function crear($dades) {
        try {
            // Validar dades obligatòries
            if (!$this->validarDadesObligatories($dades)) {
                return false;
            }
            
            // Generar slug automàticament si no s'ha proporcionat
            if (empty($dades['slug'])) {
                $dades['slug'] = $this->generarSlug($dades['nom']);
            }
            
            // Assegurar que el slug és únic
            $dades['slug'] = $this->assegurarSlugUnic($dades['slug']);
            
            // Preparar les dades per a la inserció
            $dadesPreparades = $this->prepararDades($dades);
            
            $sql = "INSERT INTO {$this->taula} (
                nom, slug, descripcio_curta, descripcio_detallada, estat, visible,
                data_publicacio, url_demo, url_github, url_documentacio,
                imatge_portada, imatge_detall, tecnologies_principals, caracteristiques
            ) VALUES (
                :nom, :slug, :descripcio_curta, :descripcio_detallada, :estat, :visible,
                :data_publicacio, :url_demo, :url_github, :url_documentacio,
                :imatge_portada, :imatge_detall, :tecnologies_principals, :caracteristiques
            )";
            
            $stmt = $this->connexio->prepare($sql);
            
            if ($stmt->execute($dadesPreparades)) {
                return $this->connexio->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en crear projecte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir un projecte per ID
     * 
     * @param int $id ID del projecte
     * @return array|false Dades del projecte o false si no existeix
     */
    public function obtenirPerId($id) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE id = :id";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $projecte = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($projecte) {
                return $this->processarDadesSortida($projecte);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en obtenir projecte per ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir un projecte per slug
     * 
     * @param string $slug Slug del projecte
     * @return array|false Dades del projecte o false si no existeix
     */
    public function obtenirPerSlug($slug) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE slug = :slug";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();
            
            $projecte = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($projecte) {
                return $this->processarDadesSortida($projecte);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en obtenir projecte per slug: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir tots els projectes amb filtres opcionals
     * 
     * @param array $opcions Opcions de filtrat i ordenació
     * @return array Array de projectes
     */
    public function obtenirTots($opcions = []) {
        try {
            $sql = "SELECT * FROM {$this->taula}";
            $parametres = [];
            $condicions = [];
            
            // Aplicar filtres
            if (isset($opcions['estat'])) {
                $condicions[] = "estat = :estat";
                $parametres[':estat'] = $opcions['estat'];
            }
            
            if (isset($opcions['visible'])) {
                $condicions[] = "visible = :visible";
                $parametres[':visible'] = $opcions['visible'] ? 1 : 0;
            }
            
            if (isset($opcions['data_publicacio_desde'])) {
                $condicions[] = "data_publicacio >= :data_desde";
                $parametres[':data_desde'] = $opcions['data_publicacio_desde'];
            }
            
            if (isset($opcions['data_publicacio_fins'])) {
                $condicions[] = "data_publicacio <= :data_fins";
                $parametres[':data_fins'] = $opcions['data_publicacio_fins'];
            }
            
            // Afegir condicions WHERE si n'hi ha
            if (!empty($condicions)) {
                $sql .= " WHERE " . implode(" AND ", $condicions);
            }
            
            // Ordenació
            $ordenacio = $opcions['ordre'] ?? 'data_creacio DESC';
            $sql .= " ORDER BY " . $ordenacio;
            
            // Límit i offset per a paginació
            if (isset($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
                
                if (isset($opcions['offset'])) {
                    $sql .= " OFFSET " . (int)$opcions['offset'];
                }
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            $projectes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Processar cada projecte
            return array_map([$this, 'processarDadesSortida'], $projectes);
            
        } catch (PDOException $e) {
            error_log("Error en obtenir projectes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir projectes visibles (per a la web pública)
     * 
     * @param int $limit Límit de resultats
     * @return array Array de projectes visibles
     */
    public function obtenirVisibles($limit = null) {
        $opcions = [
            'visible' => true,
            'ordre' => 'data_publicacio DESC, data_creacio DESC'
        ];
        
        if ($limit) {
            $opcions['limit'] = $limit;
        }
        
        return $this->obtenirTots($opcions);
    }
    
    /**
     * Actualitzar un projecte
     * 
     * @param int $id ID del projecte
     * @param array $dades Noves dades
     * @return bool True si s'ha actualitzat correctament
     */
    public function actualitzar($id, $dades) {
        try {
            // Verificar que el projecte existeix
            if (!$this->obtenirPerId($id)) {
                return false;
            }
            
            // Si s'actualitza el nom i no hi ha slug, generar-lo
            if (isset($dades['nom']) && empty($dades['slug'])) {
                $dades['slug'] = $this->generarSlug($dades['nom']);
            }
            
            // Si s'actualitza el slug, assegurar que és únic
            if (isset($dades['slug'])) {
                $dades['slug'] = $this->assegurarSlugUnic($dades['slug'], $id);
            }
            
            // Preparar les dades
            $dadesPreparades = $this->prepararDades($dades, false);
            
            // Construir la consulta dinàmicament
            $camps = [];
            foreach (array_keys($dadesPreparades) as $camp) {
                $camps[] = "{$camp} = :{$camp}";
            }
            
            $sql = "UPDATE {$this->taula} SET " . implode(', ', $camps) . " WHERE id = :id";
            $dadesPreparades[':id'] = $id;
            
            $stmt = $this->connexio->prepare($sql);
            return $stmt->execute($dadesPreparades);
            
        } catch (PDOException $e) {
            error_log("Error en actualitzar projecte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un projecte
     * 
     * @param int $id ID del projecte
     * @return bool True si s'ha eliminat correctament
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminar projecte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Comptar projectes amb filtres opcionals
     * 
     * @param array $opcions Opcions de filtrat
     * @return int Nombre de projectes
     */
    public function comptar($opcions = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->taula}";
            $parametres = [];
            $condicions = [];
            
            // Aplicar els mateixos filtres que obtenirTots
            if (isset($opcions['estat'])) {
                $condicions[] = "estat = :estat";
                $parametres[':estat'] = $opcions['estat'];
            }
            
            if (isset($opcions['visible'])) {
                $condicions[] = "visible = :visible";
                $parametres[':visible'] = $opcions['visible'] ? 1 : 0;
            }
            
            if (!empty($condicions)) {
                $sql .= " WHERE " . implode(" AND ", $condicions);
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en comptar projectes: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Buscar projectes per text
     * 
     * @param string $terme Terme de cerca
     * @param array $opcions Opcions addicionals
     * @return array Array de projectes trobats
     */
    public function buscar($terme, $opcions = []) {
        try {
            $sql = "SELECT * FROM {$this->taula} 
                    WHERE (nom LIKE :terme1 
                           OR descripcio_curta LIKE :terme2 
                           OR descripcio_detallada LIKE :terme3)";
            
            $parametres = [
                ':terme1' => "%{$terme}%",
                ':terme2' => "%{$terme}%",
                ':terme3' => "%{$terme}%"
            ];
            
            // Afegir filtres addicionals
            if (isset($opcions['visible']) && $opcions['visible']) {
                $sql .= " AND visible = 1";
            }
            
            if (isset($opcions['estat'])) {
                $sql .= " AND estat = :estat";
                $parametres[':estat'] = $opcions['estat'];
            }
            
            $sql .= " ORDER BY data_publicacio DESC, data_creacio DESC";
            
            if (isset($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            $projectes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'processarDadesSortida'], $projectes);
            
        } catch (PDOException $e) {
            error_log("Error en buscar projectes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir estadístiques dels projectes
     * 
     * @return array Estadístiques
     */
    public function obtenirEstadistiques() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN visible = 1 THEN 1 END) as visibles,
                        COUNT(CASE WHEN estat = 'actiu' THEN 1 END) as actius,
                        COUNT(CASE WHEN estat = 'desenvolupament' THEN 1 END) as desenvolupament,
                        COUNT(CASE WHEN estat = 'aturat' THEN 1 END) as aturats,
                        COUNT(CASE WHEN estat = 'archivat' THEN 1 END) as archivats
                    FROM {$this->taula}";
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenir estadístiques: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar dades obligatòries
     * 
     * @param array $dades Dades a validar
     * @return bool True si les dades són vàlides
     */
    private function validarDadesObligatories($dades) {
        return !empty($dades['nom']);
    }
    
    /**
     * Generar slug a partir del nom
     * 
     * @param string $nom Nom del projecte
     * @return string Slug generat
     */
    private function generarSlug($nom) {
        // Convertir a minúscules
        $slug = mb_strtolower($nom, 'UTF-8');
        
        // Reemplaçar caràcters especials catalans
        $slug = str_replace(
            ['à', 'á', 'è', 'é', 'í', 'ò', 'ó', 'ú', 'ü', 'ç', 'ñ'],
            ['a', 'a', 'e', 'e', 'i', 'o', 'o', 'u', 'u', 'c', 'n'],
            $slug
        );
        
        // Reemplaçar espais i caràcters no alfanumèrics per guions
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Eliminar guions del principi i final
        $slug = trim($slug, '-');
        
        return $slug;
    }
    
    /**
     * Assegurar que el slug és únic
     * 
     * @param string $slug Slug base
     * @param int $excloureId ID a excloure (per a actualitzacions)
     * @return string Slug únic
     */
    private function assegurarSlugUnic($slug, $excloureId = null) {
        $slugOriginal = $slug;
        $contador = 1;
        
        while ($this->slugExisteix($slug, $excloureId)) {
            $slug = $slugOriginal . '-' . $contador;
            $contador++;
        }
        
        return $slug;
    }
    
    /**
     * Verificar si un slug ja existeix
     * 
     * @param string $slug Slug a verificar
     * @param int $excloureId ID a excloure
     * @return bool True si existeix
     */
    private function slugExisteix($slug, $excloureId = null) {
        try {
            $sql = "SELECT id FROM {$this->taula} WHERE slug = :slug";
            $parametres = [':slug' => $slug];
            
            if ($excloureId) {
                $sql .= " AND id != :id";
                $parametres[':id'] = $excloureId;
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Preparar dades per a la base de dades
     * 
     * @param array $dades Dades originals
     * @param bool $esNou Si és un registre nou (per defectes)
     * @return array Dades preparades
     */
    private function prepararDades($dades, $esNou = true) {
        $dadesPreparades = [];
        
        // Camps de text
        $campsText = ['nom', 'slug', 'descripcio_curta', 'descripcio_detallada', 
                      'url_demo', 'url_github', 'url_documentacio', 
                      'imatge_portada', 'imatge_detall'];
        
        foreach ($campsText as $camp) {
            if (isset($dades[$camp])) {
                $dadesPreparades[":{$camp}"] = $dades[$camp] ?: null;
            } elseif ($esNou) {
                $dadesPreparades[":{$camp}"] = null;
            }
        }
        
        // Estat amb validació
        if (isset($dades['estat'])) {
            if (in_array($dades['estat'], self::ESTATS_VALIDS)) {
                $dadesPreparades[':estat'] = $dades['estat'];
            }
        } elseif ($esNou) {
            $dadesPreparades[':estat'] = 'desenvolupament';
        }
        
        // Visible (boolean)
        if (isset($dades['visible'])) {
            $dadesPreparades[':visible'] = $dades['visible'] ? 1 : 0;
        } elseif ($esNou) {
            $dadesPreparades[':visible'] = 1;
        }
        
        // Data de publicació
        if (isset($dades['data_publicacio'])) {
            $dadesPreparades[':data_publicacio'] = $dades['data_publicacio'] ?: null;
        } elseif ($esNou) {
            $dadesPreparades[':data_publicacio'] = null;
        }
        
        // Camps JSON
        if (isset($dades['tecnologies_principals'])) {
            $dadesPreparades[':tecnologies_principals'] = is_array($dades['tecnologies_principals']) 
                ? json_encode($dades['tecnologies_principals'], JSON_UNESCAPED_UNICODE) 
                : $dades['tecnologies_principals'];
        } elseif ($esNou) {
            $dadesPreparades[':tecnologies_principals'] = null;
        }
        
        if (isset($dades['caracteristiques'])) {
            $dadesPreparades[':caracteristiques'] = is_array($dades['caracteristiques']) 
                ? json_encode($dades['caracteristiques'], JSON_UNESCAPED_UNICODE) 
                : $dades['caracteristiques'];
        } elseif ($esNou) {
            $dadesPreparades[':caracteristiques'] = null;
        }
        
        return $dadesPreparades;
    }
    
    /**
     * Processar dades de sortida (convertir JSON, etc.)
     * 
     * @param array $projecte Dades del projecte
     * @return array Dades processades
     */
    private function processarDadesSortida($projecte) {
        // Convertir camps JSON a arrays
        if (!empty($projecte['tecnologies_principals'])) {
            $projecte['tecnologies_principals'] = json_decode($projecte['tecnologies_principals'], true);
        } else {
            $projecte['tecnologies_principals'] = [];
        }
        
        if (!empty($projecte['caracteristiques'])) {
            $projecte['caracteristiques'] = json_decode($projecte['caracteristiques'], true);
        } else {
            $projecte['caracteristiques'] = [];
        }
        
        // Convertir visible a boolean
        $projecte['visible'] = (bool)$projecte['visible'];
        
        return $projecte;
    }
    
    /**
     * Validar format de data
     * 
     * @param string $data Data en format Y-m-d
     * @return bool True si és vàlida
     */
    private function validarData($data) {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
    
    /**
     * Obtenir estats vàlids
     * 
     * @return array Estats vàlids
     */
    public static function getEstatsValids() {
        return self::ESTATS_VALIDS;
    }
}
?>

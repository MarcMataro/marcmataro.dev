<?php

/**
 * Classe EntradesBlog - Gestió d'entrades del blog amb suport multilingüe
 *
 * Aquesta classe gestiona les entrades del blog amb funcionalitats completes
 * de multiidioma, control d'estat, programació de publicació i traduccions.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class EntradesBlog {
    private $conn;
    private $taula = 'entrades_blog';
    private $taulaTraduccions = 'entrades_traduccions';
    private $taulaCategories = 'entrades_categories';
    private $taulaTags = 'entrades_tags';
    
    // Constants del sistema
    const ESTATS_VALIDS = ['esborrany', 'revisio', 'programat', 'publicat', 'arxivat'];
    const FORMATS_VALIDS = ['estandard', 'galeria', 'video', 'audio'];
    const ESTATS_TRADUCCIO = ['pendent', 'en_progres', 'revisio', 'completat', 'rebutjat'];
    const QUALITATS_TRADUCCIO = ['baixa', 'mitjana', 'alta', 'professional'];
    const ESTAT_DEFECTE = 'esborrany';
    const FORMAT_DEFECTE = 'estandard';
    const IDIOMA_DEFECTE = 'ca';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nova entrada
     */
    public function crear($dades) {
        $errors = $this->validar($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Crear entrada base
            $sql = "INSERT INTO {$this->taula} (
                        uuid, slug_base, idioma_original, entrada_original_id, autor_id, 
                        traductor_id, coautors, estat, data_publicacio, data_programacio,
                        visites, temps_lectura_estimat, comentaris_activats, destacat, 
                        format, traduccio_aprovada, percentatge_traduccio, usuari_actualitzacio
                    ) VALUES (
                        UUID(), :slug_base, :idioma_original, :entrada_original_id, :autor_id,
                        :traductor_id, :coautors, :estat, :data_publicacio, :data_programacio,
                        0, :temps_lectura_estimat, :comentaris_activats, :destacat,
                        :format, :traduccio_aprovada, :percentatge_traduccio, :usuari_actualitzacio
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':slug_base', $dades['slug_base']);
            $stmt->bindParam(':idioma_original', $dades['idioma_original'] ?? self::IDIOMA_DEFECTE);
            $stmt->bindParam(':entrada_original_id', $dades['entrada_original_id'] ?? null);
            $stmt->bindParam(':autor_id', $dades['autor_id']);
            $stmt->bindParam(':traductor_id', $dades['traductor_id'] ?? null);
            $stmt->bindParam(':coautors', isset($dades['coautors']) ? json_encode($dades['coautors']) : null);
            $stmt->bindParam(':estat', $dades['estat'] ?? self::ESTAT_DEFECTE);
            $stmt->bindParam(':data_publicacio', $dades['data_publicacio'] ?? null);
            $stmt->bindParam(':data_programacio', $dades['data_programacio'] ?? null);
            $stmt->bindParam(':temps_lectura_estimat', $dades['temps_lectura_estimat'] ?? null);
            $stmt->bindParam(':comentaris_activats', $dades['comentaris_activats'] ?? true, PDO::PARAM_BOOL);
            $stmt->bindParam(':destacat', $dades['destacat'] ?? false, PDO::PARAM_BOOL);
            $stmt->bindParam(':format', $dades['format'] ?? self::FORMAT_DEFECTE);
            $stmt->bindParam(':traduccio_aprovada', $dades['traduccio_aprovada'] ?? true, PDO::PARAM_BOOL);
            $stmt->bindParam(':percentatge_traduccio', $dades['percentatge_traduccio'] ?? 100);
            $stmt->bindParam(':usuari_actualitzacio', $dades['usuari_actualitzacio'] ?? $dades['autor_id']);
            
            $stmt->execute();
            $entradaId = $this->conn->lastInsertId();
            
            // Crear traduccions
            if (isset($dades['traduccions'])) {
                foreach ($dades['traduccions'] as $idioma => $traduccio) {
                    $this->crearTraduccio($entradaId, $idioma, $traduccio, $dades['autor_id']);
                }
            }
            
            // Associar categories
            if (isset($dades['categories']) && is_array($dades['categories'])) {
                $this->associarCategories($entradaId, $dades['categories']);
            }
            
            // Associar tags
            if (isset($dades['tags']) && is_array($dades['tags'])) {
                $this->associarTags($entradaId, $dades['tags']);
            }
            
            $this->conn->commit();
            return ['success' => true, 'id' => $entradaId];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creant entrada: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Actualitzar entrada
     */
    public function actualitzar($id, $dades) {
        $errors = $this->validar($dades, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Actualitzar entrada base
            $camps = [];
            $parametres = [':id' => $id];
            
            $campsPermesos = [
                'estat', 'data_publicacio', 'data_programacio', 'temps_lectura_estimat',
                'comentaris_activats', 'destacat', 'format', 'traduccio_aprovada',
                'percentatge_traduccio', 'usuari_actualitzacio'
            ];
            
            foreach ($campsPermesos as $camp) {
                if (isset($dades[$camp])) {
                    $camps[] = "{$camp} = :{$camp}";
                    $parametres[":{$camp}"] = $dades[$camp];
                }
            }
            
            // Camps JSON especials
            if (isset($dades['coautors'])) {
                $camps[] = "coautors = :coautors";
                $parametres[':coautors'] = json_encode($dades['coautors']);
            }
            
            if (!empty($camps)) {
                $sql = "UPDATE {$this->taula} SET " . implode(', ', $camps) . " WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($parametres);
            }
            
            // Actualitzar traduccions
            if (isset($dades['traduccions'])) {
                foreach ($dades['traduccions'] as $idioma => $traduccio) {
                    $this->actualitzarTraduccio($id, $idioma, $traduccio, $dades['usuari_actualitzacio'] ?? null);
                }
            }
            
            // Actualitzar categories
            if (isset($dades['categories'])) {
                $this->actualitzarCategories($id, $dades['categories']);
            }
            
            // Actualitzar tags
            if (isset($dades['tags'])) {
                $this->actualitzarTags($id, $dades['tags']);
            }
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error actualitzant entrada: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Obtenir entrada per ID amb traduccions
     */
    public function obtenirPerId($id, $idioma = self::IDIOMA_DEFECTE) {
        try {
            $sql = "SELECT e.*, 
                           et.titol, et.slug, et.contingut, et.extracte,
                           et.meta_titol, et.meta_descripcio, et.meta_keywords, et.url_canonica,
                           et.imatge_principal, et.imatge_miniatura, et.galeria_json, et.video_url,
                           et.estat_traduccio, et.qualitat_traduccio,
                           u.nom as autor_nom, u.avatar_url as autor_avatar
                    FROM {$this->taula} e
                    LEFT JOIN {$this->taulaTraduccions} et ON e.id = et.entrada_id AND et.idioma_codi = :idioma
                    LEFT JOIN usuaris_blog u ON e.autor_id = u.id
                    WHERE e.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':idioma', $idioma);
            $stmt->execute();
            
            $entrada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entrada) {
                $entrada = $this->procesarCampsJSON($entrada);
                $entrada['categories'] = $this->obtenirCategoriesEntrada($id, $idioma);
                $entrada['tags'] = $this->obtenirTagsEntrada($id, $idioma);
            }
            
            return $entrada;
        } catch (PDOException $e) {
            error_log("Error en obtenirPerId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir entrades amb traduccions i filtres
     */
    public function obtenirAmbTraducio($idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        try {
            $sql = "SELECT e.*, 
                           COALESCE(et.titol, et_ca.titol) as titol,
                           COALESCE(et.slug, et_ca.slug) as slug,
                           COALESCE(et.extracte, et_ca.extracte) as extracte,
                           COALESCE(et.imatge_principal, et_ca.imatge_principal) as imatge_principal,
                           COALESCE(et.imatge_miniatura, et_ca.imatge_miniatura) as imatge_miniatura,
                           u.nom as autor_nom, u.avatar_url as autor_avatar
                    FROM {$this->taula} e
                    LEFT JOIN {$this->taulaTraduccions} et ON e.id = et.entrada_id AND et.idioma_codi = :idioma
                    LEFT JOIN {$this->taulaTraduccions} et_ca ON e.id = et_ca.entrada_id AND et_ca.idioma_codi = 'ca'
                    LEFT JOIN usuaris_blog u ON e.autor_id = u.id";
            
            $entrades = $this->executarConsultaAmbOpcions($sql, $opcions, [':idioma' => $idioma]);
            
            // Processar camps JSON i afegir categories/tags
            foreach ($entrades as &$entrada) {
                $entrada = $this->procesarCampsJSON($entrada);
                if (!empty($opcions['incloure_categories'])) {
                    $entrada['categories'] = $this->obtenirCategoriesEntrada($entrada['id'], $idioma);
                }
                if (!empty($opcions['incloure_tags'])) {
                    $entrada['tags'] = $this->obtenirTagsEntrada($entrada['id'], $idioma);
                }
            }
            
            return $entrades;
        } catch (PDOException $e) {
            error_log("Error en obtenirAmbTraducio: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir entrades publicades per al frontend
     */
    public function obtenirPublicades($idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        $opcions['estat'] = 'publicat';
        $opcions['data_publicacio_fins'] = date('Y-m-d H:i:s'); // Només entrades ja publicades
        return $this->obtenirAmbTraducio($idioma, $opcions);
    }
    
    /**
     * Obtenir entrades destacades
     */
    public function obtenirDestacades($idioma = self::IDIOMA_DEFECTE, $limit = 5) {
        return $this->obtenirPublicades($idioma, [
            'destacat' => true,
            'limit' => $limit,
            'ordenar' => 'e.data_publicacio',
            'direccio' => 'DESC'
        ]);
    }
    
    /**
     * Cerca d'entrades per text
     */
    public function cercar($text, $idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        try {
            $sql = "SELECT e.*, 
                           COALESCE(et.titol, et_ca.titol) as titol,
                           COALESCE(et.slug, et_ca.slug) as slug,
                           COALESCE(et.extracte, et_ca.extracte) as extracte,
                           COALESCE(et.imatge_miniatura, et_ca.imatge_miniatura) as imatge_miniatura,
                           u.nom as autor_nom,
                           MATCH(et.titol, et.extracte, et.contingut) AGAINST(:text IN NATURAL LANGUAGE MODE) as rellevancia
                    FROM {$this->taula} e
                    LEFT JOIN {$this->taulaTraduccions} et ON e.id = et.entrada_id AND et.idioma_codi = :idioma
                    LEFT JOIN {$this->taulaTraduccions} et_ca ON e.id = et_ca.entrada_id AND et_ca.idioma_codi = 'ca'
                    LEFT JOIN usuaris_blog u ON e.autor_id = u.id
                    WHERE MATCH(et.titol, et.extracte, et.contingut) AGAINST(:text IN NATURAL LANGUAGE MODE)
                    AND e.estat = 'publicat'
                    ORDER BY rellevancia DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':text', $text);
            $stmt->bindParam(':idioma', $idioma);
            
            if (!empty($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en cercar: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear traducció d'entrada
     */
    private function crearTraduccio($entradaId, $idioma, $traduccio, $usuariId = null) {
        try {
            $sql = "INSERT INTO {$this->taulaTraduccions} (
                        entrada_id, idioma_codi, titol, slug, contingut, extracte,
                        meta_titol, meta_descripcio, meta_keywords, url_canonica,
                        imatge_principal, imatge_miniatura, galeria_json, video_url,
                        estat_traduccio, qualitat_traduccio, usuari_traduccio
                    ) VALUES (
                        :entrada_id, :idioma_codi, :titol, :slug, :contingut, :extracte,
                        :meta_titol, :meta_descripcio, :meta_keywords, :url_canonica,
                        :imatge_principal, :imatge_miniatura, :galeria_json, :video_url,
                        :estat_traduccio, :qualitat_traduccio, :usuari_traduccio
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_id', $entradaId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->bindParam(':titol', $traduccio['titol']);
            $stmt->bindParam(':slug', $traduccio['slug']);
            $stmt->bindParam(':contingut', $traduccio['contingut']);
            $stmt->bindParam(':extracte', $traduccio['extracte'] ?? null);
            $stmt->bindParam(':meta_titol', $traduccio['meta_titol'] ?? null);
            $stmt->bindParam(':meta_descripcio', $traduccio['meta_descripcio'] ?? null);
            $stmt->bindParam(':meta_keywords', $traduccio['meta_keywords'] ?? null);
            $stmt->bindParam(':url_canonica', $traduccio['url_canonica'] ?? null);
            $stmt->bindParam(':imatge_principal', $traduccio['imatge_principal'] ?? null);
            $stmt->bindParam(':imatge_miniatura', $traduccio['imatge_miniatura'] ?? null);
            $stmt->bindParam(':galeria_json', isset($traduccio['galeria_json']) ? json_encode($traduccio['galeria_json']) : null);
            $stmt->bindParam(':video_url', $traduccio['video_url'] ?? null);
            $stmt->bindParam(':estat_traduccio', $traduccio['estat_traduccio'] ?? 'completat');
            $stmt->bindParam(':qualitat_traduccio', $traduccio['qualitat_traduccio'] ?? 'alta');
            $stmt->bindParam(':usuari_traduccio', $usuariId);
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error creant traducció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualitzar traducció d'entrada
     */
    private function actualitzarTraduccio($entradaId, $idioma, $traduccio, $usuariId = null) {
        try {
            // Verificar si ja existeix la traducció
            $sql = "SELECT id FROM {$this->taulaTraduccions} WHERE entrada_id = :entrada_id AND idioma_codi = :idioma_codi";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_id', $entradaId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // Actualitzar traducció existent
                $camps = [];
                $parametres = [':entrada_id' => $entradaId, ':idioma_codi' => $idioma];
                
                $campsPermesos = [
                    'titol', 'slug', 'contingut', 'extracte', 'meta_titol', 'meta_descripcio', 
                    'meta_keywords', 'url_canonica', 'imatge_principal', 'imatge_miniatura',
                    'video_url', 'estat_traduccio', 'qualitat_traduccio'
                ];
                
                foreach ($campsPermesos as $camp) {
                    if (isset($traduccio[$camp])) {
                        $camps[] = "{$camp} = :{$camp}";
                        $parametres[":{$camp}"] = $traduccio[$camp];
                    }
                }
                
                if (isset($traduccio['galeria_json'])) {
                    $camps[] = "galeria_json = :galeria_json";
                    $parametres[':galeria_json'] = json_encode($traduccio['galeria_json']);
                }
                
                if (!empty($camps)) {
                    $sql = "UPDATE {$this->taulaTraduccions} SET " . implode(', ', $camps) . 
                           " WHERE entrada_id = :entrada_id AND idioma_codi = :idioma_codi";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute($parametres);
                }
            } else {
                // Crear nova traducció
                return $this->crearTraduccio($entradaId, $idioma, $traduccio, $usuariId);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error actualitzant traducció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Associar categories a entrada
     */
    private function associarCategories($entradaId, $categories) {
        try {
            foreach ($categories as $index => $categoriaId) {
                $sql = "INSERT IGNORE INTO {$this->taulaCategories} (entrada_blog_id, categoria_blog_id, ordre) 
                        VALUES (:entrada_id, :categoria_id, :ordre)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':entrada_id', $entradaId);
                $stmt->bindParam(':categoria_id', $categoriaId);
                $stmt->bindParam(':ordre', $index);
                $stmt->execute();
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error associant categories: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualitzar categories d'entrada
     */
    private function actualitzarCategories($entradaId, $categories) {
        try {
            // Eliminar categories existents
            $sql = "DELETE FROM {$this->taulaCategories} WHERE entrada_blog_id = :entrada_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_id', $entradaId);
            $stmt->execute();
            
            // Afegir noves categories
            return $this->associarCategories($entradaId, $categories);
        } catch (PDOException $e) {
            error_log("Error actualitzant categories: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Associar tags a entrada
     */
    private function associarTags($entradaId, $tags) {
        try {
            foreach ($tags as $tagId) {
                $sql = "INSERT IGNORE INTO {$this->taulaTags} (entrada_blog_id, tag_blog_id) 
                        VALUES (:entrada_id, :tag_id)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':entrada_id', $entradaId);
                $stmt->bindParam(':tag_id', $tagId);
                $stmt->execute();
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error associant tags: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualitzar tags d'entrada
     */
    private function actualitzarTags($entradaId, $tags) {
        try {
            // Eliminar tags existents
            $sql = "DELETE FROM {$this->taulaTags} WHERE entrada_blog_id = :entrada_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_id', $entradaId);
            $stmt->execute();
            
            // Afegir nous tags
            return $this->associarTags($entradaId, $tags);
        } catch (PDOException $e) {
            error_log("Error actualitzant tags: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir categories d'una entrada
     */
    private function obtenirCategoriesEntrada($entradaId, $idioma = self::IDIOMA_DEFECTE) {
        try {
            $sql = "SELECT c.id, c.slug_base,
                           COALESCE(ct.nom, ct_ca.nom) as nom,
                           COALESCE(ct.slug, ct_ca.slug) as slug
                    FROM {$this->taulaCategories} ec
                    JOIN categories_blog c ON ec.categoria_blog_id = c.id
                    LEFT JOIN categories_traduccions ct ON c.id = ct.categoria_id AND ct.idioma_codi = :idioma
                    LEFT JOIN categories_traduccions ct_ca ON c.id = ct_ca.categoria_id AND ct_ca.idioma_codi = 'ca'
                    WHERE ec.entrada_blog_id = :entrada_id
                    ORDER BY ec.ordre";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_id', $entradaId);
            $stmt->bindParam(':idioma', $idioma);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtenint categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir tags d'una entrada
     */
    private function obtenirTagsEntrada($entradaId, $idioma = self::IDIOMA_DEFECTE) {
        try {
            $sql = "SELECT t.id, t.slug_base,
                           COALESCE(tt.nom, tt_ca.nom) as nom,
                           COALESCE(tt.slug, tt_ca.slug) as slug
                    FROM {$this->taulaTags} et
                    JOIN tags_blog t ON et.tag_blog_id = t.id
                    LEFT JOIN tags_traduccions tt ON t.id = tt.tag_id AND tt.idioma_codi = :idioma
                    LEFT JOIN tags_traduccions tt_ca ON t.id = tt_ca.tag_id AND tt_ca.idioma_codi = 'ca'
                    WHERE et.entrada_blog_id = :entrada_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_id', $entradaId);
            $stmt->bindParam(':idioma', $idioma);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtenint tags: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Processar camps JSON
     */
    private function procesarCampsJSON($entrada) {
        $campsJSON = ['coautors', 'galeria_json'];
        
        foreach ($campsJSON as $camp) {
            if (!empty($entrada[$camp])) {
                $decoded = json_decode($entrada[$camp], true);
                $entrada[$camp] = $decoded !== null ? $decoded : [];
            } else {
                $entrada[$camp] = [];
            }
        }
        
        return $entrada;
    }
    
    /**
     * Executar consulta amb opcions
     */
    private function executarConsultaAmbOpcions($sql, $opcions = [], $parametresExtra = []) {
        try {
            $parametres = $parametresExtra;
            $wheres = [];
            
            // Aplicar filtres
            if (!empty($opcions['estat'])) {
                $wheres[] = "e.estat = :estat";
                $parametres[':estat'] = $opcions['estat'];
            }
            
            if (!empty($opcions['autor_id'])) {
                $wheres[] = "e.autor_id = :autor_id";
                $parametres[':autor_id'] = $opcions['autor_id'];
            }
            
            if (isset($opcions['destacat'])) {
                $wheres[] = "e.destacat = :destacat";
                $parametres[':destacat'] = $opcions['destacat'];
            }
            
            if (!empty($opcions['data_publicacio_des'])) {
                $wheres[] = "e.data_publicacio >= :data_des";
                $parametres[':data_des'] = $opcions['data_publicacio_des'];
            }
            
            if (!empty($opcions['data_publicacio_fins'])) {
                $wheres[] = "e.data_publicacio <= :data_fins";
                $parametres[':data_fins'] = $opcions['data_publicacio_fins'];
            }
            
            if (!empty($opcions['categoria_id'])) {
                $sql .= " JOIN {$this->taulaCategories} ec ON e.id = ec.entrada_blog_id";
                $wheres[] = "ec.categoria_blog_id = :categoria_id";
                $parametres[':categoria_id'] = $opcions['categoria_id'];
            }
            
            if (!empty($wheres)) {
                $sql .= " WHERE " . implode(' AND ', $wheres);
            }
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 'e.data_publicacio';
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
     * Validar dades d'entrada
     */
    private function validar($dades, $id = null) {
        $errors = [];
        
        if (empty($dades['slug_base'])) {
            $errors[] = "El slug base és obligatori";
        } elseif (strlen($dades['slug_base']) > 191) {
            $errors[] = "El slug base no pot superar els 191 caràcters";
        }
        
        if (empty($dades['autor_id'])) {
            $errors[] = "L'autor és obligatori";
        }
        
        if (isset($dades['estat']) && !in_array($dades['estat'], self::ESTATS_VALIDS)) {
            $errors[] = "L'estat especificat no és vàlid";
        }
        
        if (isset($dades['format']) && !in_array($dades['format'], self::FORMATS_VALIDS)) {
            $errors[] = "El format especificat no és vàlid";
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
        
        // Validar traduccions obligatòries
        if (isset($dades['traduccions'])) {
            foreach ($dades['traduccions'] as $idioma => $traduccio) {
                if (empty($traduccio['titol'])) {
                    $errors[] = "El títol en {$idioma} és obligatori";
                }
                if (empty($traduccio['slug'])) {
                    $errors[] = "El slug en {$idioma} és obligatori";
                }
                if (empty($traduccio['contingut'])) {
                    $errors[] = "El contingut en {$idioma} és obligatori";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Incrementar visites
     */
    public function incrementarVisites($id, $increment = 1) {
        try {
            $sql = "UPDATE {$this->taula} SET visites = visites + :increment WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':increment', $increment);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error incrementant visites: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualitzar comptadors de likes/comentaris
     */
    public function actualitzarComptadors($id, $tipus, $increment = 1) {
        try {
            if ($tipus === 'likes') {
                $sql = "UPDATE {$this->taula} SET comptador_likes = comptador_likes + :increment WHERE id = :id";
            } elseif ($tipus === 'comentaris') {
                $sql = "UPDATE {$this->taula} SET comptador_comentaris = comptador_comentaris + :increment WHERE id = :id";
            } else {
                return false;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':increment', $increment);
            $stmt->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error actualitzant comptadors: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir totes les entrades amb traduccions per administració
     */
    public function obtenirTotes($opcions = []) {
        try {
            $limit = $opcions['limit'] ?? 50;
            $offset = $opcions['offset'] ?? 0;
            $estat = $opcions['estat'] ?? null;
            $orderBy = $opcions['orderBy'] ?? 'e.data_creacio DESC';
            
            $sql = "SELECT e.*, 
                           u.nom as autor_nom
                    FROM {$this->taula} e
                    LEFT JOIN usuaris u ON e.autor_id = u.id";
            
            $params = [];
            
            if ($estat) {
                $sql .= " WHERE e.estat = :estat";
                $params[':estat'] = $estat;
            }
            
            $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $entrades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Afegir traduccions per cada entrada
            foreach ($entrades as &$entrada) {
                $entrada['traduccions'] = $this->obtenirTraduccions($entrada['id']);
            }
            
            return $entrades;
            
        } catch (PDOException $e) {
            error_log("Error obtenint entrades: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir traduccions d'una entrada organitzades per idioma
     */
    private function obtenirTraduccions($entradaId) {
        try {
            $sql = "SELECT t.*, i.codi as idioma_codi 
                    FROM {$this->taulaTraduccions} t
                    JOIN idiomes_blog i ON t.idioma_id = i.id
                    WHERE t.entrada_id = :entrada_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':entrada_id', $entradaId);
            $stmt->execute();
            
            $traduccions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $traduccions[$row['idioma_codi']] = [
                    'titol' => $row['titol'],
                    'contingut' => $row['contingut'],
                    'resum' => $row['resum'],
                    'slug' => $row['slug'],
                    'meta_titol' => $row['meta_titol'],
                    'meta_descripcio' => $row['meta_descripcio'],
                    'estat_traduccio' => $row['estat_traduccio']
                ];
            }
            
            return $traduccions;
            
        } catch (PDOException $e) {
            error_log("Error obtenint traduccions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Eliminar entrada
     */
    public function eliminar($id) {
        try {
            $this->conn->beginTransaction();
            
            // Eliminar relacions amb categories
            $sql = "DELETE FROM {$this->taulaCategories} WHERE entrada_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Eliminar relacions amb tags
            $sql = "DELETE FROM {$this->taulaTags} WHERE entrada_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Eliminar traduccions
            $sql = "DELETE FROM {$this->taulaTraduccions} WHERE entrada_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Eliminar entrada principal
            $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error eliminant entrada: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error eliminant l\'entrada']];
        }
    }
    
    /**
     * Canviar estat d'una entrada
     */
    public function canviarEstat($id, $nouEstat) {
        if (!in_array($nouEstat, self::ESTATS_VALIDS)) {
            return ['success' => false, 'errors' => ['Estat no vàlid']];
        }
        
        try {
            $sql = "UPDATE {$this->taula} SET estat = :estat";
            
            // Si es publica, actualitzar data de publicació
            if ($nouEstat === 'publicat') {
                $sql .= ", data_publicacio = NOW()";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estat', $nouEstat);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error canviant estat: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error canviant l\'estat de l\'entrada']];
        }
    }
}
?>
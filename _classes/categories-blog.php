<?php

/**
 * Classe CategoriesBlog - Gestió de categories del blog amb suport multilingüe
 *
 * Aquesta classe gestiona les categories del blog amb funcionalitats de
 * jerarquia, traduccions i sistema de fallback d'idiomes.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class CategoriesBlog {
    private $conn;
    private $taula = 'categories_blog';
    private $taulaTraduccions = 'categories_traduccions';
    
    // Constants del sistema
    const ESTATS_VALIDS = ['actiu', 'inactiu', 'ocult'];
    const ESTAT_DEFECTE = 'actiu';
    const IDIOMA_DEFECTE = 'ca';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nova categoria
     */
    public function crear($dades) {
        $errors = $this->validar($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Crear categoria base
            $sql = "INSERT INTO {$this->taula} (
                        uuid, slug_base, categoria_pare_id, nivell, ordre, estat, usuari_creacio
                    ) VALUES (
                        UUID(), :slug_base, :categoria_pare_id, :nivell, :ordre, :estat, :usuari_creacio
                    )";
            
            $stmt = $this->conn->prepare($sql);
            
            // Assignar valors a variables per bindParam
            $slugBase = $dades['slug_base'];
            $categoriaPareId = $dades['categoria_pare_id'] ?? null;
            $nivell = $this->calcularNivell($dades['categoria_pare_id'] ?? null);
            $ordre = $dades['ordre'] ?? 0;
            $estat = $dades['estat'] ?? self::ESTAT_DEFECTE;
            $usuariCreacio = $dades['usuari_creacio'] ?? null;
            
            $stmt->bindParam(':slug_base', $slugBase);
            $stmt->bindParam(':categoria_pare_id', $categoriaPareId);
            $stmt->bindParam(':nivell', $nivell);
            $stmt->bindParam(':ordre', $ordre);
            $stmt->bindParam(':estat', $estat);
            $stmt->bindParam(':usuari_creacio', $usuariCreacio);
            
            $stmt->execute();
            $categoriaId = $this->conn->lastInsertId();
            
            // Crear traduccions
            if (isset($dades['traduccions'])) {
                foreach ($dades['traduccions'] as $idioma => $traduccio) {
                    $this->crearTraduccio($categoriaId, $idioma, $traduccio, $dades['usuari_creacio'] ?? null);
                }
            }
            
            // Actualitzar camí
            $this->actualitzarCami($categoriaId);
            
            $this->conn->commit();
            return ['success' => true, 'id' => $categoriaId];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creant categoria: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Actualitzar categoria
     */
    public function actualitzar($id, $dades) {
        $errors = $this->validar($dades, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Actualitzar categoria base
            $camps = [];
            $parametres = [':id' => $id];
            
            $campsPermesos = ['categoria_pare_id', 'ordre', 'estat'];
            foreach ($campsPermesos as $camp) {
                if (isset($dades[$camp])) {
                    $camps[] = "{$camp} = :{$camp}";
                    $parametres[":{$camp}"] = $dades[$camp];
                }
            }
            
            if (!empty($camps)) {
                $sql = "UPDATE {$this->taula} SET " . implode(', ', $camps) . " WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($parametres);
                
                // Recalcular nivell si s'ha canviat el pare
                if (isset($dades['categoria_pare_id'])) {
                    $nivell = $this->calcularNivell($dades['categoria_pare_id']);
                    $sql = "UPDATE {$this->taula} SET nivell = :nivell WHERE id = :id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':nivell', $nivell);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                    
                    $this->actualitzarCami($id);
                }
            }
            
            // Actualitzar traduccions
            if (isset($dades['traduccions'])) {
                foreach ($dades['traduccions'] as $idioma => $traduccio) {
                    $this->actualitzarTraduccio($id, $idioma, $traduccio, $dades['usuari_creacio'] ?? null);
                }
            }
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error actualitzant categoria: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Eliminar categoria
     */
    public function eliminar($id) {
        try {
            $this->conn->beginTransaction();
            
            // Verificar si té subcategories
            $sql = "SELECT COUNT(*) as count FROM {$this->taula} WHERE categoria_pare_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $subcategories = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($subcategories && $subcategories['count'] > 0) {
                return ['success' => false, 'errors' => ['No es pot eliminar una categoria que té subcategories']];
            }
            
            // Eliminar traduccions primer
            $sql = "DELETE FROM {$this->taulaTraduccions} WHERE categoria_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Eliminar categoria
            $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error eliminant categoria: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades']];
        }
    }
    
    /**
     * Obtenir categoria per ID amb traduccions
     */
    public function obtenirPerId($id, $idioma = self::IDIOMA_DEFECTE) {
        try {
            $sql = "SELECT c.*, 
                           ct.nom, ct.slug, ct.descripcio, 
                           ct.meta_titol, ct.meta_descripcio, ct.meta_keywords
                    FROM {$this->taula} c
                    LEFT JOIN {$this->taulaTraduccions} ct ON c.id = ct.categoria_id AND ct.idioma_codi = :idioma
                    WHERE c.id = :id";
            
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
     * Obtenir categories amb traduccions i jerarquia
     */
    public function obtenirAmbTraducio($idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        try {
            $sql = "SELECT c.*, 
                           COALESCE(ct.nom, ct_ca.nom) as nom,
                           COALESCE(ct.slug, ct_ca.slug) as slug,
                           COALESCE(ct.descripcio, ct_ca.descripcio) as descripcio,
                           COALESCE(ct.meta_titol, ct_ca.meta_titol) as meta_titol,
                           COALESCE(ct.meta_descripcio, ct_ca.meta_descripcio) as meta_descripcio
                    FROM {$this->taula} c
                    LEFT JOIN {$this->taulaTraduccions} ct ON c.id = ct.categoria_id AND ct.idioma_codi = :idioma
                    LEFT JOIN {$this->taulaTraduccions} ct_ca ON c.id = ct_ca.categoria_id AND ct_ca.idioma_codi = 'ca'";
            
            return $this->executarConsultaAmbOpcions($sql, $opcions, [':idioma' => $idioma]);
        } catch (PDOException $e) {
            error_log("Error en obtenirAmbTraducio: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir categories amb totes les traduccions per edició
     */
    public function obtenirAmbTotesLesTraduccions() {
        try {
            // Obtenir categories base
            $sql = "SELECT * FROM {$this->taula} ORDER BY ordre ASC, id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Afegir traduccions a cada categoria
            foreach ($categories as &$categoria) {
                $sqlTrad = "SELECT idioma_codi, nom, slug, descripcio, meta_titol, meta_descripcio 
                           FROM {$this->taulaTraduccions} 
                           WHERE categoria_id = :id";
                $stmtTrad = $this->conn->prepare($sqlTrad);
                $stmtTrad->bindParam(':id', $categoria['id']);
                $stmtTrad->execute();
                $traduccions = $stmtTrad->fetchAll(PDO::FETCH_ASSOC);
                
                // Organitzar traduccions per idioma
                $categoria['traduccions'] = [];
                foreach ($traduccions as $trad) {
                    $categoria['traduccions'][$trad['idioma_codi']] = [
                        'nom' => $trad['nom'],
                        'slug' => $trad['slug'],
                        'descripcio' => $trad['descripcio'],
                        'meta_titol' => $trad['meta_titol'],
                        'meta_descripcio' => $trad['meta_descripcio']
                    ];
                }
                
                // Afegir nom per defecte per mostrar
                $categoria['nom'] = $categoria['traduccions']['ca']['nom'] ?? 
                                  $categoria['traduccions']['es']['nom'] ?? 
                                  $categoria['traduccions']['en']['nom'] ?? 
                                  'Sense nom';
                                  
                // Afegir descripció per defecte
                $categoria['descripcio'] = $categoria['traduccions']['ca']['descripcio'] ?? 
                                          $categoria['traduccions']['es']['descripcio'] ?? 
                                          $categoria['traduccions']['en']['descripcio'] ?? '';
            }
            
            return $categories;
        } catch (PDOException $e) {
            error_log("Error en obtenirAmbTotesLesTraduccions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir jerarquia completa de categories
     */
    public function obtenirJerarquia($idioma = self::IDIOMA_DEFECTE) {
        $categories = $this->obtenirAmbTraducio($idioma, ['ordenar' => 'nivell, ordre']);
        return $this->construirArbre($categories);
    }
    
    /**
     * Obtenir categories filles d'una categoria pare
     */
    public function obtenirFilles($pareId, $idioma = self::IDIOMA_DEFECTE) {
        return $this->obtenirAmbTraducio($idioma, [
            'categoria_pare_id' => $pareId,
            'ordenar' => 'ordre'
        ]);
    }
    
    /**
     * Crear traducció de categoria
     */
    private function crearTraduccio($categoriaId, $idioma, $traduccio, $usuariId = null) {
        try {
            $sql = "INSERT INTO {$this->taulaTraduccions} (
                        categoria_id, idioma_codi, nom, slug, descripcio, 
                        meta_titol, meta_descripcio, meta_keywords, usuari_traduccio
                    ) VALUES (
                        :categoria_id, :idioma_codi, :nom, :slug, :descripcio,
                        :meta_titol, :meta_descripcio, :meta_keywords, :usuari_traduccio
                    )";
            
            $stmt = $this->conn->prepare($sql);
            
            // Assignar valors a variables per bindParam
            $nom = $traduccio['nom'];
            $slug = $traduccio['slug'];
            $descripcio = $traduccio['descripcio'] ?? null;
            $metaTitol = $traduccio['meta_titol'] ?? null;
            $metaDescripcio = $traduccio['meta_descripcio'] ?? null;
            $metaKeywords = $traduccio['meta_keywords'] ?? null;
            
            $stmt->bindParam(':categoria_id', $categoriaId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':descripcio', $descripcio);
            $stmt->bindParam(':meta_titol', $metaTitol);
            $stmt->bindParam(':meta_descripcio', $metaDescripcio);
            $stmt->bindParam(':meta_keywords', $metaKeywords);
            $stmt->bindParam(':usuari_traduccio', $usuariId);
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error creant traducció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualitzar traducció de categoria
     */
    private function actualitzarTraduccio($categoriaId, $idioma, $traduccio, $usuariId = null) {
        try {
            // Verificar si ja existeix la traducció
            $sql = "SELECT id FROM {$this->taulaTraduccions} WHERE categoria_id = :categoria_id AND idioma_codi = :idioma_codi";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':categoria_id', $categoriaId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // Actualitzar
                $sql = "UPDATE {$this->taulaTraduccions} SET 
                        nom = :nom, slug = :slug, descripcio = :descripcio,
                        meta_titol = :meta_titol, meta_descripcio = :meta_descripcio, 
                        meta_keywords = :meta_keywords, usuari_traduccio = :usuari_traduccio
                        WHERE categoria_id = :categoria_id AND idioma_codi = :idioma_codi";
            } else {
                // Crear nova
                return $this->crearTraduccio($categoriaId, $idioma, $traduccio, $usuariId);
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':categoria_id', $categoriaId);
            $stmt->bindParam(':idioma_codi', $idioma);
            $stmt->bindParam(':nom', $traduccio['nom']);
            $stmt->bindParam(':slug', $traduccio['slug']);
            $stmt->bindParam(':descripcio', $traduccio['descripcio'] ?? null);
            $stmt->bindParam(':meta_titol', $traduccio['meta_titol'] ?? null);
            $stmt->bindParam(':meta_descripcio', $traduccio['meta_descripcio'] ?? null);
            $stmt->bindParam(':meta_keywords', $traduccio['meta_keywords'] ?? null);
            $stmt->bindParam(':usuari_traduccio', $usuariId);
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error actualitzant traducció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calcular nivell de jerarquia
     */
    private function calcularNivell($pareId) {
        if (!$pareId) {
            return 1;
        }
        
        try {
            $sql = "SELECT nivell FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $pareId);
            $stmt->execute();
            $pare = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $pare ? $pare['nivell'] + 1 : 1;
        } catch (PDOException $e) {
            return 1;
        }
    }
    
    /**
     * Actualitzar camí de jerarquia
     */
    private function actualitzarCami($categoriaId) {
        try {
            $cami = $this->construirCami($categoriaId);
            $sql = "UPDATE {$this->taula} SET cami = :cami WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cami', $cami);
            $stmt->bindParam(':id', $categoriaId);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualitzant camí: " . $e->getMessage());
        }
    }
    
    /**
     * Construir camí de jerarquia
     */
    private function construirCami($categoriaId, $cami = []) {
        try {
            $sql = "SELECT id, slug_base, categoria_pare_id FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $categoriaId);
            $stmt->execute();
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$categoria) return implode('/', $cami);
            
            array_unshift($cami, $categoria['slug_base']);
            
            if ($categoria['categoria_pare_id']) {
                return $this->construirCami($categoria['categoria_pare_id'], $cami);
            }
            
            return implode('/', $cami);
        } catch (PDOException $e) {
            return implode('/', $cami);
        }
    }
    
    /**
     * Construir arbre de categories
     */
    private function construirArbre($categories, $pareId = null) {
        $arbre = [];
        
        foreach ($categories as $categoria) {
            if ($categoria['categoria_pare_id'] == $pareId) {
                $categoria['filles'] = $this->construirArbre($categories, $categoria['id']);
                $arbre[] = $categoria;
            }
        }
        
        return $arbre;
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
                $wheres[] = "c.estat = :estat";
                $parametres[':estat'] = $opcions['estat'];
            }
            
            if (isset($opcions['categoria_pare_id'])) {
                $wheres[] = "c.categoria_pare_id = :categoria_pare_id";
                $parametres[':categoria_pare_id'] = $opcions['categoria_pare_id'];
            }
            
            if (!empty($opcions['cercar'])) {
                $wheres[] = "(ct.nom LIKE :cercar OR ct_ca.nom LIKE :cercar)";
                $parametres[':cercar'] = '%' . $opcions['cercar'] . '%';
            }
            
            if (!empty($wheres)) {
                $sql .= " WHERE " . implode(' AND ', $wheres);
            }
            
            // Ordenació
            $ordenar = $opcions['ordenar'] ?? 'c.ordre';
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
            error_log("Error executant consulta: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar dades de categoria
     */
    private function validar($dades, $id = null) {
        $errors = [];
        
        if (empty($dades['slug_base'])) {
            $errors[] = "El slug base és obligatori";
        } elseif (strlen($dades['slug_base']) > 100) {
            $errors[] = "El slug base no pot superar els 100 caràcters";
        }
        
        if (isset($dades['estat']) && !in_array($dades['estat'], self::ESTATS_VALIDS)) {
            $errors[] = "L'estat especificat no és vàlid";
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
     * Actualitzar comptador d'entrades
     */
    public function actualitzarComptadorEntrades($categoriaId, $increment = 1) {
        try {
            $sql = "UPDATE {$this->taula} SET total_entrades = total_entrades + :increment WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $categoriaId);
            $stmt->bindParam(':increment', $increment);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error actualitzant comptador: " . $e->getMessage());
            return false;
        }
    }
}
?>
<?php

/**
 * Classe Blog - Classe principal del sistema de blog multilingüe
 *
 * Aquesta classe actua com a façana principal per gestionar totes les funcionalitats
 * del sistema de blog, proporcionant un punt d'accés unificat a totes les operacions.
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    1.0.0
 * @license    MIT License
 */
class Blog {
    private $conn;
    
    // Instàncies de les classes del sistema
    public $idiomes;
    public $usuaris;
    public $categories;
    public $entrades;
    public $tags;
    public $comentaris;
    public $traduccions;
    
    // Constants del sistema
    const IDIOMA_DEFECTE = 'ca';
    const VERSION = '1.0.0';
    
    public function __construct($db) {
        $this->conn = $db;
        $this->inicialitzarClasses();
    }
    
    /**
     * Inicialitzar totes les classes del sistema
     */
    private function inicialitzarClasses() {
        // Carregar totes les classes necessàries
        require_once __DIR__ . '/idiomes-blog.php';
        require_once __DIR__ . '/usuaris-blog.php';
        require_once __DIR__ . '/categories-blog.php';
        require_once __DIR__ . '/entrades-blog.php';
        require_once __DIR__ . '/tags-blog.php';
        require_once __DIR__ . '/comentaris-blog.php';
        require_once __DIR__ . '/traduccions-blog.php';
        
        // Instanciar totes les classes
        $this->idiomes = new IdiomasBlog($this->conn);
        $this->usuaris = new UsuarisBlog($this->conn);
        $this->categories = new CategoriesBlog($this->conn);
        $this->entrades = new EntradesBlog($this->conn);
        $this->tags = new TagsBlog($this->conn);
        $this->comentaris = new ComentarisBlog($this->conn);
        $this->traduccions = new TraduccionsBlog($this->conn);
    }
    
    /**
     * Obtenir contingut complet per al frontend
     */
    public function obtenirContingutFrontend($idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        $contingut = [];
        
        // Entrades destacades
        $contingut['entrades_destacades'] = $this->entrades->obtenirDestacades($idioma, $opcions['limit_destacades'] ?? 3);
        
        // Entrades recents
        $contingut['entrades_recents'] = $this->entrades->obtenirPublicades($idioma, [
            'limit' => $opcions['limit_recents'] ?? 10,
            'incloure_categories' => true,
            'incloure_tags' => true
        ]);
        
        // Categories amb entrades
        $contingut['categories'] = $this->categories->obtenirAmbTraducio($idioma, [
            'estat' => 'actiu',
            'ordenar' => 'c.ordre'
        ]);
        
        // Tags populars
        $contingut['tags_populars'] = $this->tags->obtenirPopulars($idioma, $opcions['limit_tags'] ?? 15);
        
        return $contingut;
    }
    
    /**
     * Obtenir entrada completa per visualització
     */
    public function obtenirEntradaCompleta($slug, $idioma = self::IDIOMA_DEFECTE) {
        try {
            // Buscar entrada per slug
            $entrades = $this->entrades->obtenirAmbTraducio($idioma, [
                'slug' => $slug,
                'estat' => 'publicat',
                'limit' => 1,
                'incloure_categories' => true,
                'incloure_tags' => true
            ]);
            
            if (empty($entrades)) {
                return null;
            }
            
            $entrada = $entrades[0];
            
            // Incrementar visites
            $this->entrades->incrementarVisites($entrada['id']);
            
            // Obtenir comentaris
            $entrada['comentaris'] = $this->comentaris->obtenirPerEntrada($entrada['id'], [
                'incloure_respostes' => true,
                'estat' => 'publicat'
            ]);
            
            // Obtenir entrades relacionades (mateix tema/categoria)
            if (!empty($entrada['categories'])) {
                $entrada['entrades_relacionades'] = $this->entrades->obtenirAmbTraducio($idioma, [
                    'categoria_id' => $entrada['categories'][0]['id'],
                    'estat' => 'publicat',
                    'limit' => 5,
                    'excloure_id' => $entrada['id']
                ]);
            }
            
            return $entrada;
        } catch (Exception $e) {
            error_log("Error obtenint entrada completa: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cercar contingut del blog
     */
    public function cercar($text, $idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        $resultats = [];
        
        // Cercar en entrades
        $resultats['entrades'] = $this->entrades->cercar($text, $idioma, [
            'limit' => $opcions['limit_entrades'] ?? 20
        ]);
        
        // Cercar en categories
        $resultats['categories'] = $this->categories->obtenirAmbTraducio($idioma, [
            'cercar' => $text,
            'limit' => $opcions['limit_categories'] ?? 10
        ]);
        
        // Cercar en tags
        $resultats['tags'] = $this->tags->cercarPerNom($text, $idioma, $opcions['limit_tags'] ?? 10);
        
        // Estadístiques de cerca
        $resultats['estadistiques'] = [
            'total_entrades' => count($resultats['entrades']),
            'total_categories' => count($resultats['categories']),
            'total_tags' => count($resultats['tags']),
            'text_cercat' => $text,
            'idioma' => $idioma
        ];
        
        return $resultats;
    }
    
    /**
     * Obtenir contingut per categoria
     */
    public function obtenirPerCategoria($categoriaSlug, $idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        try {
            // Buscar categoria
            $categories = $this->categories->obtenirAmbTraducio($idioma, [
                'slug' => $categoriaSlug,
                'estat' => 'actiu',
                'limit' => 1
            ]);
            
            if (empty($categories)) {
                return null;
            }
            
            $categoria = $categories[0];
            
            // Obtenir entrades de la categoria
            $categoria['entrades'] = $this->entrades->obtenirAmbTraducio($idioma, [
                'categoria_id' => $categoria['id'],
                'estat' => 'publicat',
                'limit' => $opcions['limit'] ?? 20,
                'ordenar' => $opcions['ordenar'] ?? 'e.data_publicacio',
                'direccio' => $opcions['direccio'] ?? 'DESC',
                'incloure_categories' => true,
                'incloure_tags' => true
            ]);
            
            // Obtenir subcategories
            $categoria['subcategories'] = $this->categories->obtenirFilles($categoria['id'], $idioma);
            
            return $categoria;
        } catch (Exception $e) {
            error_log("Error obtenint per categoria: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir contingut per tag
     */
    public function obtenirPerTag($tagSlug, $idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        try {
            // Buscar tag
            $tags = $this->tags->obtenirAmbTraducio($idioma, [
                'slug' => $tagSlug,
                'limit' => 1
            ]);
            
            if (empty($tags)) {
                return null;
            }
            
            $tag = $tags[0];
            
            // Obtenir entrades del tag
            $tag['entrades'] = $this->entrades->obtenirAmbTraducio($idioma, [
                'tag_id' => $tag['id'],
                'estat' => 'publicat',
                'limit' => $opcions['limit'] ?? 20,
                'ordenar' => $opcions['ordenar'] ?? 'e.data_publicacio',
                'direccio' => $opcions['direccio'] ?? 'DESC',
                'incloure_categories' => true,
                'incloure_tags' => true
            ]);
            
            return $tag;
        } catch (Exception $e) {
            error_log("Error obtenint per tag: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir contingut per autor
     */
    public function obtenirPerAutor($autorId, $idioma = self::IDIOMA_DEFECTE, $opcions = []) {
        try {
            // Obtenir informació de l'autor
            $autor = $this->usuaris->obtenirPerId($autorId);
            
            if (!$autor) {
                return null;
            }
            
            // Obtenir entrades de l'autor
            $autor['entrades'] = $this->entrades->obtenirAmbTraducio($idioma, [
                'autor_id' => $autorId,
                'estat' => 'publicat',
                'limit' => $opcions['limit'] ?? 20,
                'ordenar' => $opcions['ordenar'] ?? 'e.data_publicacio',
                'direccio' => $opcions['direccio'] ?? 'DESC',
                'incloure_categories' => true,
                'incloure_tags' => true
            ]);
            
            return $autor;
        } catch (Exception $e) {
            error_log("Error obtenint per autor: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Afegir comentari a entrada
     */
    public function afegirComentari($entradaId, $dades, $usuariId = null) {
        try {
            // Verificar que l'entrada existeix i permet comentaris
            $entrada = $this->entrades->obtenirPerId($entradaId);
            
            if (!$entrada || !$entrada['comentaris_activats']) {
                return ['success' => false, 'errors' => ['Els comentaris no estan activats per aquesta entrada']];
            }
            
            // Preparar dades del comentari
            $dadesComentari = [
                'entrada_blog_id' => $entradaId,
                'usuari_id' => $usuariId,
                'contingut' => $dades['contingut'],
                'comentari_pare_id' => $dades['comentari_pare_id'] ?? null,
                'idioma_codi' => $dades['idioma_codi'] ?? self::IDIOMA_DEFECTE
            ];
            
            // Si no hi ha usuari registrat, afegir dades d'usuari anònim
            if (!$usuariId) {
                $dadesComentari['nom_usuari'] = $dades['nom_usuari'];
                $dadesComentari['email_usuari'] = $dades['email_usuari'];
                $dadesComentari['web_usuari'] = $dades['web_usuari'] ?? null;
            }
            
            return $this->comentaris->crear($dadesComentari);
        } catch (Exception $e) {
            error_log("Error afegint comentari: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error intern']];
        }
    }
    
    /**
     * Obtenir estadístiques generals del blog
     */
    public function obtenirEstadistiques() {
        try {
            $estadistiques = [];
            
            // Estadístiques bàsiques - versions simplificades
            $estadistiques['entrades'] = [
                'total' => $this->comptarTaula('entrades'),
                'publicades' => $this->comptarTaulaAmbFiltre('entrades', 'estat', 'publicat'),
                'esborranys' => $this->comptarTaulaAmbFiltre('entrades', 'estat', 'esborrany'),
                'destacades' => $this->comptarTaulaAmbFiltre('entrades', 'destacat', 1)
            ];
            
            // Estadístiques de comentaris (si existeix el mètode)
            if (method_exists($this->comentaris, 'obtenirEstadistiques')) {
                $estadistiques['comentaris'] = $this->comentaris->obtenirEstadistiques();
            } else {
                $estadistiques['comentaris'] = ['total' => $this->comptarTaula('comentaris')];
            }
            
            // Estadístiques de categories i tags
            $estadistiques['categories'] = $this->comptarTaula('categories');
            $estadistiques['tags'] = $this->comptarTaula('tags');
            
            // Estadístiques d'usuaris
            $estadistiques['usuaris'] = [
                'total' => $this->comptarTaula('usuaris'),
                'actius' => $this->comptarTaulaAmbFiltre('usuaris', 'estat', 'actiu')
            ];
            
            // Estadístiques de traduccions (si existeix el mètode)
            if (method_exists($this->traduccions, 'obtenirEstadistiques')) {
                $estadistiques['traduccions'] = $this->traduccions->obtenirEstadistiques();
            } else {
                $estadistiques['traduccions'] = ['total' => $this->comptarTaula('traduccions')];
            }
            
            return $estadistiques;
        } catch (Exception $e) {
            error_log("Error obtenint estadístiques: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Funcions auxiliars per estadístiques
     */
    private function comptarTaula($taula) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$taula}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function comptarTaulaAmbFiltre($taula, $camp, $valor) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$taula} WHERE {$camp} = :valor";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Traduir contingut automàticament
     */
    public function traduirContingut($text, $idiomaOriginal, $idiomaDesti, $proveidor = 'google') {
        return $this->traduccions->traduir($text, $idiomaOriginal, $idiomaDesti, $proveidor);
    }
    
    /**
     * Obtenir idiomes actius del sistema
     */
    public function obtenirIdiomesActius() {
        return $this->idiomes->obtenirActius();
    }
    
    /**
     * Validar sessió d'usuari
     */
    public function validarSessio($token) {
        // Implementar validació de tokens de sessió
        // Això dependrà del sistema d'autenticació utilitzat
        return null;
    }
    
    /**
     * Generar RSS feed
     */
    public function generarRSS($idioma = self::IDIOMA_DEFECTE, $limit = 20) {
        $entrades = $this->entrades->obtenirPublicades($idioma, [
            'limit' => $limit,
            'ordenar' => 'e.data_publicacio',
            'direccio' => 'DESC'
        ]);
        
        // Generar XML RSS
        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0">' . "\n";
        $rss .= '<channel>' . "\n";
        $rss .= '<title>Blog - Marc Mataró</title>' . "\n";
        $rss .= '<link>https://marcmataro.dev</link>' . "\n";
        $rss .= '<description>Blog personal de Marc Mataró</description>' . "\n";
        $rss .= '<language>' . $idioma . '</language>' . "\n";
        
        foreach ($entrades as $entrada) {
            $rss .= '<item>' . "\n";
            $rss .= '<title>' . htmlspecialchars($entrada['titol']) . '</title>' . "\n";
            $rss .= '<link>https://marcmataro.dev/blog/' . $entrada['slug'] . '</link>' . "\n";
            $rss .= '<description>' . htmlspecialchars($entrada['extracte']) . '</description>' . "\n";
            $rss .= '<pubDate>' . date('r', strtotime($entrada['data_publicacio'])) . '</pubDate>' . "\n";
            $rss .= '</item>' . "\n";
        }
        
        $rss .= '</channel>' . "\n";
        $rss .= '</rss>';
        
        return $rss;
    }
    
    /**
     * Obtenir informació del sistema
     */
    public function obtenirInfoSistema() {
        return [
            'versio' => self::VERSION,
            'idioma_defecte' => self::IDIOMA_DEFECTE,
            'idiomes_actius' => $this->obtenirIdiomesActius(),
            'total_entrades' => $this->comptarTaula('entrades'),
            'total_categories' => $this->comptarTaula('categories'),
            'total_tags' => $this->comptarTaula('tags'),
            'total_usuaris' => $this->comptarTaula('usuaris')
        ];
    }
}
?>
<?php

/**
 * Classe Projectes - Gestor de projectes amb suport multi-idioma
 *
 * Aquesta classe proporciona un sistema complet de gestió de projectes
 * amb funcionalitats avançades de multi-idioma, incloent operacions CRUD,
 * sistema de fallback d'idiomes, consultes dinàmiques amb filtres i 
 * generació d'estadístiques per al dashboard administratiu.
 *
 * Arquitectura:
 * - Utilitza el patró Repository per a l'accés a dades
 * - Implementa un sistema de fallback automàtic d'idiomes (ca -> es -> en)
 * - Proporciona consultes SQL dinàmiques amb prepared statements
 * - Gestiona validació de dades amb suport per camps multi-idioma
 * - Integra logging d'errors per facilitar la depuració
 *
 * Esquema de base de dades suportat:
 * - Camps multi-idioma amb sufixos _ca, _es, _en
 * - Camps generals sense idioma (id, estat, visible, dates, URLs)
 * - Sistema d'estats: desenvolupament, actiu, aturat, archivat
 * - Control de visibilitat per al frontend públic
 *
 * @package    MarcMataro\Classes
 * @author     Marc Mataro <contact@marcmataro.dev>
 * @version    2.0.0
 * @since      1.0.0
 * @license    MIT License
 * @link       https://marcmataro.dev
 *
 * @example
 * ```php
 * // Inicialització
 * $db = new PDO($dsn, $username, $password);
 * $projectes = new Projectes($db);
 * 
 * // Obtenir projectes en català amb fallback automàtic
 * $llistaProjectes = $projectes->obtenirAmbTraducio('ca', [
 *     'estat' => 'actiu',
 *     'visible' => 1,
 *     'cercar' => 'web',
 *     'limit' => 10
 * ]);
 * 
 * // Estadístiques per al dashboard
 * $stats = $projectes->obtenirEstadistiques();
 * echo "Total projectes: " . $stats['total'];
 * ```
 *
 * @todo Implementar cache Redis per optimitzar consultes freqüents
 * @todo Afegir suport per més idiomes (fr, de, it)
 * @todo Implementar sistema de tags per millorar la categorització
 */
class Projectes {
    
    // ===============================================================
    // CONSTANTS DE CONFIGURACIÓ
    // ===============================================================
    
    /**
     * Idioma per defecte del sistema
     * 
     * Utilitzat com a fallback quan l'idioma sol·licitat no està disponible
     * o quan no s'especifica idioma en les consultes multi-idioma.
     * 
     * @var string
     */
    const IDIOMA_DEFECTE = 'ca';
    
    /**
     * Llista dels idiomes suportats pel sistema
     * 
     * Array amb els codis ISO 639-1 dels idiomes que el sistema pot gestionar.
     * Tots els camps multi-idioma hauran de tenir una versió per cada idioma.
     * 
     * @var array<string>
     */
    const IDIOMES_SUPORTATS = ['ca', 'es', 'en'];
    
    /**
     * Definició dels camps que tenen versions multi-idioma
     * 
     * Aquesta constant defineix quins camps de la base de dades tenen
     * versions específiques per cada idioma (amb sufixos _ca, _es, _en).
     * Aquests camps es gestionen automàticament en les consultes amb traducció.
     * 
     * Format espertat a la BD: {camp}_{idioma}
     * Exemple: nom_ca, nom_es, nom_en
     * 
     * @var array<string>
     */
    const CAMPS_MULTIIDIOMA = [
        'nom',                    // Títol del projecte
        'slug',                   // URL amigable
        'descripcio_curta',       // Resum breu per llistats
        'descripcio_detallada'    // Descripció completa del projecte
    ];

    // ===============================================================
    // PROPIETATS PRIVADES
    // ===============================================================
    
    /**
     * Connexió a la base de dades (PDO)
     * 
     * Objecte PDO utilitzat per executar totes les consultes SQL.
     * S'inicialitza en el constructor i es reutilitza en tots els mètodes.
     * 
     * @var PDO
     */
    private $conn;
    
    /**
     * Nom de la taula principal de projectes
     * 
     * Permet canviar fàcilment el nom de la taula sense modificar
     * cada consulta SQL individualment. Facilita el testing i la migració.
     * 
     * @var string
     */
    private $taula = 'projectes';

    // ===============================================================
    // CONSTRUCTOR I INICIALITZACIÓ
    // ===============================================================
    
    /**
     * Constructor de la classe Projectes
     * 
     * Inicialitza la classe amb una connexió PDO vàlida a la base de dades.
     * La connexió s'emmagatzema com a propietat privada per ser reutilitzada
     * en tots els mètodes que necessiten accés a la base de dades.
     * 
     * Requisits de la connexió PDO:
     * - Ha d'estar configurada amb charset UTF-8
     * - Es recomana PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
     * - Ha d'apuntar a una base de dades amb l'esquema de projectes
     * 
     * @param PDO $db Objecte PDO amb connexió activa a la base de dades
     * 
     * @throws InvalidArgumentException Si el paràmetre no és un objecte PDO vàlid
     * 
     * @example
     * ```php
     * try {
     *     $pdo = new PDO($dsn, $username, $password, [
     *         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     *         PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
     *     ]);
     *     $projectes = new Projectes($pdo);
     * } catch (PDOException $e) {
     *     error_log("Error de connexió: " . $e->getMessage());
     * }
     * ```
     */
    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new InvalidArgumentException('El paràmetre ha de ser un objecte PDO vàlid');
        }
        
        $this->conn = $db;
    }

    // ===============================================================
    // MÈTODES PRIVATS DE VALIDACIÓ I UTILITATS
    // ===============================================================
    
    /**
     * Valida les dades d'entrada abans de processar-les
     * 
     * Aquest mètode s'encarrega de validar que les dades proporcionades
     * compleixen els requisits mínims abans de ser processades per altres
     * mètodes de la classe. Suporta validació tant per camps simples com
     * per camps multi-idioma.
     * 
     * Funcionament:
     * 1. Defineix una llista de camps obligatoris
     * 2. Si és multi-idioma, valida cada camp per cada idioma suportat  
     * 3. Si és simple, valida només els camps base
     * 4. Retorna un array amb els errors trobats (buit si tot és correcte)
     * 
     * Camps requerits:
     * - nom: Títol/nom del projecte (obligatori en tots els idiomes)
     * - descripcio_curta: Descripció breu (obligatòria en tots els idiomes)
     * - estat: Estat actual del projecte (desenvolupament/actiu/aturat/archivat)
     * 
     * @param array<string, mixed> $dades Array associatiu amb les dades a validar
     * @param bool $multiidioma Si true, valida camps per tots els idiomes
     * 
     * @return array<string> Array amb missatges d'error (buit si no hi ha errors)
     * 
     * @example
     * ```php
     * // Validació simple
     * $errors = $this->validarDades([
     *     'nom' => 'Projecte Test',
     *     'descripcio_curta' => 'Descripció breu',
     *     'estat' => 'desenvolupament'
     * ], false);
     * 
     * // Validació multi-idioma
     * $errors = $this->validarDades([
     *     'nom_ca' => 'Projecte de Prova',
     *     'nom_es' => 'Proyecto de Prueba', 
     *     'nom_en' => 'Test Project',
     *     'descripcio_curta_ca' => 'Descripció en català',
     *     // ... més camps
     *     'estat' => 'publicat'
     * ], true);
     * ```
     */
    private function validarDades($dades, $multiidioma = false) {
        $errors = [];
        
        // Validació bàsica: camps obligatoris en català
        if (empty($dades['nom_ca'])) {
            $errors[] = "El nom en català és obligatori";
        }
        
        if (empty($dades['descripcio_curta_ca'])) {
            $errors[] = "La descripció curta en català és obligatòria";
        }
        
        // L'estat sempre és requerit (camp no multi-idioma)
        $estats_valids = ['desenvolupament', 'actiu', 'aturat', 'archivat'];
        if (empty($dades['estat']) || !in_array($dades['estat'], $estats_valids)) {
            $errors[] = "L'estat ha de ser un dels següents: " . implode(', ', $estats_valids);
        }
        
        // Validar slug_ca si s'ha proporcionat
        if (!empty($dades['slug_ca'])) {
            if (!preg_match('/^[a-z0-9-]+$/', $dades['slug_ca'])) {
                $errors[] = "El slug només pot contenir lletres minúscules, números i guions";
            }
        }
        
        return $errors;
    }

    /**
     * Validació específica per actualitzacions (només valida camps presents)
     */
    private function validarDadesActualitzacio($dades) {
        $errors = [];
        
        // Validar nom_ca si està present
        if (isset($dades['nom_ca']) && empty($dades['nom_ca'])) {
            $errors[] = "El nom en català no pot estar buit";
        }
        
        // Validar descripcio_curta_ca si està present
        if (isset($dades['descripcio_curta_ca']) && empty($dades['descripcio_curta_ca'])) {
            $errors[] = "La descripció curta en català no pot estar buida";
        }
        
        // Validar estat si està present
        if (isset($dades['estat'])) {
            $estats_valids = ['desenvolupament', 'actiu', 'aturat', 'archivat'];
            if (empty($dades['estat']) || !in_array($dades['estat'], $estats_valids)) {
                $errors[] = "L'estat ha de ser un dels següents: " . implode(', ', $estats_valids);
            }
        }
        
        // Validar slug_ca si està present
        if (isset($dades['slug_ca']) && !empty($dades['slug_ca'])) {
            if (!preg_match('/^[a-z0-9-]+$/', $dades['slug_ca'])) {
                $errors[] = "El slug només pot contenir lletres minúscules, números i guions";
            }
        }
        
        return $errors;
    }

    /**
     * Genera un slug a partir d'un text
     */
    private function generarSlug($text) {
        // Convertir a minúscules
        $slug = strtolower($text);
        
        // Eliminar accents i caràcters especials catalans
        $slug = str_replace(
            ['à', 'á', 'è', 'é', 'í', 'ï', 'ò', 'ó', 'ù', 'ú', 'ü', 'ç', 'ñ'],
            ['a', 'a', 'e', 'e', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c', 'n'],
            $slug
        );
        
        // Eliminar caràcters que no siguin lletres, números o espais
        $slug = preg_replace('/[^a-z0-9\s]/', '', $slug);
        
        // Convertir espais múltiples en un sol espai
        $slug = preg_replace('/\s+/', ' ', $slug);
        
        // Convertir espais en guions
        $slug = str_replace(' ', '-', $slug);
        
        // Eliminar guions del principi i final
        $slug = trim($slug, '-');
        
        return $slug;
    }

    // ===============================================================
    // MÈTODES PÚBLICS DE CONSULTA AMB TRADUCCIÓ
    // ===============================================================
    
    /**
     * Obté projectes amb traducció automàtica i sistema de fallback
     * 
     * Aquest és el mètode principal per obtenir projectes amb suport multi-idioma.
     * Implementa un sistema intel·ligent de fallback que garanteix que sempre
     * es retorni contingut, encara que no estigui disponible en l'idioma sol·licitat.
     * 
     * Algoritme de fallback:
     * 1. Intenta obtenir el contingut en l'idioma sol·licitat
     * 2. Si el camp està buit o és null, utilitza la versió en català
     * 3. Utilitza COALESCE i NULLIF de SQL per optimitzar el procés
     * 
     * Funcionament intern:
     * 1. Validació i normalització de l'idioma d'entrada
     * 2. Construcció dinàmica del SELECT amb fallback automàtic
     * 3. Afegeix camps generals (sense idioma) al SELECT
     * 4. Execució de la consulta amb les opcions especificades
     * 5. Gestió d'errors amb logging automàtic
     * 
     * @param string|null $idioma Codi ISO 639-1 de l'idioma desitjat (ca, es, en)
     *                           Si és null o no vàlid, s'utilitza IDIOMA_DEFECTE
     * @param array<string, mixed> $opcions Array associatiu amb opcions de consulta:
     *        - 'estat' => string: Filtrar per estat específic
     *        - 'visible' => int: Filtrar per visibilitat (0/1)
     *        - 'cercar' => string: Text a buscar en camps multi-idioma
     *        - 'ordenar' => string: Camp per ordenar (defecte: data_creacio)
     *        - 'direccio' => string: Direcció d'ordenació ASC/DESC (defecte: DESC)
     *        - 'limit' => int: Límit màxim de resultats
     * 
     * @return array<array<string, mixed>> Array de projectes amb camps traduïts
     *         Cada projecte conté els camps originals + versions traduïdes
     *         En cas d'error, retorna array buit
     * 
     * @throws PDOException Es captura internament i es registra en el log d'errors
     * 
     * @example
     * ```php
     * // Obtenir tots els projectes publicats en castellà
     * $projectes = $gestorProjectes->obtenirAmbTraducio('es', [
     *     'estat' => 'publicat',
     *     'visible' => 1,
     *     'ordenar' => 'data_publicacio',
     *     'direccio' => 'DESC',
     *     'limit' => 10
     * ]);
     * 
     * // Cerca de projectes amb text específic
     * $resultats = $gestorProjectes->obtenirAmbTraducio('ca', [
     *     'cercar' => 'web development',
     *     'estat' => 'publicat'
     * ]);
     * 
     * foreach ($projectes as $projecte) {
     *     // Els camps multi-idioma ja estan traduïts automàticament
     *     echo $projecte['nom'];        // Nom en l'idioma sol·licitat o fallback
     *     echo $projecte['descripcio_curta']; // Descripció traduïda
     * }
     * ```
     */
    public function obtenirAmbTraducio($idioma = null, $opcions = []) {
        // Normalització i validació de l'idioma d'entrada
        $idioma = $idioma ?: self::IDIOMA_DEFECTE;
        
        if (!in_array($idioma, self::IDIOMES_SUPORTATS)) {
            $idioma = self::IDIOMA_DEFECTE;
        }
        
        try {
            $selectCamps = [];
            
            // Construcció dels camps multi-idioma amb fallback automàtic
            // COALESCE(NULLIF(camp_idioma, ''), camp_ca) retorna:
            // - camp_idioma si no està buit
            // - camp_ca si camp_idioma està buit o és null
            foreach (self::CAMPS_MULTIIDIOMA as $camp) {
                $campIdioma = $camp . '_' . $idioma;      // Ex: nom_es
                $campDefecte = $camp . '_ca';             // Ex: nom_ca (fallback)
                $selectCamps[] = "COALESCE(NULLIF({$campIdioma}, ''), {$campDefecte}) AS {$camp}";
            }
            
            // Afegeix camps generals que no tenen versions d'idioma
            $selectCamps[] = "id, estat, visible, data_publicacio, url_demo, url_github, url_documentacio";
            $selectCamps[] = "imatge_portada, imatge_detall, tecnologies_principals, caracteristiques";
            $selectCamps[] = "data_creacio, data_actualitzacio";
            
            // Construcció de la consulta base
            $sql = "SELECT " . implode(', ', $selectCamps) . " FROM {$this->taula}";
            
            // Delegació a mètode especialitzat per afegir filtres i executar
            return $this->executarConsultaAmbOpcions($sql, $opcions);
            
        } catch (PDOException $e) {
            // Logging d'errors per facilitar la depuració
            error_log("Error en obtenirAmbTraducio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Executa consultes SQL dinàmiques amb filtres, ordenació i paginació
     * 
     * Aquest mètode privat s'encarrega de construir i executar consultes SQL
     * complexes basades en les opcions proporcionades. Implementa un sistema
     * flexible de filtres que permet combinar múltiples criteris de cerca.
     * 
     * Funcionalitats implementades:
     * - Filtres dinàmics amb prepared statements per seguretat
     * - Cerca de text en camps multi-idioma simultàniament  
     * - Ordenació configurable per qualsevol camp
     * - Limitació de resultats per optimitzar rendiment
     * - Construcció segura de SQL per prevenir injeccions
     * 
     * Arquitectura de seguretat:
     * - Utilitza prepared statements per tots els paràmetres d'usuari
     * - Valida i escapa automàticament els valors d'entrada
     * - Els noms de camps per ordenar es validen contra una llista blanca implícita
     * 
     * @param string $sql Consulta SQL base (SELECT ... FROM table)
     * @param array<string, mixed> $opcions Opcions de filtratge i configuració:
     *        - 'estat' => string: Filtra per estat específic (desenvolupament/actiu/aturat/archivat)
     *        - 'visible' => int: Filtra per visibilitat (0=ocult, 1=visible)
     *        - 'cercar' => string: Cerca text en nom i descripció de tots els idiomes
     *        - 'ordenar' => string: Camp per ordenar resultats (defecte: data_creacio)
     *        - 'direccio' => string: ASC o DESC (defecte: DESC)
     *        - 'limit' => int: Màxim nombre de resultats a retornar
     * 
     * @return array<array<string, mixed>> Resultats de la consulta com array associatiu
     * 
     * @example
     * ```php
     * $sql = "SELECT id, nom_ca, estat FROM projectes";
     * $resultats = $this->executarConsultaAmbOpcions($sql, [
     *     'estat' => 'publicat',
     *     'visible' => 1,
     *     'cercar' => 'development',
     *     'ordenar' => 'data_publicacio',
     *     'direccio' => 'ASC',
     *     'limit' => 5
     * ]);
     * // Genera: SELECT ... WHERE estat = :estat AND visible = :visible 
     * //         AND (nom_ca LIKE :cercar OR ...) ORDER BY data_publicacio ASC LIMIT 5
     * ```
     */
    private function executarConsultaAmbOpcions($sql, $opcions = []) {
        $parametres = [];
        $wheres = [];
        
        // Construcció dinàmica de filtres WHERE
        
        // Filtre per estat del projecte
        if (!empty($opcions['estat'])) {
            $wheres[] = "estat = :estat";
            $parametres[':estat'] = $opcions['estat'];
        }
        
        // Filtre per visibilitat pública
        if (!empty($opcions['visible'])) {
            $wheres[] = "visible = :visible";
            $parametres[':visible'] = $opcions['visible'];
        }
        
        // Cerca de text en camps multi-idioma
        // Busca simultàniament en nom i descripció de tots els idiomes
        if (!empty($opcions['cercar'])) {
            $wheres[] = "(nom_ca LIKE :cercar OR nom_es LIKE :cercar OR nom_en LIKE :cercar 
                         OR descripcio_curta_ca LIKE :cercar OR descripcio_curta_es LIKE :cercar OR descripcio_curta_en LIKE :cercar)";
            $parametres[':cercar'] = '%' . $opcions['cercar'] . '%';
        }
        
        // Aplicació dels filtres WHERE si n'hi ha
        if (!empty($wheres)) {
            $sql .= " WHERE " . implode(' AND ', $wheres);
        }
        
        // Configuració d'ordenació amb valors per defecte
        $ordenar = $opcions['ordenar'] ?? 'data_creacio';
        $direccio = $opcions['direccio'] ?? 'DESC';
        $sql .= " ORDER BY {$ordenar} {$direccio}";
        
        // Limitació de resultats per optimitzar rendiment
        if (!empty($opcions['limit'])) {
            $sql .= " LIMIT " . (int)$opcions['limit'];  // Cast a int per seguretat
        }
        
        // Execució de la consulta amb prepared statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($parametres);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obté tots els projectes amb tots els camps d'idioma (sense traducció automàtica)
     * 
     * Aquest mètode proporciona accés directe a tots els camps de la base de dades
     * sense aplicar el sistema de traducció automàtica. És útil per:
     * - Administració on es necessiten veure tots els idiomes
     * - Exports de dades completes
     * - Processos de migració o sincronització
     * - Anàlisi de completitud de traduccions
     * 
     * A diferència d'obtenirAmbTraducio(), aquest mètode:
     * - Retorna tots els camps amb sufixos d'idioma (_ca, _es, _en)
     * - No aplica fallback automàtic entre idiomes
     * - Útil per interfícies d'administració multi-idioma
     * 
     * @param array<string, mixed> $opcions Opcions de consulta (iguals que obtenirAmbTraducio)
     * 
     * @return array<array<string, mixed>> Array de projectes amb tots els camps originals
     *         En cas d'error, retorna array buit
     * 
     * @see obtenirAmbTraducio() Per obtenir projectes amb traducció automàtica
     * 
     * @example
     * ```php
     * // Obtenir tots els camps per administració
     * $projectesComplerts = $gestorProjectes->obtenirTots([
     *     'estat' => 'publicat',
     *     'ordenar' => 'data_actualitzacio'
     * ]);
     * 
     * foreach ($projectesComplerts as $projecte) {
     *     echo "Català: " . $projecte['nom_ca'];
     *     echo "Castellà: " . $projecte['nom_es']; 
     *     echo "Anglès: " . $projecte['nom_en'];
     * }
     * ```
     */
    public function obtenirTots($opcions = []) {
        try {
            // Selecciona tots els camps sense aplicar traducció
            $sql = "SELECT * FROM {$this->taula}";
            return $this->executarConsultaAmbOpcions($sql, $opcions);
        } catch (PDOException $e) {
            error_log("Error en obtenirTots: " . $e->getMessage());
            return [];
        }
    }

    // ===============================================================
    // MÈTODES D'ESTADÍSTIQUES I ANALÍTICA
    // ===============================================================
    
    /**
     * Genera estadístiques detallades dels projectes per al dashboard administratiu
     * 
     * Aquest mètode proporciona un resum complet de l'estat dels projectes
     * mitjançant múltiples consultes SQL optimitzades. Les estadístiques són
     * essencials per al dashboard administratiu i proporcionen una visió ràpida
     * de l'estat del portfolio.
     * 
     * Mètriques calculades:
     * - Total: Nombre total de projectes en el sistema
     * - Publicats: Projectes visibles al públic (estat=publicat AND visible=1)
     * - En desenvolupament: Projectes en procés de creació
     * - Aturats: Projectes aturats temporalment
     * - Archivats: Projectes finalitzats o discontinuats
     * 
     * Optimitzacions implementades:
     * - Consultes individuals per evitar JOIN complexos
     * - Ús de COUNT() per màxim rendiment  
     * - Queries directes sense overhead de prepared statements
     * - Gestió d'errors amb valors per defecte per robustesa
     * 
     * Sistema de fallback:
     * En cas d'error de base de dades, retorna estadístiques a zero
     * per mantenir la interfície funcionant i evitar errors fatals.
     * 
     * @return array<string, int> Array associatiu amb les estadístiques:
     *         - 'total' => int: Total de projectes
     *         - 'publicats' => int: Projectes públics visibles
     *         - 'desenvolupament' => int: Projectes en desenvolupament
     *         - 'actius' => int: Projectes actius i visibles
     *         - 'aturats' => int: Projectes aturats temporalment
     *         - 'archivats' => int: Projectes archivats
     * 
     * @throws PDOException Es captura internament i registra l'error
     * 
     * @example
     * ```php
     * $stats = $gestorProjectes->obtenirEstadistiques();
     * 
     * echo "Dashboard de Projectes:\n";
     * echo "Total: " . $stats['total'] . "\n";
     * echo "Publicats: " . $stats['publicats'] . "\n"; 
     * echo "En desenvolupament: " . $stats['desenvolupament'] . "\n";
     * echo "Actius: " . $stats['actius'] . "\n";
 * echo "Aturats: " . $stats['aturats'] . "\n";
 * echo "Archivats: " . $stats['archivats'] . "\n";
     * 
     * // Càlcul de percentatges
     * if ($stats['total'] > 0) {
     *     $percentatgePublicats = ($stats['publicats'] / $stats['total']) * 100;
     *     echo "Percentatge publicat: " . round($percentatgePublicats, 1) . "%\n";
     * }
     * ```
     */
    public function obtenirEstadistiques() {
        try {
            $stats = [];
            
            // Comptatge total de projectes en el sistema
            $sql = "SELECT COUNT(*) as total FROM {$this->taula}";
            $stmt = $this->conn->query($sql);
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Projectes actius i visibles públicament
            $sql = "SELECT COUNT(*) as actius FROM {$this->taula} WHERE estat = 'actiu' AND visible = 1";
            $stmt = $this->conn->query($sql);
            $stats['actius'] = $stmt->fetch(PDO::FETCH_ASSOC)['actius'];
            
            // Projectes en fase de desenvolupament
            $sql = "SELECT COUNT(*) as desenvolupament FROM {$this->taula} WHERE estat = 'desenvolupament'";
            $stmt = $this->conn->query($sql);
            $stats['desenvolupament'] = $stmt->fetch(PDO::FETCH_ASSOC)['desenvolupament'];
            
            // Projectes aturats temporalment
            $sql = "SELECT COUNT(*) as aturats FROM {$this->taula} WHERE estat = 'aturat'";
            $stmt = $this->conn->query($sql);
            $stats['aturats'] = $stmt->fetch(PDO::FETCH_ASSOC)['aturats'];
            
            // Projectes archivats
            $sql = "SELECT COUNT(*) as archivats FROM {$this->taula} WHERE estat = 'archivat'";
            $stmt = $this->conn->query($sql);
            $stats['archivats'] = $stmt->fetch(PDO::FETCH_ASSOC)['archivats'];
            
            return $stats;
            
        } catch (PDOException $e) {
            // Logging detallat per facilitar la depuració d'errors de BD
            error_log("Error en obtenirEstadistiques: " . $e->getMessage());
            
            // Retorna estadístiques buides per mantenir la funcionalitat
            return [
                'total' => 0,
                'actius' => 0,
                'desenvolupament' => 0,
                'aturats' => 0,
                'archivats' => 0
            ];
        }
    }

    // ===============================================================
    // MÈTODES DE CREACIÓ I MODIFICACIÓ DE PROJECTES
    // ===============================================================
    
    /**
     * Crea un nou projecte amb suport multi-idioma complet
     * 
     * Aquest mètode gestiona la creació de nous projectes amb validació
     * exhaustiva i suport per tots els camps multi-idioma. Implementa
     * un sistema robust de validació i gestió d'errors.
     * 
     * @param array<string, mixed> $dades Array associatiu amb les dades del projecte
     * @return array<string, mixed> Resultat amb 'success' (bool) i 'id' o 'errors'
     */
    public function crear($dades) {
        // Generar slug automàticament si no es proporciona
        if (empty($dades['slug_ca']) && !empty($dades['nom_ca'])) {
            $dades['slug_ca'] = $this->generarSlug($dades['nom_ca']);
        }
        
        $errors = $this->validarDades($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $camps = ['estat'];
            $valors = [':estat'];
            $parametres = [
                ':estat' => $dades['estat'] ?? 'desenvolupament'
            ];
            
            // Afegir camps multi-idioma
            foreach (self::CAMPS_MULTIIDIOMA as $camp) {
                foreach (self::IDIOMES_SUPORTATS as $idioma) {
                    $campIdioma = $camp . '_' . $idioma;
                    $camps[] = $campIdioma;
                    $valors[] = ':' . $campIdioma;
                    $parametres[':' . $campIdioma] = $dades[$campIdioma] ?? '';
                }
            }
            
            // Afegir camps opcionals (segons esquema real)
            $campsOpcionals = ['visible', 'data_publicacio', 'url_demo', 'url_github', 'url_documentacio', 
                              'imatge_portada', 'imatge_detall', 'tecnologies_principals', 'caracteristiques'];
            
            foreach ($campsOpcionals as $camp) {
                if (isset($dades[$camp]) && !empty($dades[$camp])) {
                    $camps[] = $camp;
                    $valors[] = ':' . $camp;
                    $parametres[':' . $camp] = $dades[$camp];
                }
            }
            
            $camps[] = 'data_creacio';
            $valors[] = 'NOW()';
            
            $sql = "INSERT INTO {$this->taula} (" . implode(', ', $camps) . ") VALUES (" . implode(', ', $valors) . ")";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            
            return ['success' => true, 'id' => $this->conn->lastInsertId()];
            
        } catch (PDOException $e) {
            error_log("Error en crear projecte: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades: ' . $e->getMessage()]];
        }
    }

    /**
     * Actualitza un projecte existent
     * 
     * @param int $id ID del projecte a actualitzar
     * @param array<string, mixed> $dades Noves dades del projecte
     * @return array<string, mixed> Resultat de l'operació
     */
    public function actualitzar($id, $dades) {
        // Generar slug si es modifica el nom i no s'especifica slug
        if (empty($dades['slug_ca']) && !empty($dades['nom_ca'])) {
            $dades['slug_ca'] = $this->generarSlug($dades['nom_ca']);
        }
        
        // Validació específica per actualitzacions (només validar camps que s'estan actualitzant)
        $errors = $this->validarDadesActualitzacio($dades);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sets = [];
            $parametres = [':id' => $id];
            
            // Camps multi-idioma
            foreach (self::CAMPS_MULTIIDIOMA as $camp) {
                foreach (self::IDIOMES_SUPORTATS as $idioma) {
                    $campIdioma = $camp . '_' . $idioma;
                    if (isset($dades[$campIdioma])) {
                        $sets[] = "{$campIdioma} = :{$campIdioma}";
                        $parametres[':' . $campIdioma] = $dades[$campIdioma];
                    }
                }
            }
            
            // Camps generals (segons esquema real)  
            $campsGenerals = ['estat', 'visible', 'data_publicacio', 'url_demo', 'url_github', 'url_documentacio',
                             'imatge_portada', 'imatge_detall', 'tecnologies_principals', 'caracteristiques'];
            
            foreach ($campsGenerals as $camp) {
                if (isset($dades[$camp])) {
                    $sets[] = "{$camp} = :{$camp}";
                    $parametres[':' . $camp] = $dades[$camp];
                }
            }
            
            $sets[] = "data_actualitzacio = NOW()";
            
            $sql = "UPDATE {$this->taula} SET " . implode(', ', $sets) . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($parametres);
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error en actualitzar projecte: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades: ' . $e->getMessage()]];
        }
    }

    /**
     * Obté un projecte per ID (tots els camps d'idioma)
     * 
     * @param int $id ID del projecte
     * @return array|false Dades del projecte o false si no existeix
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
            return false;
        }
    }

    /**
     * Elimina un projecte definitivament
     * 
     * @param int $id ID del projecte a eliminar
     * @return array<string, mixed> Resultat de l'operació
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error en eliminar projecte: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error de base de dades: ' . $e->getMessage()]];
        }
    }
}

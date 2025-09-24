# Documentació de la Classe Projectes

## 📋 Resum Executiu

La classe `Projectes` és un gestor avançat de projectes amb suport complet per a múltiples idiomes, dissenyada per proporcionar una API robusta i flexible per a la gestió de portfolios multilingües. Implementa patrons de disseny moderns, seguretat avançada i un sistema intel·ligent de fallback d'idiomes.

## 🏗️ Arquitectura i Patrons de Disseny

### Patró Repository
- **Separació de responsabilitats**: Aïlla la lògica d'accés a dades de la lògica de negoci
- **Testabilitat**: Permet mock fàcil per a testing unitari
- **Mantenibilitat**: Centralitza totes les operacions de base de dades

### Patró Fallback Multi-idioma
- **Robustesa**: Garanteix contingut sempre disponible
- **Prioritat d'idiomes**: Català → Castellà → Anglès
- **Optimització SQL**: Utilitza `COALESCE` per rendiment màxim

### Seguretat per Disseny
- **Prepared Statements**: Tots els paràmetres d'usuari utilitzen binding segur
- **Validació d'entrada**: Validació exhaustiva abans de processar dades
- **Logging d'errors**: Sistema complet de registre per auditoria

## 🚀 Guia d'Ús Ràpid

### Inicialització Bàsica
```php
<?php
require_once '_classes/projectes.php';

// Configuració de connexió PDO
$dsn = "mysql:host=localhost;dbname=marcmataro_dev;charset=utf8mb4";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Instanciació del gestor
$gestorProjectes = new Projectes($pdo);
```

### Casos d'Ús Principals

#### 1. Frontend Públic amb Traducció Automàtica
```php
// Obtenir projectes publicats en l'idioma de l'usuari
$idiomeUsuari = $_GET['lang'] ?? 'ca';
$projectesPublics = $gestorProjectes->obtenirAmbTraducio($idiomeUsuari, [
    'estat' => 'publicat',
    'visible' => 1,
    'ordenar' => 'data_publicacio',
    'direccio' => 'DESC'
]);

foreach ($projectesPublics as $projecte) {
    // Els camps ja estan traduïts automàticament
    echo "<h3>" . htmlspecialchars($projecte['nom']) . "</h3>";
    echo "<p>" . htmlspecialchars($projecte['descripcio_curta']) . "</p>";
}
```

#### 2. Panel d'Administració Multilingüe
```php
// Obtenir tots els camps per edició administrativa
$totsElsProjectes = $gestorProjectes->obtenirTots([
    'ordenar' => 'data_actualitzacio',
    'direccio' => 'DESC'
]);

foreach ($totsElsProjectes as $projecte) {
    echo "Català: " . $projecte['nom_ca'] . "<br>";
    echo "Castellà: " . $projecte['nom_es'] . "<br>";
    echo "Anglès: " . $projecte['nom_en'] . "<br>";
}
```

#### 3. Dashboard amb Estadístiques en Temps Real
```php
$estadistiques = $gestorProjectes->obtenirEstadistiques();

echo "<div class='dashboard-stats'>";
echo "<div class='stat-card'>";
echo "<h4>Total Projectes</h4>";
echo "<span class='number'>{$estadistiques['total']}</span>";
echo "</div>";

echo "<div class='stat-card'>";
echo "<h4>Publicats</h4>";
echo "<span class='number'>{$estadistiques['publicats']}</span>";
echo "</div>";
echo "</div>";
```

#### 4. Sistema de Cerca Avançat
```php
// Cerca en múltiples idiomes simultàniament
$resultats = $gestorProjectes->obtenirAmbTraducio('ca', [
    'cercar' => 'web development',
    'estat' => 'publicat',
    'visible' => 1,
    'limit' => 20
]);

// La cerca funciona automàticament en nom_ca, nom_es, nom_en, 
// descripcio_curta_ca, descripcio_curta_es, descripcio_curta_en
```

## 📊 Esquema de Base de Dades

### Taula: `projectes`

#### Camps Multi-idioma (amb sufixos _ca, _es, _en)
| Camp | Tipus | Descripció |
|------|-------|------------|
| `nom_*` | VARCHAR(255) | Títol del projecte |
| `descripcio_curta_*` | TEXT | Descripció breu (llistats) |
| `descripcio_completa_*` | TEXT | Descripció detallada |
| `objectius_*` | TEXT | Objectius del projecte |
| `funcionalitats_*` | TEXT | Funcionalitats principals |
| `reptes_tecnics_*` | TEXT | Reptes tècnics superats |
| `aprenentatges_*` | TEXT | Coneixements adquirits |
| `millores_futures_*` | TEXT | Millores planificades |

#### Camps Generals (sense idioma)
| Camp | Tipus | Descripció |
|------|-------|------------|
| `id` | INT PRIMARY KEY | Identificador únic |
| `estat` | ENUM | esborrany, desenvolupament, publicat |
| `visible` | TINYINT(1) | Visibilitat pública (0/1) |
| `data_publicacio` | DATETIME | Data de publicació |
| `url_demo` | VARCHAR(500) | Enllaç a demostració |
| `url_github` | VARCHAR(500) | Repositori GitHub |
| `url_documentacio` | VARCHAR(500) | Documentació tècnica |
| `imatge_portada` | VARCHAR(255) | Imatge principal |
| `imatge_detall` | VARCHAR(255) | Imatge detallada |
| `tecnologies_principals` | TEXT | Stack tecnològic |
| `caracteristiques` | TEXT | Característiques tècniques |
| `data_creacio` | TIMESTAMP | Data de creació automàtica |
| `data_actualitzacio` | TIMESTAMP | Última actualització |

## 🔧 Configuració i Constants

### Constants d'Idioma
```php
const IDIOMA_DEFECTE = 'ca';                    // Idioma fallback
const IDIOMES_SUPORTATS = ['ca', 'es', 'en'];  // Idiomes disponibles
```

### Configuration Recomanada de PDO
```php
$opcions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::ATTR_EMULATE_PREPARES => false
];
```

## ⚡ Optimitzacions i Rendiment

### Consultes Optimitzades
- **Índexs recomanats**: `estat`, `visible`, `data_publicacio`, `data_creacio`
- **COALESCE SQL**: Fallback d'idioma a nivell de base de dades
- **Prepared Statements**: Cache de consultes per múltiples execucions

### Millors Pràctiques d'Ús
```php
// ✅ Correcte: Reutilitzar instància
$gestor = new Projectes($pdo);
$projectes1 = $gestor->obtenirAmbTraducio('ca');
$projectes2 = $gestor->obtenirAmbTraducio('es');

// ❌ Evitar: Múltiples instàncies
$gestor1 = new Projectes($pdo);
$gestor2 = new Projectes($pdo);
```

## 🛡️ Seguretat i Validació

### Proteccions Implementades
- **SQL Injection**: Tots els inputs utilitzen prepared statements
- **XSS Prevention**: Recorda fer `htmlspecialchars()` al frontend
- **Input Validation**: Validació de tipus i formats abans de processar

### Gestió d'Errors
```php
// Els mètodes retornen arrays buits en cas d'error
$projectes = $gestor->obtenirAmbTraducio('ca');
if (empty($projectes)) {
    // Comprovar logs d'error per detalls
    error_log("No s'han pogut obtenir projectes");
}
```

## 🧪 Testing i Depuració

### Casos de Test Recomanats
```php
// Test de fallback d'idioma
$projectes = $gestor->obtenirAmbTraducio('idioma_inexistent');
// Hauria de retornar contingut en català

// Test de cerca multi-idioma  
$resultats = $gestor->obtenirAmbTraducio('ca', ['cercar' => 'test']);
// Hauria de buscar en tots els camps d'idioma

// Test d'estadístiques
$stats = $gestor->obtenirEstadistiques();
assert(is_array($stats));
assert(isset($stats['total']));
```

### Logging i Monitoring
- Tots els errors es registren automàticament amb `error_log()`
- Configurar log rotation per logs de PHP
- Monitor·litzar consultes lentes amb MySQL slow query log

## 📈 Escalabilitat i Futures Millores

### Roadmap Tecnològic
- **Cache Redis**: Per consultes freqüents
- **Nous idiomes**: Extensió fàcil del sistema actual  
- **Tags/Categories**: Sistema de taxonomia avançat
- **API REST**: Exposició com a servei web

### Extensió de la Classe
```php
class ProjectesAvançats extends Projectes {
    public function obtenirAmbCache($idioma, $opcions = []) {
        $clauCache = md5(serialize([$idioma, $opcions]));
        // Implementar cache Redis...
    }
}
```

---

**Versió**: 2.0.0  
**Autor**: Marc Mataro  
**Darrera actualització**: Setembre 2025
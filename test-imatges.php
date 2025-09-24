<?php
// Script per testejar les imatges dels projectes

// Funció per obtenir el base URL del projecte
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Detectar el subdirectori automàticament
    $scriptName = $_SERVER['SCRIPT_NAME']; // Ex: /marcmataro.dev/test-imatges.php
    $pathParts = explode('/', $scriptName);
    
    // Si estem en un subdirectori (com /marcmataro.dev/), el detectem
    if (count($pathParts) > 2 && $pathParts[1] !== '') {
        return '/' . $pathParts[1]; // Retorna /marcmataro.dev
    }
    
    return ''; // Si estem a l'arrel del domini
}

$baseUrl = getBaseUrl();

require_once '_classes/connexio.php';
require_once '_classes/projectes.php';

echo "<h2>Test d'imatges dels projectes</h2>\n";
echo "<p><strong>Base URL detectat:</strong> " . ($baseUrl ?: '(arrel del domini)') . "</p>\n";

$connexio = Connexio::getInstance();
$db = $connexio->getConnexio();
$projectes = new Projectes($db);
$llistaProjectes = $projectes->obtenirTots();

echo "<h3>Directoris d'imatges:</h3>\n";
echo "<ul>\n";
echo "<li>img/Projects/ - " . (is_dir(__DIR__ . '/img/Projects/') ? 'Existeix' : 'NO EXISTEIX') . " <strong>(Carpeta principal)</strong></li>\n";
echo "<li>img/placeholder-project.jpg - " . (file_exists(__DIR__ . '/img/placeholder-project.jpg') ? 'Existeix' : 'NO EXISTEIX') . "</li>\n";
echo "</ul>\n";

if (is_dir(__DIR__ . '/img/Projects/')) {
    $fitxersProjects = scandir(__DIR__ . '/img/Projects/');
    $fitxersProjects = array_diff($fitxersProjects, ['.', '..']);
    echo "<h3>Fitxers a img/Projects/ (" . count($fitxersProjects) . "):</h3>\n";
    echo "<ul>\n";
    foreach ($fitxersProjects as $fitxer) {
        echo "<li>$fitxer</li>\n";
    }
    echo "</ul>\n";
}

echo "<h3>Projectes i les seves imatges:</h3>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>ID</th><th>Nom</th><th>Imatge portada</th><th>Existeix a Projects/</th><th>Ruta final</th></tr>\n";

foreach ($llistaProjectes as $projecte) {
    $imatgePortada = $projecte['imatge_portada'] ?? '';
    
    $existeixProjects = false;
    $rutaFinal = $baseUrl . '/img/placeholder-project.jpg';
    
    if (!empty($imatgePortada)) {
        // Netejar el nom del fitxer si conté ruta
        $nomNet = (strpos($imatgePortada, '/') !== false) ? basename($imatgePortada) : $imatgePortada;
        
        $rutaProjects = __DIR__ . '/img/Projects/' . $nomNet;
        $existeixProjects = file_exists($rutaProjects);
        
        if ($existeixProjects) {
            $rutaFinal = $baseUrl . '/img/Projects/' . $nomNet;
        }
    }
    
    echo "<tr>\n";
    echo "<td>" . $projecte['id'] . "</td>\n";
    echo "<td>" . htmlspecialchars($projecte['nom'] ?? 'Sense nom') . "</td>\n";
    echo "<td>" . htmlspecialchars($imatgePortada ?: '(cap)') . "</td>\n";
    echo "<td>" . ($existeixProjects ? '✅' : '❌') . "</td>\n";
    echo "<td>" . htmlspecialchars($rutaFinal) . "</td>\n";
    echo "</tr>\n";
}

echo "</table>\n";

echo "<h3>Test visual:</h3>\n";
foreach ($llistaProjectes as $projecte) {
    $imatgePortada = $projecte['imatge_portada'] ?? '';
    
    // Utilitzar rutes relatives per al test visual (ja que estarem a la mateixa pàgina)
    $rutaFinal = 'img/placeholder-project.jpg';
    if (!empty($imatgePortada)) {
        // Netejar el nom del fitxer
        $nomNet = (strpos($imatgePortada, '/') !== false) ? basename($imatgePortada) : $imatgePortada;
        
        $rutaProjects = __DIR__ . '/img/Projects/' . $nomNet;
        
        if (file_exists($rutaProjects)) {
            $rutaFinal = 'img/Projects/' . $nomNet;
        }
    }
    
    echo "<div style='display: inline-block; margin: 10px; text-align: center;'>\n";
    echo "<div>" . htmlspecialchars($projecte['nom'] ?? 'Projecte ' . $projecte['id']) . "</div>\n";
    echo "<img src='$rutaFinal' alt='Projecte' style='width: 150px; height: 100px; object-fit: cover; border: 1px solid #ccc;' onerror=\"this.src='img/placeholder-project.jpg'\">\n";
    echo "<div><small>" . htmlspecialchars($imatgePortada ?: 'placeholder') . "</small></div>\n";
    echo "</div>\n";
}
?>
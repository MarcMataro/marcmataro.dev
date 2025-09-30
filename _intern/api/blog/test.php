<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Test API Blog</h3>";

echo "<p><strong>Test 1: Verificar rutes</strong></p>";
$files = [
    '../../_classes/connexio.php' => file_exists('../../_classes/connexio.php'),
    '../../_classes/blog.php' => file_exists('../../_classes/blog.php'),
    '../../includes/auth.php' => file_exists('../../includes/auth.php')
];

foreach ($files as $file => $exists) {
    echo "- $file: " . ($exists ? '✅ Existeix' : '❌ No trobat') . "<br>";
}

echo "<p><strong>Test 2: Carregant dependències</strong></p>";
try {
    session_start();
    require_once '../../_classes/connexio.php';
    echo "✅ Connexio carregada<br>";
    
    require_once '../../_classes/blog.php';
    echo "✅ Blog carregat<br>";
    
    require_once '../../includes/auth.php';
    echo "✅ Auth carregat (no redirect)<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<p><strong>Test 3: Inicialitzar Blog</strong></p>";
try {
    $db = Connexio::getInstance()->getConnexio();
    echo "✅ Connexió DB<br>";
    
    $blog = new Blog($db);
    echo "✅ Blog instanciat<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<p><strong>Test 4: Provar mètodes de Blog</strong></p>";
try {
    $estadistiques = $blog->obtenirEstadistiques();
    echo "✅ Estadístiques: " . json_encode($estadistiques) . "<br>";
    
    $idiomes = $blog->obtenirIdiomesActius();
    echo "✅ Idiomes actius: " . json_encode($idiomes) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<p><strong>Test 5: Simular petició API</strong></p>";
try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/marcmataro.dev/_intern/api/blog/entrades';
    
    echo "- REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "<br>";
    echo "- REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
    
    $parsedUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    echo "- Parsed URL: " . $parsedUrl . "<br>";
    
    // Extreure el path després de "api/blog/"
    if (preg_match('#/api/blog/(.*)#', $parsedUrl, $matches)) {
        $path = $matches[1];
        echo "✅ Path extret: '$path'<br>";
    } else {
        echo "❌ No es pot extreure el path<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<p><strong>Test completat!</strong></p>";
?>
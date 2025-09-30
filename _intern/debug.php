<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Diagnòstic de Connexió</h3>";

// Test 1: Verificar fitxers
echo "<p><strong>Test 1: Verificació de fitxers</strong></p>";
$files = [
    '../_classes/connexio.php' => file_exists('../_classes/connexio.php'),
    '../_classes/blog.php' => file_exists('../_classes/blog.php')
];

foreach ($files as $file => $exists) {
    echo "- $file: " . ($exists ? '✅ Existeix' : '❌ No trobat') . "<br>";
}

// Test 2: Carregar auth
echo "<p><strong>Test 2: Carregant auth.php</strong></p>";
try {
    require_once 'includes/auth.php';
    echo "✅ Auth carregat correctament<br>";
} catch (Exception $e) {
    echo "❌ Error carregant auth: " . $e->getMessage() . "<br>";
}

// Test 3: Carregar classes
echo "<p><strong>Test 3: Carregant classes</strong></p>";
try {
    require_once '../_classes/connexio.php';
    echo "✅ Connexio carregada correctament<br>";
} catch (Exception $e) {
    echo "❌ Error carregant connexio: " . $e->getMessage() . "<br>";
    exit;
}

try {
    require_once '../_classes/blog.php';
    echo "✅ Blog carregat correctament<br>";
} catch (Exception $e) {
    echo "❌ Error carregant blog: " . $e->getMessage() . "<br>";
}

// Test 4: Connexió DB
echo "<p><strong>Test 4: Connexió a base de dades</strong></p>";
try {
    $db = Connexio::getInstance()->getConnexio();
    echo "✅ Connexió DB exitosa<br>";
    
    // Test query simple
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Query de test: " . $result['test'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error DB: " . $e->getMessage() . "<br>";
}

// Test 5: Instanciar Blog
echo "<p><strong>Test 5: Instanciant classe Blog</strong></p>";
try {
    $blog = new Blog($db);
    echo "✅ Blog instanciat correctament<br>";
} catch (Exception $e) {
    echo "❌ Error instanciant Blog: " . $e->getMessage() . "<br>";
}

echo "<p><strong>Diagnòstic completat!</strong></p>";
?>
<?php
// Test directe API - simular petició AJAX
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Test Directe API</h3>";

// Simular variables d'entorn
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/marcmataro.dev/_intern/api/blog/entrades';
$_GET = [];

try {
    // Capturar output de l'API
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    
    echo "<p><strong>Resposta de l'API:</strong></p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Verificar si és JSON vàlid
    $json = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p>✅ JSON vàlid</p>";
        echo "<p><strong>Dades decodificades:</strong></p>";
        echo "<pre>" . print_r($json, true) . "</pre>";
    } else {
        echo "<p>❌ JSON no vàlid: " . json_last_error_msg() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
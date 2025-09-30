<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Test d'Autenticació</h3>";

// Verificar sessió
session_start();
echo "<p><strong>Informació de sessió:</strong></p>";
echo "- Session ID: " . session_id() . "<br>";
echo "- Autenticat: " . (isset($_SESSION['autenticat']) ? ($_SESSION['autenticat'] ? 'Sí' : 'No') : 'No definit') . "<br>";

if (isset($_SESSION['usuari'])) {
    echo "- Usuari: " . $_SESSION['usuari'] . "<br>";
}

if (isset($_SESSION['temps_login'])) {
    echo "- Temps login: " . date('Y-m-d H:i:s', $_SESSION['temps_login']) . "<br>";
    echo "- Temps actual: " . date('Y-m-d H:i:s') . "<br>";
    $diferencia = time() - $_SESSION['temps_login'];
    echo "- Temps transcorregut: " . floor($diferencia / 60) . " minuts<br>";
}

// Test carrega auth.php
echo "<p><strong>Test auth.php:</strong></p>";
try {
    require_once 'includes/auth.php';
    echo "✅ Auth.php carregat sense redireccions<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<p><strong>Status després d'auth:</strong></p>";
echo "- Encara aquí: ✅ (no hi ha hagut redirect)<br>";
echo "- Headers enviats: " . (headers_sent() ? 'Sí' : 'No') . "<br>";

?>

<p><strong>Test completat!</strong></p>
<p><a href="index.php">Tornar al login</a> | <a href="blog.php">Provar blog.php</a></p>
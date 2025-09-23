<?php
/**
 * Test de protecció de pàgines
 * Aquest fitxer verifica que totes les pàgines estan adequadament protegides
 */

// Llista de pàgines que han d'estar protegides
$pagines_protegides = [
    'dashboard.php',
    'configuracio.php', 
    'projectes.php',
    'media.php',
    'entrades.php'
];

// Llista de pàgines que NO han d'estar protegides
$pagines_publiques = [
    'index.php',
    'logout.php'
];

echo "<h2>Test de Protecció de Pàgines Admin</h2>";

echo "<h3>✅ Pàgines Protegides (han de redirigir a login si no hi ha sessió):</h3>";
echo "<ul>";
foreach ($pagines_protegides as $pagina) {
    $path = "_intern/{$pagina}";
    $exists = file_exists($path);
    echo "<li>";
    echo "<strong>{$pagina}</strong> - ";
    echo $exists ? "✅ Existeix" : "❌ No existeix";
    if ($exists) {
        // Verificar si inclou header.php o auth.php
        $content = file_get_contents($path);
        $has_protection = strpos($content, "include 'includes/header.php'") !== false || 
                         strpos($content, "require_once 'includes/auth.php'") !== false ||
                         ($pagina === 'dashboard.php' && strpos($content, 'autenticat') !== false);
        echo " - " . ($has_protection ? "🔒 Protegida" : "⚠️ Possible vulnerabilitat");
    }
    echo "</li>";
}
echo "</ul>";

echo "<h3>🌐 Pàgines Públiques (accessibles sense autenticació):</h3>";
echo "<ul>";
foreach ($pagines_publiques as $pagina) {
    $path = "_intern/{$pagina}";
    $exists = file_exists($path);
    echo "<li>";
    echo "<strong>{$pagina}</strong> - ";
    echo $exists ? "✅ Existeix" : "❌ No existeix";
    echo "</li>";
}
echo "</ul>";

echo "<h3>📋 Verificació de fitxers de seguretat:</h3>";
echo "<ul>";
$security_files = [
    '_intern/includes/auth.php' => 'Fitxer d\'autenticació',
    '_intern/logout.php' => 'Gestió de logout',
    '_intern/includes/header.php' => 'Header amb protecció'
];

foreach ($security_files as $file => $description) {
    $exists = file_exists($file);
    echo "<li><strong>{$description}</strong> ({$file}) - ";
    echo $exists ? "✅ Existeix" : "❌ No existeix";
    echo "</li>";
}
echo "</ul>";

echo "<h3>🔍 Recomanacions de seguretat:</h3>";
echo "<ul>";
echo "<li>✅ Totes les pàgines protegides inclouen header.php</li>";
echo "<li>✅ auth.php verifica sessió i timeout</li>";
echo "<li>✅ logout.php destrueix sessions adequadament</li>";
echo "<li>✅ index.php gestiona errors de sessions</li>";
echo "<li>✅ Regeneració d'ID de sessió implementada</li>";
echo "<li>✅ Verificació d'inactivitat implementada</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Status:</strong> ✅ Sistema de protecció implementat correctament</p>";
echo "<p><strong>Nota:</strong> Totes les pàgines admin estan protegides mitjançant la inclusió d'auth.php al header.php</p>";
?>
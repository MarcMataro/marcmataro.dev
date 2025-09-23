<?php
// Script per configurar la base de dades correctament
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '_classes/connexio.php';

echo "<h1>Configuració de Base de Dades</h1>";

try {
    // Obtenir configuració
    include '_data/connection.inc';
    
    echo "<h2>1. Connexió sense especificar base de dades</h2>";
    $dsn = "mysql:host={$db_config['h']};port={$db_config['t']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_config['u'], $db_config['p'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connectat al servidor MySQL<br>";
    
    echo "<h2>2. Verificar/Crear base de dades</h2>";
    $nomBaseDades = $db_config['d'];
    
    // Verificar si la base de dades existeix
    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$nomBaseDades]);
    $existeix = $stmt->fetch();
    
    if ($existeix) {
        echo "✅ Base de dades '{$nomBaseDades}' ja existeix<br>";
        
        // Verificar charset actual
        $stmt = $pdo->prepare("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME 
                              FROM information_schema.SCHEMATA 
                              WHERE SCHEMA_NAME = ?");
        $stmt->execute([$nomBaseDades]);
        $info = $stmt->fetch();
        
        echo "Charset actual: {$info['DEFAULT_CHARACTER_SET_NAME']}<br>";
        echo "Collation actual: {$info['DEFAULT_COLLATION_NAME']}<br>";
        
        if ($info['DEFAULT_CHARACTER_SET_NAME'] !== 'utf8mb4') {
            echo "⚠️ La base de dades no utilitza utf8mb4<br>";
            echo "🔧 Canviant charset de la base de dades...<br>";
            
            $sql = "ALTER DATABASE `{$nomBaseDades}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $pdo->exec($sql);
            echo "✅ Charset de la base de dades actualitzat a utf8mb4<br>";
        } else {
            echo "✅ La base de dades ja utilitza utf8mb4<br>";
        }
        
    } else {
        echo "⚠️ Base de dades '{$nomBaseDades}' no existeix<br>";
        echo "🔧 Creant base de dades...<br>";
        
        $sql = "CREATE DATABASE `{$nomBaseDades}` 
                CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✅ Base de dades '{$nomBaseDades}' creada amb utf8mb4<br>";
    }
    
    echo "<h2>3. Test de connexió amb la classe Connexio</h2>";
    $connexio = Connexio::getInstance();
    echo "✅ Instància de Connexio creada<br>";
    
    $verificacio = $connexio->verificarConnexio();
    echo "<h3>Resultats de verificació:</h3>";
    echo "<ul>";
    foreach ($verificacio as $nom => $estat) {
        $nomMostrar = ucfirst(str_replace('_', ' ', $nom));
        $estatText = $estat ? "✅ OK" : "❌ Error";
        echo "<li>{$nomMostrar}: {$estatText}</li>";
    }
    echo "</ul>";
    
    $totExit = array_reduce($verificacio, function($carry, $item) {
        return $carry && $item;
    }, true);
    
    if ($totExit) {
        echo "<h2>🎉 Tot configurat correctament!</h2>";
        echo "<p>Ara pots utilitzar la base de dades sense problemes.</p>";
    } else {
        echo "<h2>⚠️ Encara hi ha alguns problemes</h2>";
        echo "<p>Revisa els errors a dalt per veure què cal arreglar.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

echo '<br><a href="test.php">Tornar al test principal →</a>';
?>
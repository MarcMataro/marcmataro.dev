<?php
/**
 * Script de test per verificar les operacions CRUD de projectes
 */

require_once '_classes/connexio.php';
require_once '_classes/projectes.php';

echo "<h2>Test CRUD Projectes</h2>\n";

try {
    // Connexió
    $db = Connexio::getInstance();
    $projectes = new Projectes($db->getConnexio());
    
    echo "<h3>1. Test Crear Projecte</h3>\n";
    
    // Dades de test
    $dadesTest = [
        'nom_ca' => 'Test Project ' . date('Y-m-d H:i:s'),
        'nom_es' => 'Proyecto Test ' . date('Y-m-d H:i:s'),
        'nom_en' => 'Test Project ' . date('Y-m-d H:i:s'),
        'slug_ca' => 'test-project-' . time(),
        'slug_es' => 'proyecto-test-' . time(),
        'slug_en' => 'test-project-en-' . time(),
        'descripcio_curta_ca' => 'Descripció curta en català',
        'descripcio_curta_es' => 'Descripción corta en español',
        'descripcio_curta_en' => 'Short description in English',
        'descripcio_detallada_ca' => 'Descripció detallada en català amb més contingut',
        'descripcio_detallada_es' => 'Descripción detallada en español con más contenido',
        'descripcio_detallada_en' => 'Detailed description in English with more content',
        'estat' => 'desenvolupament',
        'visible' => 1,
        'url_demo' => 'https://example.com/test',
        'url_github' => 'https://github.com/test/project',
        'url_documentacio' => 'https://docs.example.com/test',
        'imatge_portada' => 'test-image.jpg',
        'tecnologies_principals' => '["PHP", "MySQL", "JavaScript"]'
    ];
    
    $resultat = $projectes->crear($dadesTest);
    
    if ($resultat['success']) {
        echo "✅ Projecte creat correctament amb ID: " . $resultat['id'] . "\n";
        $projecteId = $resultat['id'];
        
        echo "<h3>2. Test Obtenir Projecte</h3>\n";
        
        $projecteObtingut = $projectes->obtenirPerId($projecteId);
        if ($projecteObtingut) {
            echo "✅ Projecte obtingut correctament:\n";
            echo "- Nom CA: " . $projecteObtingut['nom_ca'] . "\n";
            echo "- Slug CA: " . $projecteObtingut['slug_ca'] . "\n";
            echo "- Estat: " . $projecteObtingut['estat'] . "\n";
        } else {
            echo "❌ Error obtenint el projecte\n";
        }
        
        echo "<h3>3. Test Actualitzar Projecte</h3>\n";
        
        $dadesActualitzacio = [
            'nom_ca' => 'Test Project ACTUALITZAT ' . date('H:i:s'),
            'estat' => 'actiu',
            'url_demo' => 'https://example.com/updated',
            'visible' => 1,
            'descripcio_curta_ca' => 'Descripció actualitzada en el test'
        ];
        
        $resultatActualitzacio = $projectes->actualitzar($projecteId, $dadesActualitzacio);
        
        if ($resultatActualitzacio['success']) {
            echo "✅ Projecte actualitzat correctament\n";
            
            // Verificar l'actualització
            $projecteActualitzat = $projectes->obtenirPerId($projecteId);
            echo "- Nom actualitzat: " . $projecteActualitzat['nom_ca'] . "\n";
            echo "- Estat actualitzat: " . $projecteActualitzat['estat'] . "\n";
        } else {
            echo "❌ Error actualitzant: " . implode(', ', $resultatActualitzacio['errors']) . "\n";
        }
        
        echo "<h3>4. Test Obtenir Tots els Projectes</h3>\n";
        
        $totsProjectes = $projectes->obtenirTots();
        echo "✅ Total projectes: " . count($totsProjectes) . "\n";
        
        echo "<h3>5. Test Eliminació (Opcional)</h3>\n";
        
        // Opcional: eliminar el projecte de test
        if (method_exists($projectes, 'eliminar')) {
            $resultatEliminacio = $projectes->eliminar($projecteId);
            if ($resultatEliminacio) {
                echo "✅ Projecte eliminat correctament\n";
            } else {
                echo "❌ Error eliminant el projecte\n";
            }
        } else {
            echo "ℹ️ Mètode eliminar no implementat (projecte de test mantingut)\n";
        }
        
    } else {
        echo "❌ Error creant projecte: " . implode(', ', $resultat['errors']) . "\n";
    }
    
    echo "<h3>6. Test Estadístiques</h3>\n";
    
    if (method_exists($projectes, 'obtenirEstadistiques')) {
        $estadistiques = $projectes->obtenirEstadistiques();
        echo "✅ Estadístiques obtingudes:\n";
        echo "- Total: " . $estadistiques['total'] . "\n";
        echo "- Desenvolupament: " . ($estadistiques['desenvolupament'] ?? 0) . "\n";
        echo "- Actius: " . ($estadistiques['actiu'] ?? 0) . "\n";
    } else {
        echo "ℹ️ Mètode obtenirEstadistiques no disponible\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n<h3>Test Completat</h3>\n";
echo '<a href="_intern/formulari-projecte.php">Anar al formulari de projectes</a> | ';
echo '<a href="_intern/projectes.php">Veure llista de projectes</a>';
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; }
a { color: #007cba; text-decoration: none; margin: 5px; }
a:hover { text-decoration: underline; }
</style>
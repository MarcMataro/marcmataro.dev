<?php
// Debug específic per WebEmmaPortadaProjecte.png

require_once '_classes/connexio.php';
require_once '_classes/projectes.php';

echo "<h2>Debug imatge WebEmmaPortadaProjecte.png</h2>\n";

try {
    $connexio = Connexio::getInstance();
    $db = $connexio->getConnexio();
    $projectes = new Projectes($db);
    $llistaProjectes = $projectes->obtenirTots();
    
    echo "<h3>Tots els projectes amb imatges:</h3>\n";
    
    foreach ($llistaProjectes as $projecte) {
        $imatgePortada = $projecte['imatge_portada'] ?? '';
        
        if (!empty($imatgePortada) && strpos($imatgePortada, 'WebEmma') !== false) {
            echo "<div style='border: 2px solid red; padding: 10px; margin: 10px 0;'>\n";
            echo "<h4>PROJECTE TROBAT AMB WebEmma:</h4>\n";
            echo "<strong>ID:</strong> " . $projecte['id'] . "<br>\n";
            echo "<strong>Nom:</strong> " . htmlspecialchars($projecte['nom'] ?? 'Sense nom') . "<br>\n";
            echo "<strong>Camp imatge_portada (raw):</strong> '" . htmlspecialchars($imatgePortada) . "'<br>\n";
            echo "<strong>Longitud:</strong> " . strlen($imatgePortada) . " caràcters<br>\n";
            
            // Netejar nom fitxer
            $nomNet = (strpos($imatgePortada, '/') !== false) ? basename($imatgePortada) : $imatgePortada;
            echo "<strong>Nom netejat:</strong> '" . htmlspecialchars($nomNet) . "'<br>\n";
            
            // Comprovar existència
            $carpetes = [
                'projectes' => __DIR__ . '/img/projectes/' . $nomNet,
                'Projectes' => __DIR__ . '/img/Projectes/' . $nomNet,
                'Projects' => __DIR__ . '/img/Projects/' . $nomNet
            ];
            
            echo "<strong>Verificacions:</strong><br>\n";
            foreach ($carpetes as $nom => $ruta) {
                $existeix = file_exists($ruta);
                echo "- $nom/: $ruta -> " . ($existeix ? '✅ EXISTEIX' : '❌ NO EXISTEIX') . "<br>\n";
            }
            
            // Test de la lògica actual
            echo "<br><strong>Lògica actual del codi:</strong><br>\n";
            $imatgeProjecte = '/img/placeholder-project.jpg';
            $carpetesPossibles = [
                ['ruta' => '/img/projectes/', 'fisica' => __DIR__ . '/img/projectes/'],
                ['ruta' => '/img/Projectes/', 'fisica' => __DIR__ . '/img/Projectes/'],
                ['ruta' => '/img/Projects/', 'fisica' => __DIR__ . '/img/Projects/']
            ];
            
            $trobada = false;
            foreach ($carpetesPossibles as $carpeta) {
                $rutaCompleta = $carpeta['ruta'] . $nomNet;
                $rutaFisica = $carpeta['fisica'] . $nomNet;
                echo "Provant: $rutaCompleta (física: $rutaFisica) -> ";
                
                if (file_exists($rutaFisica)) {
                    $imatgeProjecte = $rutaCompleta;
                    $trobada = true;
                    echo "✅ TROBADA!<br>\n";
                    break;
                } else {
                    echo "❌ No existeix<br>\n";
                }
            }
            
            echo "<strong>Resultat final:</strong> $imatgeProjecte<br>\n";
            echo "<strong>Imatge trobada:</strong> " . ($trobada ? 'SÍ' : 'NO') . "<br>\n";
            
            echo "<br><strong>Test visual:</strong><br>\n";
            echo "<img src='$imatgeProjecte' alt='Test' style='width: 200px; height: 150px; border: 1px solid #ccc;' onerror=\"this.style.border='2px solid red'; this.alt='ERROR: No es pot carregar'\">\n";
            
            echo "</div>\n";
        }
    }
    
    echo "<h3>Informació del servidor:</h3>\n";
    echo "<strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>\n";
    echo "<strong>Script actual:</strong> " . __FILE__ . "<br>\n";
    echo "<strong>Directori actual:</strong> " . __DIR__ . "<br>\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
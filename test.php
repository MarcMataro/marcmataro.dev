<?php
/**
 * Test de Connexió a la Base de Dades
 * 
 * Aquest fitxer prova totes les funcionalitats de la classe Connexio
 * i verifica que la base de dades està configurada correctament.
 */

// Configuració de visualització d'errors per al test
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Variables de control d'errors
$errors = [];
$classesCarregades = false;

// Intentar carregar les classes de forma segura
try {
    if (!file_exists('_classes/connexio.php')) {
        throw new Exception("Fitxer _classes/connexio.php no trobat");
    }
    
    if (!file_exists('_classes/projectes.php')) {
        throw new Exception("Fitxer _classes/projectes.php no trobat");
    }
    
    require_once '_classes/connexio.php';
    require_once '_classes/projectes.php';
    
    $classesCarregades = true;
    
} catch (Exception $e) {
    $errors[] = "Error en carregar classes: " . $e->getMessage();
} catch (Error $e) {
    $errors[] = "Error fatal en carregar classes: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Connexió - Marc Mataró</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 30px;
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border-left: 4px solid #3498db;
            background-color: #f8f9fa;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        .info {
            color: #3498db;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .status-ok {
            background-color: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .json-display {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .test-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .test-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .test-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test de Connexió a la Base de Dades</h1>
        <p><strong>Data del test:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        
        <!-- Mostrar errors crítics si n'hi ha -->
        <?php if (!empty($errors)): ?>
            <div class="test-result test-error">
                <h2>❌ Errors Crítics Detectats</h2>
                <?php foreach ($errors as $error): ?>
                    <p><strong>Error:</strong> <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <p><strong>Solució:</strong> Verifica que els fitxers de classes existeixen i no tenen errors de sintaxi.</p>
            </div>
        <?php endif; ?>
        
        <?php
        $totalTests = 0;
        $testsOk = 0;
        
        // Funció per mostrar resultats de test
        function mostrarResultat($nom, $resultat, $detalls = '') {
            global $totalTests, $testsOk;
            $totalTests++;
            
            if ($resultat) {
                $testsOk++;
                echo "<div class='test-result test-success'>";
                echo "<strong>✅ {$nom}</strong> - Correcte";
            } else {
                echo "<div class='test-result test-error'>";
                echo "<strong>❌ {$nom}</strong> - Error";
            }
            
            if ($detalls) {
                echo "<br><small>{$detalls}</small>";
            }
            echo "</div>";
        }
        ?>

        <!-- Test 1: Carregar Classes -->
        <div class="test-section">
            <h2>📦 Test 1: Càrrega de Classes</h2>
            <?php
            $detallsClasses = '';
            
            if (!$classesCarregades) {
                $detallsClasses = 'Error en carregar classes. Revisa els errors a dalt.';
            } else {
                if (!class_exists('Connexio')) {
                    $classesCarregades = false;
                    $detallsClasses .= 'Classe Connexio no trobada després de carregar. ';
                }
                
                if (!class_exists('Projectes')) {
                    $classesCarregades = false;
                    $detallsClasses .= 'Classe Projectes no trobada després de carregar. ';
                }
                
                if ($classesCarregades) {
                    $detallsClasses = 'Classes Connexio i Projectes carregades correctament';
                }
            }
            
            mostrarResultat('Càrrega de classes', $classesCarregades, $detallsClasses);
            ?>
        </div>

        <!-- Test 2: Configuració -->
        <div class="test-section">
            <h2>⚙️ Test 2: Configuració de Base de Dades</h2>
            <?php
            $configOk = false;
            $detallsConfig = '';
            
            try {
                $rutaConfig = '_data/connection.inc';
                if (file_exists($rutaConfig)) {
                    include $rutaConfig;
                    if (isset($db_config) && is_array($db_config)) {
                        $configOk = true;
                        $detallsConfig = "Configuració carregada correctament des de {$rutaConfig}";
                        
                        echo "<table>";
                        echo "<tr><th>Paràmetre</th><th>Valor</th></tr>";
                        echo "<tr><td>Host</td><td>" . htmlspecialchars($db_config['h']) . "</td></tr>";
                        echo "<tr><td>Usuari</td><td>" . htmlspecialchars($db_config['u']) . "</td></tr>";
                        echo "<tr><td>Base de Dades</td><td>" . htmlspecialchars($db_config['d']) . "</td></tr>";
                        echo "<tr><td>Port</td><td>" . htmlspecialchars($db_config['t']) . "</td></tr>";
                        echo "<tr><td>Contrasenya</td><td>" . (empty($db_config['p']) ? 'Buida' : 'Configurada') . "</td></tr>";
                        echo "</table>";
                    } else {
                        $detallsConfig = 'Fitxer de configuració no vàlid';
                    }
                } else {
                    $detallsConfig = "Fitxer de configuració no trobat: {$rutaConfig}";
                }
            } catch (Exception $e) {
                $detallsConfig = 'Error: ' . $e->getMessage();
            }
            
            mostrarResultat('Configuració de BD', $configOk, $detallsConfig);
            ?>
        </div>

        <!-- Test 3: Instància de Connexió -->
        <div class="test-section">
            <h2>🔌 Test 3: Instància de Connexió</h2>
            <?php
            $instanciaOk = false;
            $detallsInstancia = '';
            $connexio = null;
            
            if (!$classesCarregades) {
                $detallsInstancia = 'No es pot provar sense classes carregades';
            } else {
                try {
                    $connexio = Connexio::getInstance();
                    if ($connexio instanceof Connexio) {
                        $instanciaOk = true;
                        $detallsInstancia = 'Instància de Connexio creada correctament';
                    } else {
                        $detallsInstancia = 'getInstance() no retorna una instància vàlida';
                    }
                } catch (Exception $e) {
                    $detallsInstancia = 'Error: ' . $e->getMessage();
                } catch (Error $e) {
                    $detallsInstancia = 'Error fatal: ' . $e->getMessage();
                }
            }
            
            mostrarResultat('Crear instància', $instanciaOk, $detallsInstancia);
            ?>
        </div>

        <!-- Test 4: Connexió PDO -->
        <div class="test-section">
            <h2>🗃️ Test 4: Connexió PDO</h2>
            <?php
            $pdoOk = false;
            $detallsPdo = '';
            $pdo = null;
            
            if ($connexio) {
                try {
                    $pdo = $connexio->getConnexio();
                    if ($pdo instanceof PDO) {
                        $pdoOk = true;
                        $detallsPdo = 'Objecte PDO obtingut correctament';
                    } else {
                        $detallsPdo = 'getConnexio() no retorna un objecte PDO vàlid';
                    }
                } catch (Exception $e) {
                    $detallsPdo = 'Error: ' . $e->getMessage();
                }
            } else {
                $detallsPdo = 'No es pot provar PDO sense instància de Connexio';
            }
            
            mostrarResultat('Objecte PDO', $pdoOk, $detallsPdo);
            ?>
        </div>

        <!-- Test 5: Verificació de Connexió -->
        <div class="test-section">
            <h2>✅ Test 5: Verificació de Connexió</h2>
            <?php
            if ($connexio) {
                try {
                    $verificacio = $connexio->verificarConnexio();
                    
                    echo "<table>";
                    echo "<tr><th>Verificació</th><th>Estat</th></tr>";
                    
                    foreach ($verificacio as $nom => $estat) {
                        $nomMostrar = ucfirst(str_replace('_', ' ', $nom));
                        $estatMostrar = $estat ? 
                            "<span class='status-ok'>✅ OK</span>" : 
                            "<span class='status-error'>❌ Error</span>";
                        echo "<tr><td>{$nomMostrar}</td><td>{$estatMostrar}</td></tr>";
                    }
                    echo "</table>";
                    
                    $verificacioOk = array_reduce($verificacio, function($carry, $item) {
                        return $carry && $item;
                    }, true);
                    
                    mostrarResultat('Verificació completa', $verificacioOk, 
                        $verificacioOk ? 'Totes les verificacions han passat' : 'Algunes verificacions han fallat');
                    
                } catch (Exception $e) {
                    mostrarResultat('Verificació completa', false, 'Error: ' . $e->getMessage());
                }
            } else {
                mostrarResultat('Verificació completa', false, 'No es pot verificar sense connexió');
            }
            ?>
        </div>

        <!-- Test 6: Informació de Connexió -->
        <div class="test-section">
            <h2>ℹ️ Test 6: Informació de Connexió</h2>
            <?php
            if ($connexio) {
                try {
                    $info = $connexio->getInfoConnexio();
                    
                    echo "<table>";
                    echo "<tr><th>Propietat</th><th>Valor</th></tr>";
                    foreach ($info as $clau => $valor) {
                        $clauMostrar = ucfirst(str_replace('_', ' ', $clau));
                        echo "<tr><td>{$clauMostrar}</td><td>" . htmlspecialchars($valor) . "</td></tr>";
                    }
                    echo "</table>";
                    
                    mostrarResultat('Informació de connexió', true, 'Informació obtinguda correctament');
                    
                } catch (Exception $e) {
                    mostrarResultat('Informació de connexió', false, 'Error: ' . $e->getMessage());
                }
            } else {
                mostrarResultat('Informació de connexió', false, 'No es pot obtenir informació sense connexió');
            }
            ?>
        </div>

        <!-- Test 7: Operacions Bàsiques -->
        <div class="test-section">
            <h2>🔧 Test 7: Operacions Bàsiques de BD</h2>
            <?php
            if ($connexio && $connexio->estaConnectat()) {
                try {
                    // Test de consulta simple
                    $resultat = $connexio->fetchColumn("SELECT 1 as test");
                    $testSimple = ($resultat == 1);
                    mostrarResultat('Consulta simple', $testSimple, 
                        $testSimple ? 'SELECT 1 executat correctament' : 'Error en consulta simple');
                    
                    // Test de consulta amb paràmetres
                    $resultat = $connexio->fetchColumn("SELECT ? as test", [42]);
                    $testParametres = ($resultat == 42);
                    mostrarResultat('Consulta amb paràmetres', $testParametres,
                        $testParametres ? 'Consulta preparada funcionant' : 'Error en consulta preparada');
                    
                    // Test de consulta d'informació del sistema
                    $versio = $connexio->fetchColumn("SELECT VERSION()");
                    $testVersio = !empty($versio);
                    mostrarResultat('Informació del sistema', $testVersio,
                        $testVersio ? "Versió MySQL: {$versio}" : 'No es pot obtenir la versió');
                    
                } catch (Exception $e) {
                    mostrarResultat('Operacions bàsiques', false, 'Error: ' . $e->getMessage());
                }
            } else {
                mostrarResultat('Operacions bàsiques', false, 'No es poden provar operacions sense connexió activa');
            }
            ?>
        </div>

        <!-- Test 8: Classe Projectes -->
        <div class="test-section">
            <h2>📂 Test 8: Classe Projectes</h2>
            <?php
            if ($connexio && $pdo) {
                try {
                    $projectes = new Projectes($pdo);
                    $projectesOk = ($projectes instanceof Projectes);
                    
                    if ($projectesOk) {
                        // Verificar si la taula projectes existeix
                        $taulaExisteix = false;
                        try {
                            $connexio->query("SELECT 1 FROM projectes LIMIT 1");
                            $taulaExisteix = true;
                        } catch (Exception $e) {
                            // La taula no existeix
                        }
                        
                        if ($taulaExisteix) {
                            try {
                                $stats = $projectes->obtenirEstadistiques();
                                $statsOk = is_array($stats);
                                mostrarResultat('Estadístiques de projectes', $statsOk,
                                    $statsOk ? 'Total projectes: ' . ($stats['total'] ?? 0) : 'Error en obtenir estadístiques');
                            } catch (Exception $e) {
                                mostrarResultat('Estadístiques de projectes', false, 'Error: ' . $e->getMessage());
                            }
                        } else {
                            mostrarResultat('Taula projectes', false, 'La taula projectes no existeix. Executa l\'SQL de creació primer.');
                        }
                    }
                    
                    mostrarResultat('Instància de Projectes', $projectesOk,
                        $projectesOk ? 'Classe Projectes instanciada correctament' : 'Error en crear instància de Projectes');
                    
                } catch (Exception $e) {
                    mostrarResultat('Instància de Projectes', false, 'Error: ' . $e->getMessage());
                }
            } else {
                mostrarResultat('Instància de Projectes', false, 'No es pot provar Projectes sense connexió PDO');
            }
            ?>
        </div>

        <!-- Resum Final -->
        <div class="test-section">
            <h2>📊 Resum Final</h2>
            <?php
            $percentatgeExit = $totalTests > 0 ? round(($testsOk / $totalTests) * 100, 2) : 0;
            $colorResum = $percentatgeExit >= 80 ? 'success' : ($percentatgeExit >= 60 ? 'warning' : 'error');
            ?>
            
            <div class="test-result test-<?php echo $percentatgeExit >= 80 ? 'success' : 'error'; ?>">
                <h3>Resultats del Test</h3>
                <p><strong>Tests executats:</strong> <?php echo $totalTests; ?></p>
                <p><strong>Tests correctes:</strong> <?php echo $testsOk; ?></p>
                <p><strong>Tests fallits:</strong> <?php echo $totalTests - $testsOk; ?></p>
                <p><strong>Percentatge d'èxit:</strong> <?php echo $percentatgeExit; ?>%</p>
                
                <?php if ($percentatgeExit >= 80): ?>
                    <p><strong>🎉 Excel·lent!</strong> La connexió està funcionant correctament.</p>
                <?php elseif ($percentatgeExit >= 60): ?>
                    <p><strong>⚠️ Atenció!</strong> Hi ha alguns problemes que haurien de ser revisats.</p>
                <?php else: ?>
                    <p><strong>🚨 Error crític!</strong> Hi ha problemes greus amb la connexió.</p>
                <?php endif; ?>
            </div>
            
            <h3>Recomanacions:</h3>
            <ul>
                <?php if ($percentatgeExit < 100): ?>
                    <li>Revisa els errors mostrats a dalt per identificar els problemes</li>
                    <li>Verifica que la base de dades estigui en funcionament</li>
                    <li>Comprova les credencials al fitxer _data/connection.inc</li>
                    <?php if (!isset($taulaExisteix) || !$taulaExisteix): ?>
                        <li><strong>Important:</strong> Executa l'SQL de creació de la taula 'projectes'</li>
                    <?php endif; ?>
                <?php else: ?>
                    <li>✅ Tot està funcionant correctament!</li>
                    <li>✅ Pots començar a utilitzar les classes en la teva aplicació</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="test-section">
            <h2>🔗 Enllaços Útils</h2>
            <ul>
                <li><a href="index.php">Tornar a la pàgina principal</a></li>
                <li><a href="_intern/">Accedir al panell d'administració</a></li>
            </ul>
        </div>
    </div>

    <script>
        // Actualització automàtica cada 30 segons (opcional)
        // setTimeout(() => location.reload(), 30000);
        
        console.log('Test de connexió executat:', {
            timestamp: new Date().toISOString(),
            totalTests: <?php echo $totalTests; ?>,
            testsOk: <?php echo $testsOk; ?>,
            successRate: <?php echo $percentatgeExit; ?>
        });
    </script>
</body>
</html>

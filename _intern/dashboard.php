<?php
// Iniciar sessió
session_start();

// Carregar classes necessàries per a l'autenticació
require_once '../_classes/connexio.php';
require_once '../_classes/usuaris.php';
require_once '../_classes/projectes.php';

// Variables per a missatges
$error = '';
$usuariAutenticat = null;

// Processar autenticació si es rebent dades POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomUsuari = $_POST['NomUsuari'] ?? '';
    $contrasenya = $_POST['Contrassenya'] ?? '';
    
    if (!empty($nomUsuari) && !empty($contrasenya)) {
        try {
            // Establir connexió
            $connexio = Connexio::getInstance()->getConnexio();
            $gestorUsuaris = new Usuaris($connexio);
            
            // Intentar autenticar l'usuari
            $usuariAutenticat = $gestorUsuaris->autenticar($nomUsuari, $contrasenya);
            
            if ($usuariAutenticat) {
                // Autenticació exitosa - guardar dades a la sessió
                $_SESSION['usuari_id'] = $usuariAutenticat['id'];
                $_SESSION['usuari_nom'] = $usuariAutenticat['nom'];
                $_SESSION['usuari_cognoms'] = $usuariAutenticat['cognoms'];
                $_SESSION['usuari_email'] = $usuariAutenticat['email'];
                $_SESSION['usuari_rol'] = $usuariAutenticat['rol'];
                $_SESSION['usuari_avatar'] = $usuariAutenticat['avatar'];
                $_SESSION['autenticat'] = true;
                $_SESSION['temps_login'] = time();
                
            } else {
                $error = 'Nom d\'usuari o contrasenya incorrectes';
            }
            
        } catch (Exception $e) {
            $error = 'Error de connexió: ' . $e->getMessage();
        }
    } else {
        $error = 'Si us plau, introdueix nom d\'usuari i contrasenya';
    }
}

// Verificar si l'usuari està autenticat (per accessos directes)
if (!isset($_SESSION['autenticat']) || !$_SESSION['autenticat']) {
    // Si hi ha error d'autenticació, mostrar-lo i redirigir
    if (!empty($error)) {
        // Guardar error a la sessió per mostrar-lo al formulari de login
        $_SESSION['login_error'] = $error;
    }
    
    // Redirigir al formulari de login si no està autenticat
    if (!$usuariAutenticat) {
        header('Location: index.php');
        exit;
    }
}

// Obtenir estadístiques del dashboard
try {
    $connexio = Connexio::getInstance()->getConnexio();
    $gestorProjectes = new Projectes($connexio);
    $estadistiquesProjectes = $gestorProjectes->obtenirEstadistiques();
    
    // Obtenir estadístiques generals de la base de dades
    $stmt = $connexio->prepare("SELECT 
        (SELECT COUNT(*) FROM projectes WHERE visible = 1) as projectes_visibles,
        (SELECT COUNT(*) FROM usuaris) as total_usuaris,
        (SELECT COUNT(DISTINCT DATE(data_creacio)) FROM projectes WHERE data_creacio >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as dies_actius
    ");
    $stmt->execute();
    $estadistiquesGenerals = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error obtenint estadístiques: " . $e->getMessage());
    $estadistiquesProjectes = ['total' => 0, 'visibles' => 0, 'actius' => 0, 'desenvolupament' => 0];
    $estadistiquesGenerals = ['projectes_visibles' => 0, 'total_usuaris' => 0, 'dies_actius' => 0];
}

$current_page = 'dashboard';
include 'includes/page-header.php';
$page_title = getPageTitle($current_page);
include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <!-- Page Content -->
        <section class="content-section active">
            <?php renderPageHeader($current_page); ?>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3>24</h3>
                        <p>Entrades de Blog</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            +12%
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $estadistiquesProjectes['total'] ?? 0; ?></h3>
                        <p>Projectes</p>
                        <div class="stat-details">
                            <small>
                                <?php echo $estadistiquesProjectes['actius'] ?? 0; ?> actius • 
                                <?php echo $estadistiquesProjectes['desenvolupament'] ?? 0; ?> en desenvolupament
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3>1.2K</h3>
                        <p>Visites del Mes</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            +8%
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $estadistiquesGenerals['total_usuaris'] ?? 0; ?></h3>
                        <p>Usuaris Registrats</p>
                        <div class="stat-details">
                            <small>
                                <?php echo $estadistiquesGenerals['projectes_visibles'] ?? 0; ?> projectes públics
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Accions Ràpides</h3>
                <div class="quick-actions-grid">
                    <a href="entrades.php" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        Nova Entrada
                    </a>
                    <a href="projectes.php" class="quick-action-btn">
                        <i class="fas fa-briefcase"></i>
                        Nou Projecte
                    </a>
                    <a href="media.php" class="quick-action-btn">
                        <i class="fas fa-upload"></i>
                        Pujar Media
                    </a>
                    <a href="configuracio.php" class="quick-action-btn">
                        <i class="fas fa-cog"></i>
                        Configuració
                    </a>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3>Projectes Recents</h3>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="recent-posts">
                            <?php
                            try {
                                // Obtenir projectes recents
                                $projectesRecents = $gestorProjectes->obtenirAmbTraducio('ca', [
                                    'limit' => 3,
                                    'ordenar' => 'data_creacio',
                                    'direccio' => 'DESC'
                                ]);
                                
                                if (!empty($projectesRecents)) {
                                    foreach ($projectesRecents as $projecte) {
                                        $estatClass = match($projecte['estat']) {
                                            'actiu' => 'badge-success',
                                            'desenvolupament' => 'badge-warning',
                                            'aturat' => 'badge-secondary',
                                            'archivat' => 'badge-danger',
                                            default => 'badge-secondary'
                                        };
                                        
                                        $estatText = match($projecte['estat']) {
                                            'actiu' => 'Actiu',
                                            'desenvolupament' => 'En Desenvolupament',
                                            'aturat' => 'Aturat',
                                            'archivat' => 'Archivat',
                                            default => 'Desconegut'
                                        };
                                        
                                        $dataCreacio = new DateTime($projecte['data_creacio']);
                                        $ara = new DateTime();
                                        $diferencia = $ara->diff($dataCreacio);
                                        
                                        if ($diferencia->days == 0) {
                                            $tempsText = "Avui";
                                        } elseif ($diferencia->days == 1) {
                                            $tempsText = "Fa 1 dia";
                                        } else {
                                            $tempsText = "Fa {$diferencia->days} dies";
                                        }
                                        
                                        echo "<div class='recent-post'>";
                                        echo "<h4>" . htmlspecialchars($projecte['nom']) . "</h4>";
                                        echo "<p>Creat {$tempsText}</p>";
                                        echo "<span class='badge {$estatClass}'>{$estatText}</span>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<div class='recent-post'>";
                                    echo "<h4>Cap projecte encara</h4>";
                                    echo "<p>Crea el teu primer projecte</p>";
                                    echo "<span class='badge badge-secondary'>Buit</span>";
                                    echo "</div>";
                                }
                            } catch (Exception $e) {
                                echo "<div class='recent-post'>";
                                echo "<h4>Error carregant projectes</h4>";
                                echo "<p>Hi ha hagut un error</p>";
                                echo "<span class='badge badge-danger'>Error</span>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3>Activitat Recent</h3>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="activity-feed">
                            <div class="activity-item">
                                <i class="fas fa-plus-circle"></i>
                                <div>
                                    <strong>Nova entrada creada</strong>
                                    <p>Fa 2 hores</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-edit"></i>
                                <div>
                                    <strong>Projecte actualitzat</strong>
                                    <p>Fa 4 hores</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-upload"></i>
                                <div>
                                    <strong>Imatge pujada</strong>
                                    <p>Fa 1 dia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
                        

<?php include 'includes/footer.php'; ?>
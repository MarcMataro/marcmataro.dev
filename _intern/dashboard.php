<?php
// Iniciar sessió
session_start();

// Carregar classes necessàries per a l'autenticació
require_once '../_classes/connexio.php';
require_once '../_classes/usuaris.php';

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
                        <h3>8</h3>
                        <p>Projectes</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            +3%
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
                        <h3>156</h3>
                        <p>Usuaris Únics</p>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            +15%
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
                        <h3>Entrades Recents</h3>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="recent-posts">
                            <div class="recent-post">
                                <h4>Com crear una API REST amb PHP</h4>
                                <p>Publicat fa 2 dies</p>
                                <span class="badge badge-success">Publicat</span>
                            </div>
                            <div class="recent-post">
                                <h4>Introducció a JavaScript ES6</h4>
                                <p>Publicat fa 5 dies</p>
                                <span class="badge badge-success">Publicat</span>
                            </div>
                            <div class="recent-post">
                                <h4>CSS Grid vs Flexbox</h4>
                                <p>Esborrador</p>
                                <span class="badge badge-secondary">Esborrador</span>
                            </div>
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
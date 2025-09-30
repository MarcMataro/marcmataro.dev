<?php
// Protecció d'autenticació primer de tot
require_once 'includes/auth.php';

$current_page = 'configuracio';
include 'includes/page-header.php';
$page_title = getPageTitle($current_page);

// Carregar classes necessàries
require_once '../_classes/connexio.php';
require_once '../_classes/usuaris.php';

// Establir connexió
try {
    $connexio = Connexio::getInstance();
    $usuaris = new Usuaris($connexio->getConnexio());
} catch (Exception $e) {
    die("Error de connexió: " . $e->getMessage());
}

// Processar accions
$missatge = '';
$tipusMissatge = '';

// Verificar si ve d'una actualització exitosa
if (isset($_GET['updated'])) {
    $missatge = "Usuari actualitzat correctament";
    $tipusMissatge = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accio = $_POST['accio'] ?? '';
    
    switch ($accio) {
        case 'crear':
            $resultat = $usuaris->crear([
                'nom' => $_POST['nom'],
                'cognoms' => $_POST['cognoms'],
                'email' => $_POST['email'],
                'usuari' => $_POST['usuari'],
                'password' => $_POST['password'],
                'rol' => $_POST['rol'],
                'bio' => $_POST['bio'] ?? null,
                'actiu' => isset($_POST['actiu'])
            ]);
            
            if ($resultat) {
                $missatge = "Usuari creat correctament amb ID: {$resultat}";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error en crear l'usuari";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'actualitzar':
            $dades = [
                'nom' => $_POST['nom'],
                'cognoms' => $_POST['cognoms'],
                'email' => $_POST['email'],
                'usuari' => $_POST['usuari'],
                'rol' => $_POST['rol'],
                'bio' => $_POST['bio'] ?? null,
                'actiu' => isset($_POST['actiu'])
            ];
            
            // Només afegir contrasenya si s'ha especificat
            if (!empty($_POST['password'])) {
                $dades['password'] = $_POST['password'];
            }
            
            $resultat = $usuaris->actualitzar($_POST['id'], $dades);
            
            if ($resultat) {
                $missatge = "Usuari actualitzat correctament";
                $tipusMissatge = 'success';
                // Redirigir per evitar problemes de cache
                header("Location: configuracio.php?updated=1");
                exit;
            } else {
                $missatge = "Error en actualitzar l'usuari";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'eliminar':
            $resultat = $usuaris->eliminar($_POST['id'], false); // Desactivar, no eliminar físicament
            
            if ($resultat) {
                $missatge = "Usuari desactivat correctament";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error en desactivar l'usuari";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'activar':
            $resultat = $usuaris->actualitzar($_POST['id'], ['actiu' => true]);
            
            if ($resultat) {
                $missatge = "Usuari activat correctament";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error en activar l'usuari";
                $tipusMissatge = 'error';
            }
            break;
    }
}

// Obtenir llista d'usuaris i estadístiques
try {
    $llistaUsuaris = $usuaris->obtenirTots(['ordre' => 'data_registre DESC']);
} catch (Exception $e) {
    $missatge = "Error en obtenir la llista d'usuaris: " . $e->getMessage();
    $tipusMissatge = 'error';
    $llistaUsuaris = [];
}

// Calcular estadístiques a partir de la llista d'usuaris
$estadistiques = [
    'total' => 0,
    'actius' => 0,
    'admins' => 0,
    'superadmins' => 0,
    'editors' => 0,
    'lectors' => 0
];

foreach ($llistaUsuaris as $usuari) {
    $estadistiques['total']++;
    
    if ($usuari['actiu']) {
        $estadistiques['actius']++;
    }
    
    switch ($usuari['rol']) {
        case 'superadmin':
            $estadistiques['superadmins']++;
            break;
        case 'admin':
            $estadistiques['admins']++;
            break;
        case 'editor':
            $estadistiques['editors']++;
            break;
        case 'lector':
            $estadistiques['lectors']++;
            break;
    }
}

$usuariEditar = null;

// Si s'ha sol·licitat editar un usuari
if (isset($_GET['editar'])) {
    $usuariEditar = $usuaris->obtenirPerId($_GET['editar']);
}

include 'includes/header.php';
include 'includes/sidebar.php';

// Configuració d'errors per debugging (després dels headers)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

        <section class="content-section active">
            <?php renderPageHeader($current_page); ?>
            
            <div class="section-header">
                <h2>Gestió d'Usuaris</h2>
                <p>Administra els usuaris del sistema</p>
            </div>
            
            <!-- Missatges de feedback -->
            <?php if ($missatge): ?>
                <div class="alert alert-<?php echo $tipusMissatge; ?>">
                    <?php echo htmlspecialchars($missatge); ?>
                </div>
            <?php endif; ?>
            
            <!-- Estadístiques d'usuaris -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3><?php echo $estadistiques['total']; ?></h3>
                        <p>Total Usuaris</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $estadistiques['actius']; ?></h3>
                        <p>Usuaris Actius</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👑</div>
                    <div class="stat-content">
                        <h3><?php echo $estadistiques['admins'] + $estadistiques['superadmins']; ?></h3>
                        <p>Administradors</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <h3><?php echo $estadistiques['editors']; ?></h3>
                        <p>Editors</p>
                    </div>
                </div>
            </div>
            
            <!-- Formulari per crear/editar usuari -->
            <div class="form-container">
                <h3><?php echo $usuariEditar ? 'Editar Usuari' : 'Crear Nou Usuari'; ?></h3>
                
                <form method="POST" class="user-form">
                    <input type="hidden" name="accio" value="<?php echo $usuariEditar ? 'actualitzar' : 'crear'; ?>">
                    <?php if ($usuariEditar): ?>
                        <input type="hidden" name="id" value="<?php echo $usuariEditar['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?php echo $usuariEditar ? htmlspecialchars($usuariEditar['nom']) : ''; ?>"
                                   placeholder="Nom de l'usuari">
                        </div>
                        
                        <div class="form-group">
                            <label for="cognoms">Cognoms *</label>
                            <input type="text" id="cognoms" name="cognoms" required 
                                   value="<?php echo $usuariEditar ? htmlspecialchars($usuariEditar['cognoms']) : ''; ?>"
                                   placeholder="Cognoms de l'usuari">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo $usuariEditar ? htmlspecialchars($usuariEditar['email']) : ''; ?>"
                                   placeholder="email@exemple.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="usuari">Nom d'usuari *</label>
                            <input type="text" id="usuari" name="usuari" required 
                                   value="<?php echo $usuariEditar ? htmlspecialchars($usuariEditar['usuari']) : ''; ?>"
                                   placeholder="nomusuari" pattern="[a-zA-Z0-9_]+" 
                                   title="Només lletres, números i guions baixos">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Contrasenya <?php echo $usuariEditar ? '(deixar buit per mantenir)' : '*'; ?></label>
                            <input type="password" id="password" name="password" 
                                   <?php echo $usuariEditar ? '' : 'required'; ?>
                                   placeholder="Mínim 8 caràcters amb lletres i números"
                                   minlength="8">
                        </div>
                        
                        <div class="form-group">
                            <label for="rol">Rol *</label>
                            <select id="rol" name="rol" required>
                                <?php foreach (Usuaris::getRolsValids() as $rol): ?>
                                    <option value="<?php echo $rol; ?>" 
                                            <?php echo ($usuariEditar && $usuariEditar['rol'] === $rol) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($rol); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Biografia</label>
                        <textarea id="bio" name="bio" rows="3" 
                                  placeholder="Descripció breu de l'usuari"><?php echo $usuariEditar ? htmlspecialchars($usuariEditar['bio']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="actiu" 
                                   <?php echo (!$usuariEditar || $usuariEditar['actiu']) ? 'checked' : ''; ?>>
                            Usuari actiu
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $usuariEditar ? 'Actualitzar Usuari' : 'Crear Usuari'; ?>
                        </button>
                        <?php if ($usuariEditar): ?>
                            <a href="configuracio.php" class="btn btn-secondary">Cancel·lar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Llista d'usuaris -->
            <div class="users-list">
                <div class="list-header">
                    <h3>Usuaris del Sistema</h3>
                    <div class="search-box">
                        <input type="text" id="searchUsers" placeholder="Cercar usuaris..." onkeyup="filtrarUsuaris()">
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="users-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Usuari</th>
                                <th>Rol</th>
                                <th>Estat</th>
                                <th>Registre</th>
                                <th>Últim accés</th>
                                <th>Accions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($llistaUsuaris as $usuari): ?>
                                <tr data-user-info="<?php echo strtolower($usuari['nom'] . ' ' . $usuari['cognoms'] . ' ' . $usuari['email'] . ' ' . $usuari['usuari']); ?>">
                                    <td>
                                        <div class="avatar">
                                            <?php if ($usuari['avatar']): ?>
                                                <img src="<?php echo htmlspecialchars($usuari['avatar']); ?>" alt="Avatar">
                                            <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?php echo strtoupper(substr($usuari['nom'], 0, 1) . substr($usuari['cognoms'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($usuari['nom'] . ' ' . $usuari['cognoms']); ?></strong>
                                        <?php if ($usuari['bio']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($usuari['bio'], 0, 50)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuari['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuari['usuari']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $usuari['rol']; ?>">
                                            <?php echo ucfirst($usuari['rol']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $usuari['actiu'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $usuari['actiu'] ? 'Actiu' : 'Inactiu'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($usuari['data_registre'])); ?></td>
                                    <td>
                                        <?php if (isset($usuari['data_ultim_acces']) && $usuari['data_ultim_acces']): ?>
                                            <?php echo date('d/m/Y H:i', strtotime($usuari['data_ultim_acces'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Mai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?editar=<?php echo $usuari['id']; ?>" 
                                               class="btn-action btn-edit" title="Editar">
                                                ✏️
                                            </a>
                                            
                                            <?php if ($usuari['actiu']): ?>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Estàs segur que vols desactivar aquest usuari?')">
                                                    <input type="hidden" name="accio" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $usuari['id']; ?>">
                                                    <button type="submit" class="btn-action btn-disable" title="Desactivar">
                                                        ❌
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="accio" value="activar">
                                                    <input type="hidden" name="id" value="<?php echo $usuari['id']; ?>">
                                                    <button type="submit" class="btn-action btn-enable" title="Activar">
                                                        ✅
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </section>

        <!-- CSS específic per a la gestió d'usuaris -->
        <style>
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 8px;
                font-weight: 500;
            }
            
            .alert-success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            
            .alert-error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .stat-card {
                background: white;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .stat-icon {
                font-size: 2rem;
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                color: white;
            }
            
            .stat-content h3 {
                margin: 0;
                font-size: 2rem;
                font-weight: 700;
                color: #2d3748;
            }
            
            .stat-content p {
                margin: 5px 0 0 0;
                color: #718096;
                font-size: 0.9rem;
            }
            
            .form-container {
                background: white;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                margin-bottom: 30px;
            }
            
            .form-container h3 {
                margin-bottom: 25px;
                color: #2d3748;
                font-size: 1.5rem;
            }
            
            .form-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #374151;
            }
            
            .form-group input, .form-group select, .form-group textarea {
                width: 100%;
                padding: 12px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 14px;
                transition: border-color 0.3s ease;
            }
            
            .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .checkbox-group {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .checkbox-group input[type="checkbox"] {
                width: auto;
                margin-right: 8px;
            }
            
            .form-actions {
                margin-top: 25px;
                display: flex;
                gap: 15px;
            }
            
            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-block;
                text-align: center;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            }
            
            .btn-secondary {
                background: #6b7280;
                color: white;
            }
            
            .btn-secondary:hover {
                background: #4b5563;
            }
            
            .users-list {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            
            .list-header {
                padding: 20px 30px;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .list-header h3 {
                margin: 0;
                color: #2d3748;
            }
            
            .search-box input {
                padding: 10px 15px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                width: 300px;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .users-table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .users-table th {
                background: #f9fafb;
                padding: 15px;
                text-align: left;
                font-weight: 600;
                color: #374151;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .users-table td {
                padding: 15px;
                border-bottom: 1px solid #f3f4f6;
                vertical-align: middle;
            }
            
            .users-table tr:hover {
                background: #f9fafb;
            }
            
            .avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                overflow: hidden;
            }
            
            .avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .avatar-placeholder {
                width: 40px;
                height: 40px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 14px;
                border-radius: 50%;
            }
            
            .role-badge {
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .role-superadmin { background: #fecaca; color: #991b1b; }
            .role-admin { background: #fed7aa; color: #9a3412; }
            .role-editor { background: #bfdbfe; color: #1e40af; }
            .role-lector { background: #d1fae5; color: #065f46; }
            
            .status-badge {
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
            }
            
            .status-active { background: #d1fae5; color: #065f46; }
            .status-inactive { background: #fee2e2; color: #991b1b; }
            
            .action-buttons {
                display: flex;
                gap: 8px;
            }
            
            .btn-action {
                width: 32px;
                height: 32px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                transition: all 0.3s ease;
                text-decoration: none;
            }
            
            .btn-edit { background: #dbeafe; color: #1e40af; }
            .btn-edit:hover { background: #bfdbfe; }
            
            .btn-disable { background: #fee2e2; color: #991b1b; }
            .btn-disable:hover { background: #fecaca; }
            
            .btn-enable { background: #d1fae5; color: #065f46; }
            .btn-enable:hover { background: #a7f3d0; }
            
            .text-muted {
                color: #6b7280;
                font-size: 0.9rem;
            }
            
            @media (max-width: 768px) {
                .stats-grid {
                    grid-template-columns: 1fr;
                }
                
                .form-grid {
                    grid-template-columns: 1fr;
                }
                
                .list-header {
                    flex-direction: column;
                    gap: 15px;
                    align-items: stretch;
                }
                
                .search-box input {
                    width: 100%;
                }
                
                .users-table {
                    font-size: 14px;
                }
                
                .users-table th, .users-table td {
                    padding: 10px 8px;
                }
            }
        </style>
        
        <!-- JavaScript per a la funcionalitat -->
        <script>
            function filtrarUsuaris() {
                const searchTerm = document.getElementById('searchUsers').value.toLowerCase();
                const rows = document.querySelectorAll('#usersTable tbody tr');
                
                rows.forEach(row => {
                    const userInfo = row.getAttribute('data-user-info');
                    if (userInfo.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            // Validació del formulari
            document.querySelector('.user-form').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const isEditing = document.querySelector('input[name="accio"]').value === 'actualitzar';
                
                if (!isEditing && password.length < 8) {
                    e.preventDefault();
                    alert('La contrasenya ha de tenir almenys 8 caràcters');
                    return;
                }
                
                if (password && !/(?=.*[a-zA-Z])(?=.*[0-9])/.test(password)) {
                    e.preventDefault();
                    alert('La contrasenya ha de contenir almenys una lletra i un número');
                    return;
                }
            });
            
            // Auto-amagar alertes després de 5 segons
            document.addEventListener('DOMContentLoaded', function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }, 5000);
                });
            });
        </script>
        </section>

<?php include 'includes/footer.php'; ?>
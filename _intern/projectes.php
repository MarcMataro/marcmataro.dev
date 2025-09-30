<?php
// Protecció d'autenticació primer de tot
require_once 'includes/auth.php';
require_once '../_classes/connexio.php';
require_once '../_classes/projectes.php';

// Funció per obtenir el base URL del projecte
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Detectar el subdirectori automàticament
    $scriptName = $_SERVER['SCRIPT_NAME']; // Ex: /marcmataro.dev/_intern/projectes.php
    $pathParts = explode('/', $scriptName);
    
    // Si estem en un subdirectori (com /marcmataro.dev/), el detectem
    if (count($pathParts) > 2 && $pathParts[1] !== '') {
        return '/' . $pathParts[1]; // Retorna /marcmataro.dev
    }
    
    return ''; // Si estem a l'arrel del domini
}

$baseUrl = getBaseUrl();

// Obtenir projectes de la base de dades
try {
    $connexio = Connexio::getInstance()->getConnexio();
    $gestorProjectes = new Projectes($connexio);
    
    // Configurar filtres i ordenació
    $filtrEstat = $_GET['estat'] ?? '';
    $cercar = $_GET['cercar'] ?? '';
    $ordre = $_GET['ordre'] ?? 'data_creacio DESC';
    
    $opcions = [
        'ordre' => $ordre
    ];
    
    if ($filtrEstat && $filtrEstat !== 'tots') {
        $opcions['on'] = "estat = :estat";
        $opcions['parametres'] = ['estat' => $filtrEstat];
    }
    
    $projectes = $gestorProjectes->obtenirAmbTraducio('ca', $opcions);
    
    // Filtrar per cerca si s'especifica
    if (!empty($cercar)) {
        $projectes = array_filter($projectes, function($projecte) use ($cercar) {
            return stripos($projecte['nom'] ?? '', $cercar) !== false || 
                   stripos($projecte['descripcio_curta'] ?? '', $cercar) !== false ||
                   stripos($projecte['descripcio_detallada'] ?? '', $cercar) !== false ||
                   stripos($projecte['tecnologies_principals'] ?? '', $cercar) !== false;
        });
    }
    
} catch (Exception $e) {
    error_log("Error carregant projectes: " . $e->getMessage());
    $projectes = [];
}

$current_page = 'projectes';
include 'includes/page-header.php';
$page_title = getPageTitle($current_page);
include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <section class="content-section active">
            <?php renderPageHeader($current_page); ?>
            
            <div class="projects-content">
                <div class="section-header">
                    <h2>Projectes</h2>
                    <button class="btn-add-project" id="addProjectBtn">
                        <i class="fas fa-plus"></i>
                        Nou Projecte
                    </button>
                </div>
            
            <div class="filters-bar">
                <div class="filters-header">
                    <h4>Filtres i Cerca</h4>
                    <?php if (!empty($filtrEstat) || !empty($cercar)): ?>
                        <a href="projectes.php" class="clear-filters-btn">
                            <i class="fas fa-times"></i>
                            Netejar filtres
                        </a>
                    <?php endif; ?>
                </div>
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="estat-filter">Estat</label>
                        <select id="estat-filter" name="estat" onchange="this.form.submit()">
                            <option value="">Tots els estats</option>
                            <option value="actiu" <?php echo ($filtrEstat === 'actiu') ? 'selected' : ''; ?>>Actiu</option>
                            <option value="desenvolupament" <?php echo ($filtrEstat === 'desenvolupament') ? 'selected' : ''; ?>>En Desenvolupament</option>
                            <option value="aturat" <?php echo ($filtrEstat === 'aturat') ? 'selected' : ''; ?>>Aturat</option>
                            <option value="archivat" <?php echo ($filtrEstat === 'archivat') ? 'selected' : ''; ?>>Archivat</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="ordre-filter">Ordenar per</label>
                        <select id="ordre-filter" name="ordre" onchange="this.form.submit()">
                            <option value="data_creacio DESC" <?php echo ($ordre === 'data_creacio DESC') ? 'selected' : ''; ?>>Més recents primer</option>
                            <option value="data_creacio ASC" <?php echo ($ordre === 'data_creacio ASC') ? 'selected' : ''; ?>>Més antics primer</option>
                            <option value="nom ASC" <?php echo ($ordre === 'nom ASC') ? 'selected' : ''; ?>>Nom A-Z</option>
                            <option value="nom DESC" <?php echo ($ordre === 'nom DESC') ? 'selected' : ''; ?>>Nom Z-A</option>
                        </select>
                    </div>
                    <div class="search-filter">
                        <label for="cercar-filter">Cerca</label>
                        <div class="search-input-wrapper">
                            <input type="text" id="cercar-filter" name="cercar" placeholder="Nom, descripció o tecnologies..." value="<?php echo htmlspecialchars($cercar); ?>">
                            <i class="fas fa-search"></i>
                            <button type="submit" class="search-btn" title="Cercar">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <input type="hidden" name="estat" value="<?php echo htmlspecialchars($filtrEstat); ?>">
                        <input type="hidden" name="ordre" value="<?php echo htmlspecialchars($ordre); ?>">
                    </div>
                </form>
            </div>
            
            <div class="projects-grid">
                <?php if (!empty($projectes)): ?>
                    <?php foreach ($projectes as $projecte): ?>
                        <?php
                        // Preparar estat amb classes CSS
                        $estatClass = match($projecte['estat']) {
                            'actiu' => 'published',
                            'desenvolupament' => 'development',
                            'aturat' => 'draft', 
                            'archivat' => 'archived',
                            default => 'draft'
                        };
                        
                        $estatText = match($projecte['estat']) {
                            'actiu' => 'Actiu',
                            'desenvolupament' => 'En Desenvolupament',
                            'aturat' => 'Aturat',
                            'archivat' => 'Archivat',
                            default => 'Desconegut'
                        };
                        
                        // Formatar data
                        $dataCreacio = new DateTime($projecte['data_creacio']);
                        $dataFormatada = $dataCreacio->format('d/m/Y');
                        
                        // Processar tecnologies principals (JSON)
                        $tecnologiesJson = !empty($projecte['tecnologies_principals']) ? json_decode($projecte['tecnologies_principals'], true) : [];
                        $tecnologies = is_array($tecnologiesJson) ? $tecnologiesJson : [];
                        
                        // Imatge del projecte (amb base URL automàtic)
                        $imatgeProjecte = $baseUrl . '/img/placeholder-project.jpg'; // Valor per defecte
                        
                        if (!empty($projecte['imatge_portada'])) {
                            $nomFitxer = $projecte['imatge_portada'];
                            
                            // Si el nom del fitxer ja conté una ruta (relativa o absoluta), netejar-la
                            if (strpos($nomFitxer, '/') !== false) {
                                // Extreure només el nom del fitxer
                                $nomFitxer = basename($nomFitxer);
                            }
                            
                            // Totes les imatges estan a /img/Projects/
                            $baseDir = dirname(__DIR__); // Pujar un nivell des de _intern/
                            $rutaWeb = $baseUrl . '/img/Projects/' . $nomFitxer;
                            $rutaFisica = $baseDir . '/img/Projects/' . $nomFitxer;
                            
                            if (file_exists($rutaFisica)) {
                                $imatgeProjecte = $rutaWeb;
                            } else {
                                // Debug: log imatges no trobades
                                error_log("Imatge no trobada: '$nomFitxer' a $rutaFisica per projecte ID: " . $projecte['id']);
                            }
                        }
                        ?>
                        
                        <div class="project-card-admin" data-id="<?php echo $projecte['id']; ?>">
                            <div class="project-card-header">
                                <img src="<?php echo htmlspecialchars($imatgeProjecte); ?>" 
                                     alt="<?php echo htmlspecialchars($projecte['nom'] ?? 'Projecte'); ?>"
                                     onerror="this.src='<?php echo $baseUrl; ?>/img/placeholder-project.jpg'">
                                <div class="project-actions">
                                    <button class="btn-icon edit-project" title="Editar" data-id="<?php echo $projecte['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete-project" title="Eliminar" data-id="<?php echo $projecte['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php if (!empty($projecte['url_demo'])): ?>
                                    <button class="btn-icon view-project" title="Veure Demo" data-url="<?php echo htmlspecialchars($projecte['url_demo']); ?>">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="project-card-body">
                                <h3><?php echo htmlspecialchars($projecte['nom'] ?? 'Sense títol'); ?></h3>
                                <p><?php 
                                    $descripcio = $projecte['descripcio_curta'] ?? '';
                                    echo htmlspecialchars(mb_substr($descripcio, 0, 100)) . (mb_strlen($descripcio) > 100 ? '...' : ''); 
                                ?></p>
                                
                                <?php if (!empty($tecnologies)): ?>
                                <div class="project-tech">
                                    <?php foreach (array_slice($tecnologies, 0, 3) as $tech): ?>
                                        <span class="tech-badge"><?php echo htmlspecialchars(trim($tech)); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($tecnologies) > 3): ?>
                                        <span class="tech-badge more">+<?php echo count($tecnologies) - 3; ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="project-meta">
                                    <span class="project-status <?php echo $estatClass; ?>"><?php echo $estatText; ?></span>
                                    <span class="project-date"><?php echo $dataFormatada; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-projects">
                        <i class="fas fa-folder-open"></i>
                        <h3>Cap projecte trobat</h3>
                        <p>
                            <?php if (!empty($cercar) || !empty($filtrEstat)): ?>
                                No hi ha projectes que coincideixin amb els filtres aplicats.
                            <?php else: ?>
                                Encara no has creat cap projecte. Comença creant el teu primer projecte!
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Card per afegir nou projecte -->
                <div class="project-card-admin add-new">
                    <div class="add-new-content">
                        <i class="fas fa-plus"></i>
                        <h3>Afegir Nou Projecte</h3>
                        <p>Crea un nou projecte per mostrar en el teu portfolio</p>
                        <button class="btn btn-primary">Crear Projecte</button>
                    </div>
                </div>
            </div>
            </div> <!-- End projects-content -->
        </section>

        <!-- Modal for New Project -->
        <div id="newProjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Nou Projecte</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="newProjectForm">
                        <div class="form-group">
                            <label>Nom del Projecte</label>
                            <input type="text" required>
                        </div>
                        <div class="form-group">
                            <label>Descripció</label>
                            <textarea rows="4" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tecnologies</label>
                                <input type="text" placeholder="Laravel, Vue.js, MySQL...">
                            </div>
                            <div class="form-group">
                                <label>Estat</label>
                                <select required>
                                    <option value="">Selecciona estat</option>
                                    <option>Publicat</option>
                                    <option>Esborrany</option>
                                    <option>En desenvolupament</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>URL del Projecte</label>
                            <input type="url" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>Imatge del Projecte</label>
                            <input type="file" accept="image/*">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline">Cancel·lar</button>
                            <button type="submit" class="btn btn-primary">Crear Projecte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestionar botons d'editar projecte
    const editButtons = document.querySelectorAll('.edit-project');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const projectId = this.getAttribute('data-id');
            window.location.href = 'formulari-projecte.php?id=' + projectId;
        });
    });
    
    // Gestionar botó de nou projecte (header)
    const addProjectBtn = document.getElementById('addProjectBtn');
    if (addProjectBtn) {
        addProjectBtn.addEventListener('click', function() {
            window.location.href = 'formulari-projecte.php';
        });
    }
    
    // Gestionar botó de nou projecte (card)
    const addNewCard = document.querySelector('.add-new .btn');
    if (addNewCard) {
        addNewCard.addEventListener('click', function() {
            window.location.href = 'formulari-projecte.php';
        });
    }
    
    // Gestionar botons de veure demo
    const viewButtons = document.querySelectorAll('.view-project');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.open(url, '_blank');
            }
        });
    });
    
    // Gestionar botons d'eliminar (amb confirmació)
    const deleteButtons = document.querySelectorAll('.delete-project');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const projectId = this.getAttribute('data-id');
            if (confirm('Estàs segur que vols eliminar aquest projecte? Aquesta acció no es pot desfer.')) {
                // Aquí hauria d'anar la lògica d'eliminació via AJAX o redirecció
                console.log('Eliminar projecte ID:', projectId);
                alert('Funcionalitat d\'eliminació per implementar');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
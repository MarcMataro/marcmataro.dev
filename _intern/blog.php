
<?php
require_once 'includes/auth.php';
require_once '../_classes/connexio.php';
require_once '../_classes/blog.php';

try {
    $db = Connexio::getInstance()->getConnexio();
    $blog = new Blog($db);
} catch (Exception $e) {
    die("Error de connexió: " . $e->getMessage());
}

$tabActiva = $_GET['tab'] ?? 'entrades';
$missatge = $tipusMissatge = null;

function getImagesFromDirectory($directory) {
    $files = glob($directory . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
    usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
    return array_map('basename', $files);
}
$images = getImagesFromDirectory("../img/blog/");

// --- Gestió d'accions per pestanyes ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accio = $_POST['accio'] ?? '';
        switch ($tabActiva) {
            case 'entrades':
                switch ($accio) {
                    case 'crear':
                        $resultat = $blog->entrades->crear([
                            'idioma_original' => $_POST['idioma_original'] ?? 'ca',
                            'autor_id' => $_SESSION['user_id'] ?? 1,
                            'estat' => $_POST['estat'] ?? 'esborrany',
                            'format' => $_POST['format'] ?? 'estandard',
                            'comentaris_activats' => isset($_POST['comentaris_activats']) ? 1 : 0,
                            'destacat' => isset($_POST['destacat']) ? 1 : 0,
                            'traduccions' => [
                                'ca' => [
                                    'titol' => $_POST['titol_ca'] ?? '',
                                    'contingut' => $_POST['contingut_ca'] ?? '',
                                    'resum' => $_POST['resum_ca'] ?? ''
                                ],
                                'es' => [
                                    'titol' => $_POST['titol_es'] ?? '',
                                    'contingut' => $_POST['contingut_es'] ?? '',
                                    'resum' => $_POST['resum_es'] ?? ''
                                ],
                                'en' => [
                                    'titol' => $_POST['titol_en'] ?? '',
                                    'contingut' => $_POST['contingut_en'] ?? '',
                                    'resum' => $_POST['resum_en'] ?? ''
                                ]
                            ]
                        ]);
                        $missatge = $resultat['success'] ? "Entrada creada correctament" : implode(', ', $resultat['errors']);
                        $tipusMissatge = $resultat['success'] ? "success" : "danger";
                        break;
                    case 'editar':
                        $id = (int)$_POST['id'];
                        $resultat = $blog->entrades->actualitzar($id, [
                            'estat' => $_POST['estat'] ?? 'esborrany',
                            'format' => $_POST['format'] ?? 'estandard',
                            'comentaris_activats' => isset($_POST['comentaris_activats']) ? 1 : 0,
                            'destacat' => isset($_POST['destacat']) ? 1 : 0,
                            'traduccions' => [
                                'ca' => [
                                    'titol' => $_POST['titol_ca'] ?? '',
                                    'contingut' => $_POST['contingut_ca'] ?? '',
                                    'resum' => $_POST['resum_ca'] ?? ''
                                ],
                                'es' => [
                                    'titol' => $_POST['titol_es'] ?? '',
                                    'contingut' => $_POST['contingut_es'] ?? '',
                                    'resum' => $_POST['resum_es'] ?? ''
                                ],
                                'en' => [
                                    'titol' => $_POST['titol_en'] ?? '',
                                    'contingut' => $_POST['contingut_en'] ?? '',
                                    'resum' => $_POST['resum_en'] ?? ''
                                ]
                            ]
                        ]);
                        $missatge = $resultat['success'] ? "Entrada actualitzada correctament" : implode(', ', $resultat['errors']);
                        $tipusMissatge = $resultat['success'] ? "success" : "danger";
                        break;
                    case 'eliminar':
                        $id = (int)$_POST['id'];
                        $resultat = $blog->entrades->eliminar($id);
                        $missatge = $resultat['success'] ? "Entrada eliminada correctament" : implode(', ', $resultat['errors']);
                        $tipusMissatge = $resultat['success'] ? "success" : "danger";
                        break;
                    case 'canviar_estat':
                        $id = (int)$_POST['id'];
                        $nouEstat = $_POST['nou_estat'];
                        $resultat = $blog->entrades->canviarEstat($id, $nouEstat);
                        $missatge = $resultat['success'] ? "Estat de l'entrada canviat correctament" : implode(', ', $resultat['errors']);
                        $tipusMissatge = $resultat['success'] ? "success" : "danger";
                        break;
                }
                break;
            // ...existing code for idiomes, categories, tags...
        }
    } catch (Exception $e) {
        $missatge = "Error: " . $e->getMessage();
        $tipusMissatge = "danger";
    }
}

// Gestionar accions per idiomes
if ($tabActiva === 'idiomes' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accio = $_POST['accio'] ?? '';
        
        switch ($accio) {
            case 'crear':
                $resultat = $blog->idiomes->crear([
                    'codi' => $_POST['codi'],
                    'nom' => $_POST['nom'],
                    'nom_natiu' => $_POST['nom_natiu'],
                    'estat' => 'actiu',
                    'ordre' => (int)($_POST['ordre'] ?? 99),
                    'bandera_url' => $_POST['bandera_url'] ?? ''
                ]);
                
                if ($resultat['success']) {
                    $missatge = "Idioma creat correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'editar':
                $id = (int)$_POST['id'];
                $resultat = $blog->idiomes->actualitzar($id, [
                    'codi' => $_POST['codi_hidden'] ?? $_POST['codi'], // Utilitzar el camp hidden si existeix
                    'nom' => $_POST['nom'],
                    'nom_natiu' => $_POST['nom_natiu'],
                    'estat' => $_POST['estat'],
                    'ordre' => (int)$_POST['ordre'],
                    'bandera_url' => $_POST['bandera_url'] ?? ''
                ]);
                
                if ($resultat['success']) {
                    $missatge = "Idioma actualitzat correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'eliminar':
                $id = (int)$_POST['id'];
                $resultat = $blog->idiomes->eliminar($id);
                
                if ($resultat['success']) {
                    $missatge = "Idioma eliminat correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'canviar_estat':
                $id = (int)$_POST['id'];
                $nouEstat = $_POST['nou_estat'];
                $resultat = $blog->idiomes->actualitzar($id, ['estat' => $nouEstat]);
                
                if ($resultat['success']) {
                    $missatge = "Estat de l'idioma canviat correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
        }
    } catch (Exception $e) {
        $missatge = "Error: " . $e->getMessage();
        $tipusMissatge = "danger";
    }
}

// Gestionar accions per categories
if ($tabActiva === 'categories' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accio = $_POST['accio'] ?? '';
        
        switch ($accio) {
            case 'crear':
                $resultat = $blog->categories->crear([
                    'slug_base' => $_POST['slug_base'],
                    'traduccions' => [
                        'ca' => [
                            'nom' => $_POST['nom_ca'],
                            'slug' => $_POST['slug_ca'] ?? '',
                            'descripcio' => $_POST['descripcio_ca'] ?? ''
                        ],
                        'es' => [
                            'nom' => $_POST['nom_es'] ?? '',
                            'slug' => $_POST['slug_es'] ?? '',
                            'descripcio' => $_POST['descripcio_es'] ?? ''
                        ],
                        'en' => [
                            'nom' => $_POST['nom_en'] ?? '',
                            'slug' => $_POST['slug_en'] ?? '',
                            'descripcio' => $_POST['descripcio_en'] ?? ''
                        ]
                    ],
                    'estat' => 'actiu',
                    'ordre' => (int)($_POST['ordre'] ?? 0),
                    'categoria_pare_id' => !empty($_POST['categoria_pare_id']) ? (int)$_POST['categoria_pare_id'] : null
                ]);
                
                if ($resultat['success']) {
                    $missatge = "Categoria creada correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'editar':
                $id = (int)$_POST['id'];
                $resultat = $blog->categories->actualitzar($id, [
                    'slug_base' => $_POST['slug_base'],
                    'traduccions' => [
                        'ca' => [
                            'nom' => $_POST['nom_ca'],
                            'slug' => $_POST['slug_ca'] ?? '',
                            'descripcio' => $_POST['descripcio_ca'] ?? ''
                        ],
                        'es' => [
                            'nom' => $_POST['nom_es'] ?? '',
                            'slug' => $_POST['slug_es'] ?? '',
                            'descripcio' => $_POST['descripcio_es'] ?? ''
                        ],
                        'en' => [
                            'nom' => $_POST['nom_en'] ?? '',
                            'slug' => $_POST['slug_en'] ?? '',
                            'descripcio' => $_POST['descripcio_en'] ?? ''
                        ]
                    ],
                    'ordre' => (int)($_POST['ordre'] ?? 0),
                    'categoria_pare_id' => !empty($_POST['categoria_pare_id']) ? (int)$_POST['categoria_pare_id'] : null
                ]);
                
                if ($resultat['success']) {
                    $missatge = "Categoria actualitzada correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'eliminar':
                $id = (int)$_POST['id'];
                $resultat = $blog->categories->eliminar($id);
                
                if ($resultat['success']) {
                    $missatge = "Categoria eliminada correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'canviar_estat':
                $id = (int)$_POST['id'];
                
                // Obtenir categoria actual per canviar l'estat
                $categoriaActual = $blog->categories->obtenirPerId($id, 'ca');
                if ($categoriaActual) {
                    $nouEstat = ($categoriaActual['estat'] === 'actiu') ? 'inactiu' : 'actiu';
                    $resultat = $blog->categories->actualitzar($id, ['estat' => $nouEstat]);
                    
                    if ($resultat['success']) {
                        $missatge = "Estat de la categoria canviat correctament";
                        $tipusMissatge = "success";
                    } else {
                        $missatge = implode(', ', $resultat['errors']);
                        $tipusMissatge = "danger";
                    }
                } else {
                    $missatge = "No s'ha trobat la categoria";
                    $tipusMissatge = "danger";
                }
                break;
        }
    } catch (Exception $e) {
        $missatge = "Error: " . $e->getMessage();
        $tipusMissatge = "danger";
    }
}

// Gestionar accions per tags
if ($tabActiva === 'tags' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accio = $_POST['accio'] ?? '';
        
        switch ($accio) {
            case 'crear':
                $resultat = $blog->tags->crear([
                    'slug_base' => $_POST['slug_base'],
                    'traduccions' => [
                        'ca' => [
                            'nom' => $_POST['nom_ca'],
                            'slug' => $_POST['slug_ca'] ?? '',
                            'descripcio' => $_POST['descripcio_ca'] ?? ''
                        ],
                        'es' => [
                            'nom' => $_POST['nom_es'] ?? '',
                            'slug' => $_POST['slug_es'] ?? '',
                            'descripcio' => $_POST['descripcio_es'] ?? ''
                        ],
                        'en' => [
                            'nom' => $_POST['nom_en'] ?? '',
                            'slug' => $_POST['slug_en'] ?? '',
                            'descripcio' => $_POST['descripcio_en'] ?? ''
                        ]
                    ]
                ]);
                
                if ($resultat['success']) {
                    $missatge = "Tag creat correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'editar':
                $id = (int)$_POST['id'];
                $resultat = $blog->tags->actualitzar($id, [
                    'slug_base' => $_POST['slug_base'],
                    'traduccions' => [
                        'ca' => [
                            'nom' => $_POST['nom_ca'],
                            'slug' => $_POST['slug_ca'] ?? '',
                            'descripcio' => $_POST['descripcio_ca'] ?? ''
                        ],
                        'es' => [
                            'nom' => $_POST['nom_es'] ?? '',
                            'slug' => $_POST['slug_es'] ?? '',
                            'descripcio' => $_POST['descripcio_es'] ?? ''
                        ],
                        'en' => [
                            'nom' => $_POST['nom_en'] ?? '',
                            'slug' => $_POST['slug_en'] ?? '',
                            'descripcio' => $_POST['descripcio_en'] ?? ''
                        ]
                    ]
                ]);
                
                if ($resultat['success']) {
                    $missatge = "Tag actualitzat correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'eliminar':
                $id = (int)$_POST['id'];
                $resultat = $blog->tags->eliminar($id);
                
                if ($resultat['success']) {
                    $missatge = "Tag eliminat correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
        }
    } catch (Exception $e) {
        $missatge = "Error: " . $e->getMessage();
        $tipusMissatge = "danger";
    }
}

// Obtenir dades segons la pestanya
$dades = [];
$error = null;

try {
    switch ($tabActiva) {
        case 'entrades':
            $dades = []; // Temporal - simplement mostrar que funciona
            break;
        case 'categories':
            $dades = $blog->categories->obtenirAmbTotesLesTraduccions();
            break;
        case 'comentaris':
            $dades = []; // Temporal
            break;
        case 'usuaris':
            $dades = []; // Temporal
            break;
        case 'idiomes':
            $dades = $blog->idiomes->obtenirTots();
            break;
        case 'tags':
            $dades = $blog->tags->obtenirAmbTotesLesTraduccions();
            break;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $dades = [];
}

// Configuració de la pàgina
$current_page = 'blog';
include 'includes/page-header.php';
$page_title = getPageTitle($current_page);
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<link rel="stylesheet" href="../css/normalize.css">
<link rel="stylesheet" href="css/blog-admin.css">

<!-- Page Content -->
<section class="content-section active">
    <?php renderPageHeader($current_page); ?>
    
    <div class="page-actions">
        <a href="?tab=nova-entrada" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Entrada
        </a>
        <a href="?tab=configuracio" class="btn btn-secondary">
            <i class="fas fa-cog"></i> Configuració
        </a>
    </div>

    <!-- Estadístiques Básiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">-</div>
                <div class="stat-label">Entrades</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">-</div>
                <div class="stat-label">Comentaris</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">-</div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">-</div>
                <div class="stat-label">Usuaris</div>
            </div>
        </div>
    </div>

    <!-- Pestanyes -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <a href="?tab=entrades" class="tab-btn <?= $tabActiva === 'entrades' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Entrades
            </a>
            <a href="?tab=categories" class="tab-btn <?= $tabActiva === 'categories' ? 'active' : '' ?>">
                <i class="fas fa-folder"></i> Categories
            </a>
            <a href="?tab=comentaris" class="tab-btn <?= $tabActiva === 'comentaris' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i> Comentaris
            </a>
            <a href="?tab=usuaris" class="tab-btn <?= $tabActiva === 'usuaris' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Usuaris
            </a>
            <a href="?tab=idiomes" class="tab-btn <?= $tabActiva === 'idiomes' ? 'active' : '' ?>">
                <i class="fas fa-globe"></i> Idiomes
            </a>
            <a href="?tab=tags" class="tab-btn <?= $tabActiva === 'tags' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Tags
            </a>
        </div>

        <!-- Contingut del Tab Actiu -->
        <div class="tab-content active">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    Error: <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($tabActiva === 'entrades'): ?>
                <?php $entrades = $blog->entrades->obtenirTotes(['limit' => 50]); ?>
                
                <div class="section-header">
                    <h2>Gestió d'Entrades</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="mostrarFormulariEntrada('nou')">
                            <i class="fas fa-plus"></i> Nova Entrada
                        </button>
                    </div>
                </div>

                <?php if ($missatge && $tabActiva === 'entrades'): ?>
                    <div class="alert alert-<?= $tipusMissatge ?>">
                        <?= htmlspecialchars($missatge) ?>
                    </div>
                <?php endif; ?>

                <div class="filters-container">
                    <div class="filters-group">
                        <select class="filter-select" id="filtreEstat">
                            <option value="">Tots els estats</option>
                            <option value="esborrany">Esborrany</option>
                            <option value="revisio">En revisió</option>
                            <option value="programat">Programat</option>
                            <option value="publicat">Publicat</option>
                            <option value="arxivat">Arxivat</option>
                        </select>
                        
                        <select class="filter-select" id="filtreAutor">
                            <option value="">Tots els autors</option>
                        </select>
                        
                        <input type="text" class="filter-input" placeholder="Cercar entrades..." id="cercaEntrades">
                    </div>
                </div>

                <div class="table-container">
                    <?php if (empty($entrades)): ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt fa-3x"></i>
                            <h3>Cap entrada trobada</h3>
                            <p>No s'han trobat entrades al blog. Crea la primera entrada!</p>
                            <button class="btn btn-primary" onclick="mostrarFormulariEntrada('nou')">
                                <i class="fas fa-plus"></i> Crear Primera Entrada
                            </button>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="select-all"></th>
                                    <th>Títol</th>
                                    <th>Autor</th>
                                    <th>Estat</th>
                                    <th>Data</th>
                                    <th>Visites</th>
                                    <th>Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entrades as $entrada): ?>
                                    <?php 
                                    $titol = $entrada['traduccions']['ca']['titol'] ?? 
                                            $entrada['traduccions']['es']['titol'] ?? 
                                            $entrada['traduccions']['en']['titol'] ?? 
                                            'Sense títol';
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" value="<?= $entrada['id'] ?>"></td>
                                        <td>
                                            <div class="entry-title">
                                                <strong><?= htmlspecialchars($titol) ?></strong>
                                                <?php if ($entrada['destacat']): ?>
                                                    <span class="badge badge-warning">Destacat</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="entry-meta">
                                                Format: <?= ucfirst($entrada['format']) ?>
                                                <?php if (!empty($entrada['slug_base'])): ?>
                                                    | Slug: <?= htmlspecialchars($entrada['slug_base']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($entrada['autor_nom'] ?? 'Desconegut') ?></td>
                                        <td>
                                            <span class="badge badge-<?= 
                                                $entrada['estat'] === 'publicat' ? 'success' : 
                                                ($entrada['estat'] === 'esborrany' ? 'secondary' : 
                                                ($entrada['estat'] === 'programat' ? 'info' : 'warning')) 
                                            ?>">
                                                <?= ucfirst($entrada['estat']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-info">
                                                <small>Creat: <?= date('d/m/Y H:i', strtotime($entrada['data_creacio'])) ?></small>
                                                <?php if ($entrada['data_publicacio']): ?>
                                                    <br><small>Publicat: <?= date('d/m/Y H:i', strtotime($entrada['data_publicacio'])) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= number_format($entrada['visites'] ?? 0) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon" title="Editar" 
                                                        onclick='editarEntrada(<?= json_encode($entrada) ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <?php if ($entrada['estat'] !== 'publicat'): ?>
                                                    <button class="btn-icon btn-success" title="Publicar"
                                                            onclick="canviarEstatEntrada(<?= $entrada['id'] ?>, 'publicat')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn-icon btn-warning" title="Despublicar"
                                                            onclick="canviarEstatEntrada(<?= $entrada['id'] ?>, 'esborrany')">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn-icon btn-danger" title="Eliminar"
                                                        onclick="eliminarEntrada(<?= $entrada['id'] ?>, '<?= htmlspecialchars($titol) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            <?php elseif ($tabActiva === 'comentaris'): ?>
                <div class="section-header">
                    <h2>Gestió de Comentaris</h2>
                    <p>Pròximament: moderació de comentaris</p>
                </div>

            <?php elseif ($tabActiva === 'usuaris'): ?>
                <div class="section-header">
                    <h2>Gestió d'Usuaris</h2>
                    <p>Pròximament: gestió d'usuaris del blog</p>
                </div>

            <?php elseif ($tabActiva === 'categories'): ?>
                <div class="section-header">
                    <h2>Gestió de Categories</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="mostrarFormulariCategoria('nou')">
                            <i class="fas fa-plus"></i> Afegir Categoria
                        </button>
                    </div>
                </div>

                <?php if ($missatge): ?>
                    <div class="alert alert-<?= $tipusMissatge ?>">
                        <?= htmlspecialchars($missatge) ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <?php if (empty($dades)): ?>
                        <div class="empty-state">
                            <i class="fas fa-folder fa-3x"></i>
                            <h3>Cap categoria trobada</h3>
                            <p>No s'han trobat categories configurades al sistema.</p>
                            <button class="btn btn-primary" onclick="mostrarFormulariCategoria('nou')">
                                <i class="fas fa-plus"></i> Crear la primera categoria
                            </button>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Slug Base</th>
                                    <th>Jerarquia</th>
                                    <th>Estat</th>
                                    <th>Ordre</th>
                                    <th>Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dades as $categoria): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($categoria['nom'] ?? 'Sense nom') ?></strong>
                                            <?php if (!empty($categoria['descripcio'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($categoria['descripcio'], 0, 80)) ?><?= strlen($categoria['descripcio']) > 80 ? '...' : '' ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= htmlspecialchars($categoria['slug_base']) ?></code></td>
                                        <td>
                                            <?php if ($categoria['categoria_pare_id']): ?>
                                                <span class="badge badge-info">
                                                    <i class="fas fa-level-up-alt"></i> Subcategoria
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-folder"></i> Principal
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="accio" value="canviar_estat">
                                                <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                                                <button type="submit" class="badge badge-<?= $categoria['estat'] === 'actiu' ? 'success' : 'secondary' ?>" 
                                                        style="border: none; cursor: pointer;" 
                                                        title="Clic per canviar estat">
                                                    <?= ucfirst($categoria['estat']) ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?= $categoria['ordre'] ?? 0 ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editarCategoria(<?= htmlspecialchars(json_encode($categoria)) ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Estàs segur que vols eliminar aquesta categoria? Aquesta acció no es pot desfer.')">
                                                <input type="hidden" name="accio" value="eliminar">
                                                <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Formulari Nou/Editar Categoria -->
                <div id="formulari-categoria" class="formulari-container" style="display: none;">
                    <div class="form-card">
                        <div class="form-header">
                            <h3 id="form-title-categoria">Nova Categoria</h3>
                            <button type="button" class="btn-close" onclick="tancarFormulariCategoria()">×</button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="accio" id="form-accio-categoria" value="crear">
                            <input type="hidden" name="id" id="form-id-categoria" value="">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="slug_base">Slug Base *</label>
                                    <input type="text" name="slug_base" id="form-slug-base" required 
                                           placeholder="categoria-principal">
                                    <small>URL única per a la categoria (només lletres, números i guions)</small>
                                </div>
                                <div class="form-group">
                                    <label for="ordre">Ordre</label>
                                    <input type="number" name="ordre" id="form-ordre-categoria" value="0" min="0">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom_ca">Nom en Català *</label>
                                    <input type="text" name="nom_ca" id="form-nom-ca" required placeholder="Tecnologia">
                                </div>
                                <div class="form-group">
                                    <label for="slug_ca">Slug en Català</label>
                                    <input type="text" name="slug_ca" id="form-slug-ca" placeholder="tecnologia">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcio_ca">Descripció en Català</label>
                                <textarea name="descripcio_ca" id="form-descripcio-ca" rows="3" placeholder="Descripció de la categoria..."></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom_es">Nom en Espanyol</label>
                                    <input type="text" name="nom_es" id="form-nom-es" placeholder="Tecnología">
                                </div>
                                <div class="form-group">
                                    <label for="slug_es">Slug en Espanyol</label>
                                    <input type="text" name="slug_es" id="form-slug-es" placeholder="tecnologia">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcio_es">Descripció en Espanyol</label>
                                <textarea name="descripcio_es" id="form-descripcio-es" rows="3" placeholder="Descripción de la categoría..."></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom_en">Nom en Anglès</label>
                                    <input type="text" name="nom_en" id="form-nom-en" placeholder="Technology">
                                </div>
                                <div class="form-group">
                                    <label for="slug_en">Slug en Anglès</label>
                                    <input type="text" name="slug_en" id="form-slug-en" placeholder="technology">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="descripcio_en">Descripció en Anglès</label>
                                    <textarea name="descripcio_en" id="form-descripcio-en" rows="3" placeholder="Category description..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="categoria_pare_id">Categoria Pare</label>
                                    <select name="categoria_pare_id" id="form-categoria-pare">
                                        <option value="">Categoria principal</option>
                                        <?php if (!empty($dades) && $tabActiva === 'categories'): ?>
                                            <?php foreach ($dades as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom'] ?? $cat['slug_base']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="tancarFormulariCategoria()">Cancel·lar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <span id="btn-text-categoria">Crear Categoria</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                            </div>

            <?php elseif ($tabActiva === 'idiomes'): ?>
                <div class="section-header">
                    <h2>Gestió d'Idiomes</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="mostrarFormulari('nou')">
                            <i class="fas fa-plus"></i> Afegir Idioma
                        </button>
                    </div>
                </div>

                <?php if ($missatge): ?>
                    <div class="alert alert-<?= $tipusMissatge ?>">
                        <?= htmlspecialchars($missatge) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulari Nou/Editar Idioma -->
                <div id="formulari-idioma" class="formulari-container" style="display: none;">
                    <div class="form-card">
                        <div class="form-header">
                            <h3 id="form-title">Nou Idioma</h3>
                            <button type="button" class="btn-close" onclick="tancarFormulari()">×</button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="accio" id="form-accio" value="crear">
                            <input type="hidden" name="id" id="form-id" value="">
                            <input type="hidden" name="codi_hidden" id="form-codi-hidden" value="">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="codi">Codi de l'idioma *</label>
                                    <input type="text" name="codi" id="form-codi" maxlength="5" required 
                                           pattern="[a-z]{2,5}" placeholder="ca, es, en...">
                                    <small>Codi ISO (2-5 caràcters, minúscules)</small>
                                </div>
                                <div class="form-group">
                                    <label for="ordre">Ordre</label>
                                    <input type="number" name="ordre" id="form-ordre" min="1" max="99" value="1">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom">Nom de l'idioma *</label>
                                    <input type="text" name="nom" id="form-nom" required placeholder="Català">
                                </div>
                                <div class="form-group">
                                    <label for="nom_natiu">Nom natiu *</label>
                                    <input type="text" name="nom_natiu" id="form-nom-natiu" required placeholder="Català">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="estat">Estat</label>
                                    <select name="estat" id="form-estat">
                                        <option value="actiu">Actiu</option>
                                        <option value="inactiu">Inactiu</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="bandera_url">Ruta de la bandera</label>
                                    <input type="text" name="bandera_url" id="form-bandera" placeholder="/img/flags/ca.png">
                                    <small>Ruta relativa (ex: /img/flags/ca.png) o URL completa (ex: https://...)</small>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="tancarFormulari()">Cancel·lar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <span id="btn-text">Crear Idioma</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="table-container">
                    <?php if (empty($dades)): ?>
                        <div class="empty-state">
                            <i class="fas fa-globe fa-3x"></i>
                            <h3>Cap idioma trobat</h3>
                            <p>No s'han trobat idiomes configurats al sistema.</p>
                            <button class="btn btn-primary" onclick="mostrarFormulari('nou')">
                                <i class="fas fa-plus"></i> Crear el primer idioma
                            </button>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Codi</th>
                                    <th>Nom</th>
                                    <th>Nom Natiu</th>
                                    <th>Estat</th>
                                    <th>Ordre</th>
                                    <th>Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dades as $idioma): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($idioma['codi']) ?></code></td>
                                        <td><?= htmlspecialchars($idioma['nom']) ?></td>
                                        <td><?= htmlspecialchars($idioma['nom_natiu']) ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="accio" value="canviar_estat">
                                                <input type="hidden" name="id" value="<?= $idioma['id'] ?>">
                                                <input type="hidden" name="nou_estat" value="<?= $idioma['estat'] === 'actiu' ? 'inactiu' : 'actiu' ?>">
                                                <button type="submit" class="badge badge-<?= $idioma['estat'] === 'actiu' ? 'success' : 'secondary' ?>" 
                                                        style="border: none; cursor: pointer;" 
                                                        title="Clic per canviar estat">
                                                    <?= ucfirst($idioma['estat']) ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?= $idioma['ordre'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editarIdioma(<?= htmlspecialchars(json_encode($idioma)) ?>)" 
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($idioma['codi'] !== 'ca'): ?>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Estàs segur que vols eliminar aquest idioma?')">
                                                    <input type="hidden" name="accio" value="eliminar">
                                                    <input type="hidden" name="id" value="<?= $idioma['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Idioma per defecte">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            <?php elseif ($tabActiva === 'tags'): ?>
                <div class="section-header">
                    <h2>Gestió de Tags</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="mostrarFormulariTag('nou')">
                            <i class="fas fa-plus"></i> Afegir Tag
                        </button>
                    </div>
                </div>

                <?php if ($missatge): ?>
                    <div class="alert alert-<?= $tipusMissatge ?>">
                        <?= htmlspecialchars($missatge) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulari Nou/Editar Tag -->
                <div id="formulari-tag" class="formulari-container" style="display: none;">
                    <div class="form-card">
                        <div class="form-header">
                            <h3 id="form-title-tag">Nou Tag</h3>
                            <button type="button" class="btn-close" onclick="tancarFormulariTag()">×</button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="accio" id="form-accio-tag" value="crear">
                            <input type="hidden" name="id" id="form-id-tag" value="">
                            
                            <div class="form-group">
                                <label for="slug_base">Slug Base *</label>
                                <input type="text" name="slug_base" id="form-slug-base-tag" required 
                                       placeholder="javascript">
                                <small>URL única per al tag (només lletres, números i guions)</small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom_ca">Nom en Català *</label>
                                    <input type="text" name="nom_ca" id="form-nom-ca-tag" required placeholder="JavaScript">
                                </div>
                                <div class="form-group">
                                    <label for="slug_ca">Slug en Català</label>
                                    <input type="text" name="slug_ca" id="form-slug-ca-tag" placeholder="javascript">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcio_ca">Descripció en Català</label>
                                <textarea name="descripcio_ca" id="form-descripcio-ca-tag" rows="3" placeholder="Descripció del tag..."></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom_es">Nom en Espanyol</label>
                                    <input type="text" name="nom_es" id="form-nom-es-tag" placeholder="JavaScript">
                                </div>
                                <div class="form-group">
                                    <label for="slug_es">Slug en Espanyol</label>
                                    <input type="text" name="slug_es" id="form-slug-es-tag" placeholder="javascript">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcio_es">Descripció en Espanyol</label>
                                <textarea name="descripcio_es" id="form-descripcio-es-tag" rows="3" placeholder="Descripción del tag..."></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom_en">Nom en Anglès</label>
                                    <input type="text" name="nom_en" id="form-nom-en-tag" placeholder="JavaScript">
                                </div>
                                <div class="form-group">
                                    <label for="slug_en">Slug en Anglès</label>
                                    <input type="text" name="slug_en" id="form-slug-en-tag" placeholder="javascript">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcio_en">Descripció en Anglès</label>
                                <textarea name="descripcio_en" id="form-descripcio-en-tag" rows="3" placeholder="Tag description..."></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="tancarFormulariTag()">Cancel·lar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <span id="btn-text-tag">Crear Tag</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-container">
                    <?php if (empty($dades)): ?>
                        <div class="empty-state">
                            <i class="fas fa-tags fa-3x"></i>
                            <h3>Cap tag trobat</h3>
                            <p>No s'han trobat tags configurats al sistema.</p>
                            <button class="btn btn-primary" onclick="mostrarFormulariTag('nou')">
                                <i class="fas fa-plus"></i> Crear el primer tag
                            </button>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Slug Base</th>
                                    <th>Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dades as $tag): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($tag['nom'] ?? 'Sense nom') ?></strong>
                                            <?php if (!empty($tag['descripcio'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($tag['descripcio'], 0, 80)) ?><?= strlen($tag['descripcio']) > 80 ? '...' : '' ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= htmlspecialchars($tag['slug_base']) ?></code></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editarTag(<?= htmlspecialchars(json_encode($tag)) ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Estàs segur que vols eliminar aquest tag? Aquesta acció no es pot desfer.')">
                                                <input type="hidden" name="accio" value="eliminar">
                                                <input type="hidden" name="id" value="<?= $tag['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="section-header">
                    <h2>Benvingut al Blog Admin</h2>
                    <p>Selecciona una pestanya per començar a gestionar el blog.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Formulari Nova/Editar Entrada -->
<div id="formulari-entrada" class="formulari-container" style="display: none;">
    <div class="form-card form-large">
        <div class="form-header">
            <h3 id="form-title-entrada">Nova Entrada</h3>
            <button type="button" class="btn-close" onclick="tancarFormulariEntrada()" title="Tancar">×</button>
        </div>
    <form method="POST">
            <input type="hidden" name="accio" id="form-accio-entrada" value="crear">
            <input type="hidden" name="id" id="form-id-entrada" value="">
            
            <!-- Configuració general -->
            <div class="form-section">
                <h4>Configuració General</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="form-idioma-original">Idioma Original</label>
                        <select id="form-idioma-original" name="idioma_original" required>
                            <option value="ca">Català</option>
                            <option value="es">Espanyol</option>
                            <option value="en">Anglès</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="form-estat">Estat</label>
                        <select id="form-estat" name="estat" required>
                            <option value="esborrany">Esborrany</option>
                            <option value="revisio">En revisió</option>
                            <option value="programat">Programat</option>
                            <option value="publicat">Publicat</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="form-format">Format</label>
                        <select id="form-format" name="format">
                            <option value="estandard">Estàndard</option>
                            <option value="galeria">Galeria</option>
                            <option value="video">Vídeo</option>
                            <option value="audio">Àudio</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="form-comentaris" name="comentaris_activats" value="1" checked>
                        <label for="form-comentaris">Permetre comentaris</label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="form-destacat" name="destacat" value="1">
                        <label for="form-destacat">Entrada destacada</label>
                    </div>
                </div>
            </div>

            <!-- Traduccions -->
            <div class="form-section">
                <h4>Contingut Multilingüe</h4>
                
                <!-- Tabs per idiomes -->
                <div class="lang-tabs">
                    <button type="button" class="lang-tab active" data-lang="ca">
                        <img src="../img/cat.png" alt="Català"> Català
                    </button>
                    <button type="button" class="lang-tab" data-lang="es">
                        <img src="../img/esp.png" alt="Espanyol"> Espanyol
                    </button>
                    <button type="button" class="lang-tab" data-lang="en">
                        <img src="../img/eng.png" alt="Anglès"> Anglès
                    </button>
                </div>

                <!-- Contingut Català -->
                <div class="lang-content active" data-lang="ca">
                    <div class="form-group">
                        <label for="form-titol-ca">Títol *</label>
                        <input type="text" id="form-titol-ca" name="titol_ca" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="form-resum-ca">Resum</label>
                        <textarea id="form-resum-ca" name="resum_ca" rows="3" 
                                placeholder="Resum breu de l'entrada..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="form-contingut-ca">Contingut *</label>
                        <textarea id="form-contingut-ca" name="contingut_ca" rows="12" required
                                placeholder="Escriu el contingut de l'entrada aquí..."></textarea>
                    </div>
                </div>

                <!-- Contingut Espanyol -->
                <div class="lang-content" data-lang="es">
                    <div class="form-group">
                        <label for="form-titol-es">Título</label>
                        <input type="text" id="form-titol-es" name="titol_es">
                    </div>
                    
                    <div class="form-group">
                        <label for="form-resum-es">Resumen</label>
                        <textarea id="form-resum-es" name="resum_es" rows="3" 
                                placeholder="Resumen breve de la entrada..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="form-contingut-es">Contenido</label>
                        <textarea id="form-contingut-es" name="contingut_es" rows="12"
                                placeholder="Escribe el contenido de la entrada aquí..."></textarea>
                    </div>
                </div>

                <!-- Contingut Anglès -->
                <div class="lang-content" data-lang="en">
                    <div class="form-group">
                        <label for="form-titol-en">Title</label>
                        <input type="text" id="form-titol-en" name="titol_en">
                    </div>
                    
                    <div class="form-group">
                        <label for="form-resum-en">Summary</label>
                        <textarea id="form-resum-en" name="resum_en" rows="3" 
                                placeholder="Brief summary of the entry..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="form-contingut-en">Content</label>
                        <textarea id="form-contingut-en" name="contingut_en" rows="12"
                                placeholder="Write the entry content here..."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="tancarFormulariEntrada()">Cancel·lar</button>
                <button type="submit" class="btn btn-primary" id="btn-text-entrada">Crear Entrada</button>
            </div>
        </form>
    </div>
</div>

<!-- JS Blog Admin -->
<script src="js/blog-admin.js"></script>

<?php require_once 'includes/footer.php'; ?>
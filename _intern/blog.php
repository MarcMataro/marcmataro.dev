<?php
/**
 * Panell d'Administració del Blog - Versió Simplificada PHP
 */

require_once 'includes/auth.php';
require_once '../_classes/connexio.php';
require_once '../_classes/blog.php';

// Inicialitzar sistema de blog
try {
    $db = Connexio::getInstance()->getConnexio();
    $blog = new Blog($db);
} catch (Exception $e) {
    die("Error de connexió: " . $e->getMessage());
}

// Gestionar pestanya activa
$tabActiva = isset($_GET['tab']) ? $_GET['tab'] : 'entrades';

// Gestionar accions
$missatge = null;
$tipusMissatge = null;

// Funció per obtenir la llista d'imatges de la carpeta notícies
function getImagesFromDirectory($directory) {
    $images = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $files = glob($directory . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
    // Ordenar per data de modificació, més recents primer
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    foreach ($files as $file) {
        $images[] = basename($file); // Obtenir només el nom de l'arxiu
    }
    return $images;
}

$images = getImagesFromDirectory("../img/blog/");

// Gestionar accions per entrades
if ($tabActiva === 'entrades' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accio = $_POST['accio'] ?? '';
        
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
                
                if ($resultat['success']) {
                    $missatge = "Entrada creada correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
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
                
                if ($resultat['success']) {
                    $missatge = "Entrada actualitzada correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'eliminar':
                $id = (int)$_POST['id'];
                $resultat = $blog->entrades->eliminar($id);
                
                if ($resultat['success']) {
                    $missatge = "Entrada eliminada correctament";
                    $tipusMissatge = "success";
                } else {
                    $missatge = implode(', ', $resultat['errors']);
                    $tipusMissatge = "danger";
                }
                break;
                
            case 'canviar_estat':
                $id = (int)$_POST['id'];
                $nouEstat = $_POST['nou_estat'];
                $resultat = $blog->entrades->canviarEstat($id, $nouEstat);
                
                if ($resultat['success']) {
                    $missatge = "Estat de l'entrada canviat correctament";
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
<script>
// Gestió de formularis d'idiomes
function mostrarFormulari(tipus) {
    const formulari = document.getElementById('formulari-idioma');
    const title = document.getElementById('form-title');
    const accio = document.getElementById('form-accio');
    const btnText = document.getElementById('btn-text');
    
    // Reset form
    document.querySelector('#formulari-idioma form').reset();
    document.getElementById('form-id').value = '';
    document.getElementById('form-codi-hidden').value = '';
    
    if (tipus === 'nou') {
        title.textContent = 'Nou Idioma';
        accio.value = 'crear';
        btnText.textContent = 'Crear Idioma';
        document.getElementById('form-codi').disabled = false;
    }
    
    formulari.style.display = 'block';
    document.getElementById('form-codi').focus();
}

function editarIdioma(idioma) {
    const formulari = document.getElementById('formulari-idioma');
    const title = document.getElementById('form-title');
    const accio = document.getElementById('form-accio');
    const btnText = document.getElementById('btn-text');
    
    // Omplir formulari amb dades de l'idioma
    document.getElementById('form-id').value = idioma.id;
    document.getElementById('form-codi').value = idioma.codi;
    document.getElementById('form-codi-hidden').value = idioma.codi;
    document.getElementById('form-nom').value = idioma.nom;
    document.getElementById('form-nom-natiu').value = idioma.nom_natiu;
    document.getElementById('form-estat').value = idioma.estat;
    document.getElementById('form-ordre').value = idioma.ordre;
    document.getElementById('form-bandera').value = idioma.bandera_url || '';
    
    // Configurar formulari per edició
    title.textContent = 'Editar Idioma';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Idioma';
    
    // Deshabilitar el codi si és l'idioma per defecte
    document.getElementById('form-codi').disabled = (idioma.codi === 'ca');
    
    formulari.style.display = 'block';
    document.getElementById('form-nom').focus();
}

function tancarFormulari() {
    document.getElementById('formulari-idioma').style.display = 'none';
}

// Tancar formulari amb Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tancarFormulari();
    }
});

// Auto-ocultació d'alertes
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Gestió de formularis de categories
function mostrarFormulariCategoria(tipus) {
    const formulari = document.getElementById('formulari-categoria');
    const title = document.getElementById('form-title-categoria');
    const accio = document.getElementById('form-accio-categoria');
    const btnText = document.getElementById('btn-text-categoria');
    
    // Reset formulari
    document.querySelector('#formulari-categoria form').reset();
    document.getElementById('form-id-categoria').value = '';
    
    if (tipus === 'nou') {
        title.textContent = 'Nova Categoria';
        accio.value = 'crear';
        btnText.textContent = 'Crear Categoria';
    }
    
    formulari.style.display = 'block';
    document.getElementById('form-slug-base').focus();
}

function editarCategoria(categoria) {
    const formulari = document.getElementById('formulari-categoria');
    const title = document.getElementById('form-title-categoria');
    const accio = document.getElementById('form-accio-categoria');
    const btnText = document.getElementById('btn-text-categoria');
    
    // Omplir formulari amb dades existents
    document.getElementById('form-id-categoria').value = categoria.id;
    document.getElementById('form-slug-base').value = categoria.slug_base;
    document.getElementById('form-nom-ca').value = categoria.traduccions?.ca?.nom || categoria.nom || '';
    document.getElementById('form-slug-ca').value = categoria.traduccions?.ca?.slug || '';
    document.getElementById('form-descripcio-ca').value = categoria.traduccions?.ca?.descripcio || '';
    document.getElementById('form-nom-es').value = categoria.traduccions?.es?.nom || '';
    document.getElementById('form-slug-es').value = categoria.traduccions?.es?.slug || '';
    document.getElementById('form-descripcio-es').value = categoria.traduccions?.es?.descripcio || '';
    document.getElementById('form-nom-en').value = categoria.traduccions?.en?.nom || '';
    document.getElementById('form-slug-en').value = categoria.traduccions?.en?.slug || '';
    document.getElementById('form-descripcio-en').value = categoria.traduccions?.en?.descripcio || '';
    document.getElementById('form-ordre-categoria').value = categoria.ordre || 0;
    
    // Si hi ha categoria pare
    if (categoria.categoria_pare_id) {
        document.getElementById('form-categoria-pare').value = categoria.categoria_pare_id;
    }
    
    title.textContent = 'Editar Categoria';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Categoria';
    
    formulari.style.display = 'block';
}

function tancarFormulariCategoria() {
    document.getElementById('formulari-categoria').style.display = 'none';
}

// Gestió de formularis de tags
function mostrarFormulariTag(tipus) {
    const formulari = document.getElementById('formulari-tag');
    const title = document.getElementById('form-title-tag');
    const accio = document.getElementById('form-accio-tag');
    const btnText = document.getElementById('btn-text-tag');
    
    // Reset formulari
    document.querySelector('#formulari-tag form').reset();
    document.getElementById('form-id-tag').value = '';
    
    if (tipus === 'nou') {
        title.textContent = 'Nou Tag';
        accio.value = 'crear';
        btnText.textContent = 'Crear Tag';
    }
    
    formulari.style.display = 'block';
    document.getElementById('form-slug-base-tag').focus();
}

function editarTag(tag) {
    const formulari = document.getElementById('formulari-tag');
    const title = document.getElementById('form-title-tag');
    const accio = document.getElementById('form-accio-tag');
    const btnText = document.getElementById('btn-text-tag');
    
    // Omplir formulari amb dades existents
    document.getElementById('form-id-tag').value = tag.id;
    document.getElementById('form-slug-base-tag').value = tag.slug_base;
    document.getElementById('form-nom-ca-tag').value = tag.traduccions?.ca?.nom || tag.nom || '';
    document.getElementById('form-slug-ca-tag').value = tag.traduccions?.ca?.slug || '';
    document.getElementById('form-descripcio-ca-tag').value = tag.traduccions?.ca?.descripcio || '';
    document.getElementById('form-nom-es-tag').value = tag.traduccions?.es?.nom || '';
    document.getElementById('form-slug-es-tag').value = tag.traduccions?.es?.slug || '';
    document.getElementById('form-descripcio-es-tag').value = tag.traduccions?.es?.descripcio || '';
    document.getElementById('form-nom-en-tag').value = tag.traduccions?.en?.nom || '';
    document.getElementById('form-slug-en-tag').value = tag.traduccions?.en?.slug || '';
    document.getElementById('form-descripcio-en-tag').value = tag.traduccions?.en?.descripcio || '';
    
    title.textContent = 'Editar Tag';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Tag';
    
    formulari.style.display = 'block';
}

// Gestió de formularis de tags
function mostrarFormulariTag(tipus) {
    const formulari = document.getElementById('formulari-tag');
    const title = document.getElementById('form-title-tag');
    const accio = document.getElementById('form-accio-tag');
    const btnText = document.getElementById('btn-text-tag');
    
    // Reset formulari
    document.querySelector('#formulari-tag form').reset();
    document.getElementById('form-id-tag').value = '';
    
    if (tipus === 'nou') {
        title.textContent = 'Nou Tag';
        accio.value = 'crear';
        btnText.textContent = 'Crear Tag';
    }
    
    formulari.style.display = 'block';
    document.getElementById('form-slug-base-tag').focus();
}

function editarTag(tag) {
    const formulari = document.getElementById('formulari-tag');
    const title = document.getElementById('form-title-tag');
    const accio = document.getElementById('form-accio-tag');
    const btnText = document.getElementById('btn-text-tag');
    
    // Omplir formulari amb dades existents
    document.getElementById('form-id-tag').value = tag.id;
    document.getElementById('form-slug-base-tag').value = tag.slug_base;
    
    // Omplir traduccions
    if (tag.traduccions) {
        // Català
        if (tag.traduccions.ca) {
            document.getElementById('form-nom-ca-tag').value = tag.traduccions.ca.nom || '';
            document.getElementById('form-slug-ca-tag').value = tag.traduccions.ca.slug || '';
            document.getElementById('form-descripcio-ca-tag').value = tag.traduccions.ca.descripcio || '';
        }
        // Espanyol
        if (tag.traduccions.es) {
            document.getElementById('form-nom-es-tag').value = tag.traduccions.es.nom || '';
            document.getElementById('form-slug-es-tag').value = tag.traduccions.es.slug || '';
            document.getElementById('form-descripcio-es-tag').value = tag.traduccions.es.descripcio || '';
        }
        // Anglès
        if (tag.traduccions.en) {
            document.getElementById('form-nom-en-tag').value = tag.traduccions.en.nom || '';
            document.getElementById('form-slug-en-tag').value = tag.traduccions.en.slug || '';
            document.getElementById('form-descripcio-en-tag').value = tag.traduccions.en.descripcio || '';
        }
    }
    
    title.textContent = 'Editar Tag';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Tag';
    
    formulari.style.display = 'block';
}

function tancarFormulariTag() {
    document.getElementById('formulari-tag').style.display = 'none';
}

// Gestió de formularis d'entrades
function mostrarFormulariEntrada(tipus) {
    const formulari = document.getElementById('formulari-entrada');
    const title = document.getElementById('form-title-entrada');
    const accio = document.getElementById('form-accio-entrada');
    const btnText = document.getElementById('btn-text-entrada');
    
    // Reset formulari
    document.querySelector('#formulari-entrada form').reset();
    document.getElementById('form-id-entrada').value = '';
    
    // Activar primera pestanya
    document.querySelectorAll('.lang-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.lang-content').forEach(content => content.classList.remove('active'));
    document.querySelector('.lang-tab[data-lang="ca"]').classList.add('active');
    document.querySelector('.lang-content[data-lang="ca"]').classList.add('active');
    
    if (tipus === 'nou') {
        title.textContent = 'Nova Entrada';
        accio.value = 'crear';
        btnText.textContent = 'Crear Entrada';
    }
    
    formulari.style.display = 'block';
    document.getElementById('form-titol-ca').focus();
}

function editarEntrada(entrada) {
    const formulari = document.getElementById('formulari-entrada');
    const title = document.getElementById('form-title-entrada');
    const accio = document.getElementById('form-accio-entrada');
    const btnText = document.getElementById('btn-text-entrada');
    
    // Omplir dades generals
    document.getElementById('form-id-entrada').value = entrada.id;
    document.getElementById('form-idioma-original').value = entrada.idioma_original || 'ca';
    document.getElementById('form-estat').value = entrada.estat;
    document.getElementById('form-format').value = entrada.format;
    document.getElementById('form-comentaris').checked = entrada.comentaris_activats == 1;
    document.getElementById('form-destacat').checked = entrada.destacat == 1;
    
    // Omplir traduccions
    if (entrada.traduccions) {
        if (entrada.traduccions.ca) {
            document.getElementById('form-titol-ca').value = entrada.traduccions.ca.titol || '';
            document.getElementById('form-resum-ca').value = entrada.traduccions.ca.resum || '';
            document.getElementById('form-contingut-ca').value = entrada.traduccions.ca.contingut || '';
        }
        if (entrada.traduccions.es) {
            document.getElementById('form-titol-es').value = entrada.traduccions.es.titol || '';
            document.getElementById('form-resum-es').value = entrada.traduccions.es.resum || '';
            document.getElementById('form-contingut-es').value = entrada.traduccions.es.contingut || '';
        }
        if (entrada.traduccions.en) {
            document.getElementById('form-titol-en').value = entrada.traduccions.en.titol || '';
            document.getElementById('form-resum-en').value = entrada.traduccions.en.resum || '';
            document.getElementById('form-contingut-en').value = entrada.traduccions.en.contingut || '';
        }
    }
    
    title.textContent = 'Editar Entrada';
    accio.value = 'editar';
    btnText.textContent = 'Actualitzar Entrada';
    
    formulari.style.display = 'block';
}

function tancarFormulariEntrada() {
    // Netejar textareas
    ['form-contingut-ca', 'form-contingut-es', 'form-contingut-en'].forEach(id => {
        const textarea = document.getElementById(id);
        if (textarea) textarea.value = '';
    });
    // Netejar altres camps
    const inputs = document.querySelectorAll('#formulari-entrada input, #formulari-entrada textarea');
    inputs.forEach(input => {
        if (input.type !== 'hidden') {
            input.value = '';
            if (input.type === 'checkbox') input.checked = false;
        }
    });
    // Tancar el formulari
    const formulari = document.getElementById('formulari-entrada');
    if (formulari) {
        formulari.style.display = 'none';
    }
}



function canviarEstatEntrada(id, nouEstat) {
    if (confirm(`Segur que vols canviar l'estat de l'entrada a "${nouEstat}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accio" value="canviar_estat">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="nou_estat" value="${nouEstat}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function eliminarEntrada(id, titol) {
    if (confirm(`Segur que vols eliminar l'entrada "${titol}"?\n\nAquesta acció no es pot desfer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accio" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Gestió de pestanyes d'idiomes
document.addEventListener('DOMContentLoaded', function() {
    // CKEditor no necessita bloqueig de calls externes (completament offline)
    
    // Afegir event listeners adicionals per botons de tancar
    const btnClose = document.querySelector('#formulari-entrada .btn-close');
    const btnCancel = document.querySelector('#formulari-entrada .btn-secondary');
    
    if (btnClose) {
        btnClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            tancarFormulariEntrada();
        });
    }
    
    if (btnCancel) {
        btnCancel.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            tancarFormulariEntrada();
        });
    }
    
    // Tancar modal fent clic fora
    const modalContainer = document.getElementById('formulari-entrada');
    if (modalContainer) {
        modalContainer.addEventListener('click', function(e) {
            if (e.target === modalContainer) {
                tancarFormulariEntrada();
            }
        });
    }
    
    // Tancar modal amb tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const formulari = document.getElementById('formulari-entrada');
            if (formulari && formulari.style.display !== 'none') {
                tancarFormulariEntrada();
            }
        }
    });
    
    const langTabs = document.querySelectorAll('.lang-tab');
    const langContents = document.querySelectorAll('.lang-content');
    
    langTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.dataset.lang;
            
            // Activar pestanya
            langTabs.forEach(t => t.classList.remove('active'));
            langContents.forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            document.querySelector(`.lang-content[data-lang="${lang}"]`).classList.add('active');
        });
    });
    // Carrega TinyMCE des del CDN si no està carregat
    if (typeof tinymce === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.tiny.cloud/1/ds0tgp458zh4vbyxcyhq2bgbf9wnk8sj1k8874ohwvqpmn39/tinymce/5/tinymce.min.js';
        script.referrerPolicy = 'origin';
        script.onload = function() {
            inicialitzarTiny();
        };
        document.head.appendChild(script);
    } else {
        inicialitzarTiny();
    }

    function inicialitzarTiny() {
        // Assegura que els textareas no estan deshabilitats abans d'inicialitzar TinyMCE
        document.querySelectorAll('textarea[name="contingut_ca"], textarea[name="contingut_es"], textarea[name="contingut_en"]').forEach(function(textarea) {
            textarea.removeAttribute('disabled');
            textarea.removeAttribute('readonly');
        });

        tinymce.init({
            selector: 'textarea[name="contingut_ca"], textarea[name="contingut_es"], textarea[name="contingut_en"]',
            height: 400,
            menubar: true,
            statusbar: false,
            skin: 'oxide',
            content_css: false,
            content_style: `
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; margin: 10px; }
                img { max-width: 100%; height: auto; border-radius: 4px; }
                blockquote { border-left: 4px solid #3498db; padding-left: 1rem; margin: 1rem 0; background: #f8f9fa; padding: 1rem; border-radius: 4px; }
            `,
            convert_urls: true,
            relative_urls: false,
            remove_script_host: false,
            document_base_url: '<?php echo "http://" . $_SERVER['HTTP_HOST']; ?>',
            branding: false,
            promotion: false,
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
            image_class_list: [
                {title: 'Responsive', value: 'img-fluid'}
            ],
            image_list: [
                <?php foreach ($images as $image): ?>
                {title: '<?php echo htmlspecialchars(basename($image)); ?>', value: '/marcmataro.dev/img/blog/<?php echo htmlspecialchars($image); ?>'},
                <?php endforeach; ?>
            ],
            paste_as_text: false,
            paste_auto_cleanup_on_paste: true,
            paste_remove_styles: false,
            paste_remove_styles_if_webkit: false,
            table_toolbar: "tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol",
            forced_root_block: false,
            readonly: false,
            setup: function(editor) {
                editor.on('init', function() {
                    // Força l'editor a estar actiu
                    editor.setMode('design');
                    const textareaName = editor.getElement().name;
                    if (textareaName.includes('_es')) {
                        editor.getDoc().documentElement.lang = 'es';
                    } else if (textareaName.includes('_en')) {
                        editor.getDoc().documentElement.lang = 'en';
                    } else {
                        editor.getDoc().documentElement.lang = 'ca';
                    }
                });
            }
        });

        // Workaround: després d'inicialitzar, elimina disabled/readonly si TinyMCE els ha posat
        setTimeout(function() {
            document.querySelectorAll('textarea[name="contingut_ca"], textarea[name="contingut_es"], textarea[name="contingut_en"]').forEach(function(textarea) {
                textarea.removeAttribute('disabled');
                textarea.removeAttribute('readonly');
            });
            tinymce.editors.forEach(function(editor) {
                if (editor.mode !== 'design') editor.setMode('design');
            });
        }, 500);
    }
    // ...
});
</script>

<?php require_once 'includes/footer.php'; ?>
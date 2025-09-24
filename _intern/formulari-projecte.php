<?php
/**
 * Formulari de Creació/Edició de Projectes
 * 
 * Interfície administrativa per gestionar projectes amb suport multi-idioma.
 * Permet crear nous projectes i editar els existents amb validació completa.
 */

session_start();
require_once '../_classes/connexio.php';
require_once '../_classes/projectes.php';
require_once 'includes/auth.php';
require_once 'includes/page-header.php';

// Verificar autenticació
verificarAuth();

// Inicialitzar variables
$esEdicio = false;
$projecteId = null;
$projecte = [];
$errors = [];
$missatge = '';

// Crear instància del gestor de projectes
try {
    $db = Connexio::getInstance();
    $gestorProjectes = new Projectes($db->getConnexio());
} catch (Exception $e) {
    die("Error de connexió: " . $e->getMessage());
}

// Detectar si és edició
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $esEdicio = true;
    $projecteId = (int)$_GET['id'];
    
    // Obtenir dades del projecte per editar (sense traducció per obtenir tots els idiomes)
    try {
        $sql = "SELECT * FROM projectes WHERE id = :id";
        $stmt = $db->getConnexio()->prepare($sql);
        $stmt->bindParam(':id', $projecteId);
        $stmt->execute();
        $projecte = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$projecte) {
            header('Location: projectes.php?error=projecte_no_trobat');
            exit;
        }
    } catch (Exception $e) {
        header('Location: projectes.php?error=db_error');
        exit;
    }
}

// Processar formulari
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DEBUG: Mostrar què arriba en POST (temporal)
    // error_log("POST data: " . print_r($_POST, true));
    
    // Validar dades requerides
    $errors = [];
    if (empty($_POST['nom_ca'])) $errors[] = "El nom en català és obligatori";
    if (empty($_POST['slug_ca'])) $errors[] = "El slug en català és obligatori";
    if (empty($_POST['descripcio_curta_ca'])) $errors[] = "La descripció curta en català és obligatòria";
    
    // Validar slug únic
    if (!$errors) {
        try {
            $sql = "SELECT id FROM projectes WHERE slug_ca = :slug" . ($esEdicio ? " AND id != :id" : "");
            $stmt = $db->getConnexio()->prepare($sql);
            $stmt->bindParam(':slug', $_POST['slug_ca']);
            if ($esEdicio) {
                $stmt->bindParam(':id', $projecteId);
            }
            $stmt->execute();
            if ($stmt->fetch()) {
                $errors[] = "Aquest slug ja existeix";
            }
        } catch (Exception $e) {
            $errors[] = "Error validant slug: " . $e->getMessage();
        }
    }
    
    if ($errors) {
        $missatge = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' . implode('<br>', $errors) . '</div>';
    } else {
        // Processar dades del formulari
        $dades = [
            'nom_ca' => trim($_POST['nom_ca']),
            'nom_es' => !empty($_POST['nom_es']) ? trim($_POST['nom_es']) : null,
            'nom_en' => !empty($_POST['nom_en']) ? trim($_POST['nom_en']) : null,
            'slug_ca' => trim($_POST['slug_ca']),
            'slug_es' => !empty($_POST['slug_es']) ? trim($_POST['slug_es']) : null,
            'slug_en' => !empty($_POST['slug_en']) ? trim($_POST['slug_en']) : null,
            'descripcio_curta_ca' => trim($_POST['descripcio_curta_ca']),
            'descripcio_curta_es' => !empty($_POST['descripcio_curta_es']) ? trim($_POST['descripcio_curta_es']) : null,
            'descripcio_curta_en' => !empty($_POST['descripcio_curta_en']) ? trim($_POST['descripcio_curta_en']) : null,
            'descripcio_detallada_ca' => !empty($_POST['descripcio_detallada_ca']) ? trim($_POST['descripcio_detallada_ca']) : null,
            'descripcio_detallada_es' => !empty($_POST['descripcio_detallada_es']) ? trim($_POST['descripcio_detallada_es']) : null,
            'descripcio_detallada_en' => !empty($_POST['descripcio_detallada_en']) ? trim($_POST['descripcio_detallada_en']) : null,
            'estat' => $_POST['estat'] ?? 'desenvolupament',
            'visible' => isset($_POST['visible']) ? 1 : 0,
            'url_demo' => !empty($_POST['url_demo']) ? trim($_POST['url_demo']) : null,
            'url_github' => !empty($_POST['url_github']) ? trim($_POST['url_github']) : null,
            'url_documentacio' => !empty($_POST['url_documentacio']) ? trim($_POST['url_documentacio']) : null,
            'imatge_portada' => !empty($_POST['imatge_portada']) ? trim($_POST['imatge_portada']) : null,
            'data_publicacio' => !empty($_POST['data_publicacio']) ? $_POST['data_publicacio'] : null
        ];
        
        try {
            if ($esEdicio) {
                $resultat = $gestorProjectes->actualitzar($projecteId, $dades);
                if ($resultat['success']) {
                    $missatge = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Projecte actualitzat correctament</div>';
                    // Recarregar dades actualitzades
                    $sql = "SELECT * FROM projectes WHERE id = :id";
                    $stmt = $db->getConnexio()->prepare($sql);
                    $stmt->bindParam(':id', $projecteId);
                    $stmt->execute();
                    $projecte = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $missatge = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error actualitzant: ' . implode(', ', $resultat['errors']) . '</div>';
                }
            } else {
                $resultat = $gestorProjectes->crear($dades);
                if ($resultat['success']) {
                    header('Location: formulari-projecte.php?id=' . $resultat['id'] . '&success=creat');
                    exit;
                } else {
                    $missatge = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error creant: ' . implode(', ', $resultat['errors']) . '</div>';
                }
            }
        } catch (Exception $e) {
            $missatge = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error de base de dades: ' . $e->getMessage() . '</div>';
        }
    }
}

// Definir pestanyes d'idiomes
$idiomes = [
    'ca' => ['nom' => 'Català', 'flag' => '🇪🇸'],
    'es' => ['nom' => 'Castellà', 'flag' => '🇪🇸'], 
    'en' => ['nom' => 'Anglès', 'flag' => '🇬🇧']
];

// Gestionar missatges d'èxit per URL
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'creat') {
        $missatge = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Projecte creat correctament</div>';
    } elseif ($_GET['success'] === '1') {
        $missatge = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Operació completada correctament</div>';
    }
}

$titolPagina = $esEdicio ? 'Editar Projecte' : 'Nou Projecte';
$current_page = 'projectes'; // Per a la sidebar
require_once 'includes/header.php';
require_once 'includes/sidebar.php'; ?>

<style>
/* Millorar visibilitat dels inputs */
.project-form .form-control {
    background: white !important;
    border: 2px solid #e2e8f0 !important;
    color: #1a202c !important;
    font-size: 14px !important;
    padding: 12px !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
}

.project-form .form-control:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    outline: none !important;
}

.project-form select.form-control {
    cursor: pointer !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px 12px !important;
    padding-right: 40px !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
}

/* Alertes personalitzades */
.alert {
    padding: 16px 20px !important;
    border-radius: 8px !important;
    margin-bottom: 20px !important;
    border: 1px solid transparent !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.alert-success {
    background-color: #d1fae5 !important;
    border-color: #10b981 !important;
    color: #047857 !important;
}

.alert-danger {
    background-color: #fee2e2 !important;
    border-color: #ef4444 !important;
    color: #dc2626 !important;
}

.alert i {
    font-size: 16px !important;
    flex-shrink: 0 !important;
}

.project-form .form-control::placeholder {
    color: #9ca3af !important;
}

.project-form label {
    color: #374151 !important;
    font-weight: 500 !important;
    margin-bottom: 6px !important;
    display: block !important;
}

.project-form select.form-control {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
    background-position: right 8px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px 12px !important;
    padding-right: 32px !important;
}
</style>

<?php 
generarPageHeader(
    $titolPagina, 
    $esEdicio ? 'Modificar les dades del projecte existent' : 'Crear un nou projecte per al portfolio'
); 
?>
    
    <div class="content-wrapper">
        <!-- Missatges d'estat -->
        <?php if ($missatge): ?>
            <?= $missatge ?>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Hi ha errors al formulari:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulari principal -->
        <form method="POST" class="project-form" id="projectForm" action="">
            
            <!-- Barra d'accions superior -->
            <div class="form-actions-top">
                <div class="form-actions-left">
                    <a href="projectes.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Tornar als projectes
                    </a>
                </div>
                
                <div class="form-actions-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?= $esEdicio ? 'Actualitzar' : 'Crear projecte' ?>
                    </button>
                </div>
            </div>

            <!-- Configuració general -->
            <div class="form-section">
                <h3><i class="fas fa-cog"></i> Configuració General</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="visible">Visibilitat pública</label>
                        <select name="visible" id="visible" class="form-control">
                            <option value="0" <?= (!$esEdicio || $projecte['visible'] == 0) ? 'selected' : '' ?>>Ocult</option>
                            <option value="1" <?= ($esEdicio && $projecte['visible'] == 1) ? 'selected' : '' ?>>Visible</option>
                        </select>
                        <small>Determina si el projecte es mostra al frontend públic</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="data_publicacio">Data de publicació</label>
                        <input type="datetime-local" 
                               name="data_publicacio" 
                               id="data_publicacio" 
                               class="form-control"
                               value="<?= $esEdicio && $projecte['data_publicacio'] ? date('Y-m-d\TH:i', strtotime($projecte['data_publicacio'])) : '' ?>">
                        <small>Deixar buit per utilitzar la data actual</small>
                    </div>
                </div>
            </div>

            <!-- Pestanyes d'idiomes -->
            <div class="form-section">
                <h3><i class="fas fa-language"></i> Contingut Multi-idioma</h3>
                
                <div class="language-tabs">
                    <?php foreach ($idiomes as $codi => $info): ?>
                        <button type="button" 
                                class="language-tab <?= $codi === 'ca' ? 'active' : '' ?>" 
                                data-lang="<?= $codi ?>">
                            <span class="flag"><?= $info['flag'] ?></span>
                            <?= $info['nom'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Contingut de cada idioma -->
                <?php foreach ($idiomes as $codi => $info): ?>
                    <div class="language-content <?= $codi === 'ca' ? 'active' : '' ?>" id="lang-<?= $codi ?>">
                        
                        <!-- Informació bàsica -->
                        <div class="form-subsection">
                            <h4>Informació Bàsica</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nom_<?= $codi ?>">Nom del projecte *</label>
                                    <input type="text" 
                                           name="nom_<?= $codi ?>" 
                                           id="nom_<?= $codi ?>" 
                                           class="form-control" 
                                           required
                                           value="<?= $esEdicio ? htmlspecialchars($projecte['nom_' . $codi] ?? '') : '' ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="slug_<?= $codi ?>">URL amigable (slug)</label>
                                    <input type="text" 
                                           name="slug_<?= $codi ?>" 
                                           id="slug_<?= $codi ?>" 
                                           class="form-control" 
                                           placeholder="url-amigable-del-projecte"
                                           value="<?= $esEdicio ? htmlspecialchars($projecte['slug_' . $codi] ?? '') : '' ?>">
                                    <small>Es generarà automàticament si es deixa buit</small>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="descripcio_curta_<?= $codi ?>">Descripció curta *</label>
                                    <textarea name="descripcio_curta_<?= $codi ?>" 
                                              id="descripcio_curta_<?= $codi ?>" 
                                              class="form-control" 
                                              rows="3" 
                                              required><?= $esEdicio ? htmlspecialchars($projecte['descripcio_curta_' . $codi] ?? '') : '' ?></textarea>
                                    <small>Descripció breu que apareix als llistats</small>
                                </div>
                            </div>
                        </div>

                        <!-- Descripció completa -->
                        <div class="form-subsection">
                            <h4>Descripció Detallada</h4>
                            <div class="form-group">
                                <label for="descripcio_detallada_<?= $codi ?>">Descripció detallada</label>
                                <textarea name="descripcio_detallada_<?= $codi ?>" 
                                          id="descripcio_detallada_<?= $codi ?>" 
                                          class="form-control wysiwyg" 
                                          rows="6"><?= $esEdicio ? htmlspecialchars($projecte['descripcio_detallada_' . $codi] ?? '') : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Enllaços i recursos -->
            <div class="form-section">
                <h3><i class="fas fa-link"></i> Enllaços i Recursos</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="url_demo">URL de demostració</label>
                        <input type="url" 
                               name="url_demo" 
                               id="url_demo" 
                               class="form-control"
                               value="<?= $esEdicio ? htmlspecialchars($projecte['url_demo'] ?? '') : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="url_github">Repository GitHub</label>
                        <input type="url" 
                               name="url_github" 
                               id="url_github" 
                               class="form-control"
                               value="<?= $esEdicio ? htmlspecialchars($projecte['url_github'] ?? '') : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="url_documentacio">Documentació</label>
                        <input type="url" 
                               name="url_documentacio" 
                               id="url_documentacio" 
                               class="form-control"
                               value="<?= $esEdicio ? htmlspecialchars($projecte['url_documentacio'] ?? '') : '' ?>">
                    </div>
                </div>
            </div>

            <!-- Imatges i media -->
            <div class="form-section">
                <h3><i class="fas fa-images"></i> Imatges i Media</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="imatge_portada">Imatge de portada</label>
                        <input type="text" 
                               name="imatge_portada" 
                               id="imatge_portada" 
                               class="form-control"
                               value="<?= $esEdicio ? htmlspecialchars($projecte['imatge_portada'] ?? '') : '' ?>">
                        <small>Ruta relativa o URL completa de la imatge principal</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="imatge_detall">Imatge de detall</label>
                        <input type="text" 
                               name="imatge_detall" 
                               id="imatge_detall" 
                               class="form-control"
                               value="<?= $esEdicio ? htmlspecialchars($projecte['imatge_detall'] ?? '') : '' ?>">
                        <small>Imatge per a la vista detallada del projecte</small>
                    </div>
                </div>
            </div>

            <!-- Informació tècnica -->
            <div class="form-section">
                <h3><i class="fas fa-code"></i> Informació Tècnica</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="tecnologies_principals">Tecnologies Principals</label>
                        <textarea name="tecnologies_principals" 
                                  id="tecnologies_principals" 
                                  class="form-control" 
                                  rows="3"><?= $esEdicio ? htmlspecialchars($projecte['tecnologies_principals'] ?? '') : '' ?></textarea>
                        <small>Stack tecnològic utilitzat (separar amb comes)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="caracteristiques">Característiques Tècniques</label>
                        <textarea name="caracteristiques" 
                                  id="caracteristiques" 
                                  class="form-control" 
                                  rows="3"><?= $esEdicio ? htmlspecialchars($projecte['caracteristiques'] ?? '') : '' ?></textarea>
                        <small>Detalls tècnics, arquitectura, patrons utilitzats...</small>
                    </div>
                </div>
            </div>

            <!-- Configuració de publicació -->
            <div class="form-section">
                <h3><i class="fas fa-cog"></i> Configuració de Publicació</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="estat">Estat del projecte</label>
                        <select name="estat" id="estat" class="form-control" required>
                            <option value="desenvolupament" <?= (!$esEdicio || ($projecte['estat'] ?? 'desenvolupament') === 'desenvolupament') ? 'selected' : '' ?>>En desenvolupament</option>
                            <option value="actiu" <?= $esEdicio && ($projecte['estat'] ?? '') === 'actiu' ? 'selected' : '' ?>>Actiu</option>
                            <option value="aturat" <?= $esEdicio && ($projecte['estat'] ?? '') === 'aturat' ? 'selected' : '' ?>>Aturat</option>
                            <option value="archivat" <?= $esEdicio && ($projecte['estat'] ?? '') === 'archivat' ? 'selected' : '' ?>>Archivat</option>
                        </select>
                        <small>Selecciona l'estat actual del projecte</small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" 
                                   name="visible" 
                                   value="1"
                                   <?= $esEdicio && ($projecte['visible'] ?? 0) ? 'checked' : '' ?>>
                            Projecte visible al públic
                        </label>
                        <small>Marca aquesta casella perquè el projecte aparegui al portafoli</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="data_publicacio">Data de publicació</label>
                        <input type="date" 
                               name="data_publicacio" 
                               id="data_publicacio" 
                               class="form-control"
                               value="<?= $esEdicio ? ($projecte['data_publicacio'] ?? '') : '' ?>">
                        <small>Data en què es va publicar o llançar el projecte</small>
                    </div>
                </div>
            </div>

            <!-- Barra d'accions inferior -->
            <div class="form-actions-bottom">
                <div class="form-actions-left">
                    <?php if ($esEdicio): ?>
                        <button type="button" class="btn btn-danger" id="deleteBtn">
                            <i class="fas fa-trash"></i>
                            Eliminar projecte
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions-right">
                    <a href="projectes.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel·lar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?= $esEdicio ? 'Actualitzar projecte' : 'Crear projecte' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmació d'eliminació -->
<?php if ($esEdicio): ?>
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar eliminació</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar aquest projecte? Aquesta acció no es pot desfer.</p>
            <p><strong>Projecte:</strong> <?= htmlspecialchars($projecte['nom_ca'] ?? 'Sense nom') ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Cancel·lar</button>
            <form method="POST" action="eliminar-projecte.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= $projecteId ?>">
                <button type="submit" class="btn btn-danger">Eliminar definitivament</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestió de pestanyes d'idiomes
    const languageTabs = document.querySelectorAll('.language-tab');
    const languageContents = document.querySelectorAll('.language-content');
    
    languageTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const language = this.dataset.language;
            
            // Desactivar tots els tabs i continguts
            languageTabs.forEach(t => t.classList.remove('active'));
            languageContents.forEach(c => c.classList.remove('active'));
            
            // Activar el tab i contingut seleccionat
            this.classList.add('active');
            document.getElementById(`content-${language}`).classList.add('active');
        });
    });
    
    // Modal d'eliminació
    <?php if ($esEdicio): ?>
    const deleteBtn = document.getElementById('deleteBtn');
    const deleteModal = document.getElementById('deleteModal');
    const modalCloses = document.querySelectorAll('.modal-close');
    
    if (deleteBtn && deleteModal) {
        deleteBtn.addEventListener('click', function() {
            deleteModal.style.display = 'flex';
        });
        
        modalCloses.forEach(close => {
            close.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
        });
        
        // Tancar amb click fora del modal
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }
    <?php endif; ?>
    
    // Validació de formulari
    const form = document.getElementById('projectForm');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('input[required], textarea[required]');
        let hasErrors = false;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                hasErrors = true;
            } else {
                field.classList.remove('error');
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            alert('Si us plau, emplena tots els camps obligatoris marcats amb *');
        }
    });
});
</script>

<script src="js/backend.js"></script>
<?php require_once 'includes/footer.php'; ?>
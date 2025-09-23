<?php
// Protecció d'autenticació primer de tot
require_once 'includes/auth-simple.php';

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
                    <h2>Gestió de Projectes</h2>
                    <button class="btn btn-primary" id="addProjectBtn">
                        <i class="fas fa-plus"></i> Nou Projecte
                    </button>
                </div>
            
            <div class="filters-bar">
                <div class="filter-group">
                    <select>
                        <option>Tots els estats</option>
                        <option>Publicat</option>
                        <option>Esborrany</option>
                        <option>En desenvolupament</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select>
                        <option>Totes les tecnologies</option>
                        <option>Laravel</option>
                        <option>Symfony</option>
                        <option>Vue.js</option>
                        <option>React</option>
                    </select>
                </div>
                <div class="search-filter">
                    <input type="text" placeholder="Cercar projectes...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <div class="projects-grid">
                <div class="project-card-admin">
                    <div class="project-card-header">
                        <img src="../img/projectes/ecommerce-platform.jpg" alt="Plataforma E-commerce">
                        <div class="project-actions">
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="project-card-body">
                        <h3>Plataforma E-commerce</h3>
                        <p>Desenvolupament d'una plataforma de comerç electrònic amb Laravel i Vue.js</p>
                        <div class="project-tech">
                            <span class="tech-badge">Laravel</span>
                            <span class="tech-badge">Vue.js</span>
                            <span class="tech-badge">MySQL</span>
                        </div>
                        <div class="project-meta">
                            <span class="project-status published">Publicat</span>
                            <span class="project-date">15/12/2023</span>
                        </div>
                    </div>
                </div>
                
                <div class="project-card-admin">
                    <div class="project-card-header">
                        <img src="../img/projectes/api-rest.jpg" alt="API REST Symfony">
                        <div class="project-actions">
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="project-card-body">
                        <h3>API REST Symfony</h3>
                        <p>API robusta per a gestió de recursos amb autenticació JWT</p>
                        <div class="project-tech">
                            <span class="tech-badge">Symfony</span>
                            <span class="tech-badge">API Platform</span>
                            <span class="tech-badge">JWT</span>
                        </div>
                        <div class="project-meta">
                            <span class="project-status published">Publicat</span>
                            <span class="project-date">10/12/2023</span>
                        </div>
                    </div>
                </div>
                
                <div class="project-card-admin">
                    <div class="project-card-header">
                        <img src="../img/projectes/sistema-reserves.jpg" alt="Sistema de Reserves">
                        <div class="project-actions">
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="project-card-body">
                        <h3>Sistema de Reserves</h3>
                        <p>Sistema de gestió de reserves per a restaurant amb confirmació per email</p>
                        <div class="project-tech">
                            <span class="tech-badge">PHP</span>
                            <span class="tech-badge">JavaScript</span>
                            <span class="tech-badge">MySQL</span>
                        </div>
                        <div class="project-meta">
                            <span class="project-status draft">Esborrany</span>
                            <span class="project-date">05/12/2023</span>
                        </div>
                    </div>
                </div>
                
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

<?php include 'includes/footer.php'; ?>
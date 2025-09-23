<?php
// Protecció d'autenticació primer de tot
require_once 'includes/auth-simple.php';

$current_page = 'media';
include 'includes/page-header.php';
$page_title = getPageTitle($current_page);
include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <section class="content-section active">
            <?php renderPageHeader($current_page); ?>
            
            <div class="section-header">
                <h2>Biblioteca de Media</h2>
                <button class="btn btn-primary" id="uploadMediaBtn">
                    <i class="fas fa-upload"></i> Pujar Fitxers
                </button>
            </div>
            
            <div class="filters-bar">
                <div class="filter-group">
                    <select>
                        <option>Tots els tipus</option>
                        <option>Imatges</option>
                        <option>Documents</option>
                        <option>Vídeos</option>
                        <option>Altres</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select>
                        <option>Ordenar per data</option>
                        <option>Ordenar per nom</option>
                        <option>Ordenar per mida</option>
                    </select>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <div class="search-filter">
                    <input type="text" placeholder="Cercar fitxers...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <!-- Upload Area -->
            <div class="upload-area" id="uploadArea">
                <div class="upload-content">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h3>Arrossega fitxers aquí o clica per seleccionar</h3>
                    <p>Formats suportats: JPG, PNG, GIF, PDF, DOC, MP4 (Max. 10MB)</p>
                    <input type="file" id="fileInput" multiple accept="image/*,application/pdf,.doc,.docx,video/*">
                </div>
            </div>
            
            <!-- Media Grid -->
            <div class="media-grid" id="mediaGrid">
                <div class="media-item">
                    <div class="media-preview">
                        <img src="../img/projectes/ecommerce-platform.jpg" alt="Ecommerce Platform">
                        <div class="media-overlay">
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-info">
                        <h4>ecommerce-platform.jpg</h4>
                        <p>1920x1080 • 245 KB</p>
                        <span class="media-date">15/12/2023</span>
                    </div>
                </div>
                
                <div class="media-item">
                    <div class="media-preview">
                        <img src="../img/projectes/api-rest.jpg" alt="API REST">
                        <div class="media-overlay">
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-info">
                        <h4>api-rest.jpg</h4>
                        <p>1920x1080 • 312 KB</p>
                        <span class="media-date">10/12/2023</span>
                    </div>
                </div>
                
                <div class="media-item">
                    <div class="media-preview">
                        <img src="../img/projectes/sistema-reserves.jpg" alt="Sistema Reserves">
                        <div class="media-overlay">
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-info">
                        <h4>sistema-reserves.jpg</h4>
                        <p>1920x1080 • 189 KB</p>
                        <span class="media-date">05/12/2023</span>
                    </div>
                </div>
                
                <div class="media-item">
                    <div class="media-preview document">
                        <i class="fas fa-file-pdf"></i>
                        <div class="media-overlay">
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Descarregar">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-info">
                        <h4>curriculum-vitae.pdf</h4>
                        <p>PDF • 2.1 MB</p>
                        <span class="media-date">01/12/2023</span>
                    </div>
                </div>
                
                <div class="media-item">
                    <div class="media-preview">
                        <img src="../img/Me.jpg" alt="Profile Photo">
                        <div class="media-overlay">
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-info">
                        <h4>profile-photo.jpg</h4>
                        <p>800x1000 • 156 KB</p>
                        <span class="media-date">25/11/2023</span>
                    </div>
                </div>
                
                <div class="media-item">
                    <div class="media-preview">
                        <img src="../img/LogoM.png" alt="Logo">
                        <div class="media-overlay">
                            <button class="btn-icon" title="Veure">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-info">
                        <h4>logo-marc.png</h4>
                        <p>512x512 • 45 KB</p>
                        <span class="media-date">20/11/2023</span>
                    </div>
                </div>
            </div>
            
            <div class="table-footer">
                <div class="bulk-actions">
                    <select>
                        <option>Accions massives</option>
                        <option>Eliminar</option>
                        <option>Descarregar</option>
                        <option>Moure a carpeta</option>
                    </select>
                    <button class="btn btn-outline">Aplicar</button>
                </div>
                <div class="pagination">
                    <button class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="pagination-info">Pàgina 1 de 2</span>
                    <button class="pagination-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Modal for Media Details -->
        <div id="mediaModal" class="modal">
            <div class="modal-content media-modal">
                <div class="modal-header">
                    <h3>Detalls del fitxer</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="media-details">
                        <div class="media-preview-large">
                            <img src="../img/projectes/ecommerce-platform.jpg" alt="Preview">
                        </div>
                        <div class="media-metadata">
                            <h4>ecommerce-platform.jpg</h4>
                            <div class="metadata-item">
                                <strong>Mida:</strong> 1920x1080 píxels
                            </div>
                            <div class="metadata-item">
                                <strong>Pes:</strong> 245 KB
                            </div>
                            <div class="metadata-item">
                                <strong>Tipus:</strong> JPEG
                            </div>
                            <div class="metadata-item">
                                <strong>Pujat:</strong> 15/12/2023 14:30
                            </div>
                            <div class="metadata-item">
                                <strong>URL:</strong> 
                                <input type="text" value="https://marcmataro.dev/img/projectes/ecommerce-platform.jpg" readonly>
                                <button class="btn btn-outline btn-sm copy-url">Copiar</button>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-outline">Descarregar</button>
                                <button class="btn btn-danger">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php include 'includes/footer.php'; ?>
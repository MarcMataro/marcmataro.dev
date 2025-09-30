<?php
// Protecció d'autenticació primer de tot
require_once 'includes/auth.php';

$current_page = 'entrades';
include 'includes/page-header.php';
$page_title = getPageTitle($current_page);
include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <section class="content-section active">
            <?php renderPageHeader($current_page); ?>
            
            <div class="section-header">
                <h2>Gestió d'Entrades de Blog</h2>
                <button class="btn btn-primary" id="addPostBtn">
                    <i class="fas fa-plus"></i> Nova Entrada
                </button>
            </div>
            
            <div class="filters-bar">
                <div class="filter-group">
                    <select>
                        <option>Totes les categories</option>
                        <option>PHP</option>
                        <option>Laravel</option>
                        <option>Symfony</option>
                        <option>Seguretat</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select>
                        <option>Tots els estats</option>
                        <option>Publicat</option>
                        <option>Esborrany</option>
                        <option>Programat</option>
                    </select>
                </div>
                <div class="search-filter">
                    <input type="text" placeholder="Cercar entrades...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="select-all"></th>
                            <th>Títol</th>
                            <th>Categoria</th>
                            <th>Data</th>
                            <th>Estat</th>
                            <th>Visites</th>
                            <th>Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>Millors pràctiques Laravel 2024</td>
                            <td><span class="badge badge-primary">Laravel</span></td>
                            <td>15/01/2024</td>
                            <td><span class="status-badge published">Publicat</span></td>
                            <td>324</td>
                            <td>
                                <div class="action-buttons">
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
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>Novetats de PHP 8.3</td>
                            <td><span class="badge badge-info">PHP</span></td>
                            <td>10/01/2024</td>
                            <td><span class="status-badge published">Publicat</span></td>
                            <td>587</td>
                            <td>
                                <div class="action-buttons">
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
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>Guia de seguretat en APIs REST</td>
                            <td><span class="badge badge-warning">Seguretat</span></td>
                            <td>05/01/2024</td>
                            <td><span class="status-badge scheduled">Programat</span></td>
                            <td>0</td>
                            <td>
                                <div class="action-buttons">
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
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="table-footer">
                <div class="bulk-actions">
                    <select>
                        <option>Accions massives</option>
                        <option>Eliminar</option>
                        <option>Publicar</option>
                        <option>Moure a esborrany</option>
                    </select>
                    <button class="btn btn-outline">Aplicar</button>
                </div>
                <div class="pagination">
                    <button class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="pagination-info">Pàgina 1 de 3</span>
                    <button class="pagination-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Modal for New Post -->
        <div id="newPostModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Nova Entrada de Blog</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="newPostForm">
                        <div class="form-group">
                            <label>Títol</label>
                            <input type="text" required>
                        </div>
                        <div class="form-group">
                            <label>Contingut</label>
                            <textarea rows="6" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Categoria</label>
                                <select required>
                                    <option value="">Selecciona una categoria</option>
                                    <option>PHP</option>
                                    <option>Laravel</option>
                                    <option>Symfony</option>
                                    <option>Seguretat</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Estat</label>
                                <select required>
                                    <option value="">Selecciona estat</option>
                                    <option>Publicat</option>
                                    <option>Esborrany</option>
                                    <option>Programat</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline">Cancel·lar</button>
                            <button type="submit" class="btn btn-primary">Publicar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php include 'includes/footer.php'; ?>
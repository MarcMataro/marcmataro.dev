<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panell de Control - Emmaalcala.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-styles.css">
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../img/LogoM.png" alt="Logo Marc Mataró" class="sidebar-logo">
            <h2>Panell de Control</h2>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
                    <a href="#dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#entrades">
                        <i class="fas fa-newspaper"></i>
                        <span>Entrades de Blog</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#projectes">
                        <i class="fas fa-briefcase"></i>
                        <span>Projectes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#media">
                        <i class="fas fa-images"></i>
                        <span>Media Library</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#configuracio">
                        <i class="fas fa-cog"></i>
                        <span>Configuració</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">MM</div>
                <div class="user-details">
                    <span class="user-name">Marc Mataró</span>
                    <span class="user-role">Administrador</span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Tancar Sessió</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="content-header">
            <div class="header-left">
                <button id="sidebarToggle" class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <input type="text" placeholder="Cercar...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="notifications">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3>12</h3>
                        <p>Entrades de Blog</p>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i> 5%
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <h3>8</h3>
                        <p>Projectes Publicats</p>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i> 12%
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3>2.4K</h3>
                        <p>Visites Mensuals</p>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i> 8%
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3>24</h3>
                        <p>Nous Comentaris</p>
                    </div>
                    <div class="stat-trend negative">
                        <i class="fas fa-arrow-down"></i> 3%
                    </div>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="content-column">
                    <div class="card">
                        <div class="card-header">
                            <h3>Activitat Recent</h3>
                            <button class="card-action">Veure tot</button>
                        </div>
                        <div class="card-body">
                            <div class="activity-list">
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p>Nova entrada de blog: <strong>"Millors pràctiques Laravel 2024"</strong></p>
                                        <span class="activity-time">Fa 2 hores</span>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p>Projecte actualitzat: <strong>API REST Symfony</strong></p>
                                        <span class="activity-time">Ahir</span>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-comment"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p>Nou comentari a <strong>"Novetats PHP 8"</strong></p>
                                        <span class="activity-time">Fa 2 dies</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="content-column">
                    <div class="card">
                        <div class="card-header">
                            <h3>Tràfic del Lloc</h3>
                            <div class="time-filter">
                                <select>
                                    <option>7 dies</option>
                                    <option>30 dies</option>
                                    <option selected>90 dies</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="traffic-chart">
                                <canvas id="trafficChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blog Posts Section -->
        <section id="entrades" class="content-section">
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

        <!-- Projects Section -->
        <section id="projectes" class="content-section">
            <div class="section-header">
                <h2>Gestió de Projectes</h2>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nou Projecte
                </button>
            </div>
            
            <div class="projects-grid">
                <div class="project-card-admin">
                    <div class="project-card-header">
                        <img src="../img/projectes/ecommerce-platform.jpg" alt="Plataforma E-commerce">
                        <div class="project-actions">
                            <button class="btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="project-card-body">
                        <h3>Plataforma E-commerce</h3>
                        <p>Desenvolupament d'una plataforma de comerç electrònic amb Laravel i Vue.js</p>
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
                            <button class="btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="project-card-body">
                        <h3>API REST Symfony</h3>
                        <p>API robusta per a gestió de recursos amb autenticació JWT</p>
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
                            <button class="btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="project-card-body">
                        <h3>Sistema de Reserves</h3>
                        <p>Sistema de gestió de reserves per a restaurant amb confirmació per email</p>
                        <div class="project-meta">
                            <span class="project-status draft">Esborrany</span>
                            <span class="project-date">05/12/2023</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

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

    <script src="js/backend.js"></script>
</body>
</html>
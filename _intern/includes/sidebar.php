<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/LogoM.png" alt="Logo Marc Mataró" class="sidebar-logo">
        <h2>Menú principal</h2>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'entrades') ? 'active' : ''; ?>">
                <a href="entrades.php">
                    <i class="fas fa-newspaper"></i>
                    <span>Entrades de Blog</span>
                </a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'projectes') ? 'active' : ''; ?>">
                <a href="projectes.php">
                    <i class="fas fa-briefcase"></i>
                    <span>Projectes</span>
                </a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'media') ? 'active' : ''; ?>">
                <a href="media.php">
                    <i class="fas fa-images"></i>
                    <span>Media Library</span>
                </a>
            </li>
            <li class="nav-item <?php echo ($current_page == 'configuracio') ? 'active' : ''; ?>">
                <a href="configuracio.php">
                    <i class="fas fa-cog"></i>
                    <span>Configuració</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <img src="../img/Me.jpg" alt="Marc Mataró" class="user-avatar">
            <div class="user-details">
                <h4>Marc Mataró</h4>
                <p>Administrador</p>
            </div>
        </div>
        <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Tancar Sessió</span>
        </button>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<main class="main-content">
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
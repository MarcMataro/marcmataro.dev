<nav class="nav-container" role="navigation" aria-label="Menú principal">
    <a href="index.php" class="logo" aria-label="Marc Mataró - Pàgina d'inici">
        <img src="<?php echo isset($baseUrl) ? $baseUrl : '.'; ?>/img/LogoM.png" class="logom" alt="Logotip de Marc Mataró - Programador web PHP">
    </a>
    <ul class="nav-menu">
        <li><a href="index.php" aria-current="page">Inici</a></li>
        <li><a href="#projectes">Projectes</a></li>
        <li><a href="#blog">Blog</a></li>
        <li><a href="aboutme.php">Sobre mi</a></li>
        <li><a href="contact.php">Contacte</a></li>
    </ul>
    <div class="hamburger" aria-label="Menú mòbil" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

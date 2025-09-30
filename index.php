<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Marc Mataró - Arquitecte de solucions web i programador PHP. Desenvolupo solucions digitals a mida per a les teves necessitats.">
    <meta name="keywords" content="programador web, PHP, desenvolupador web, arquitecte solucions web, Marc Mataró, programador PHP">
    <meta name="author" content="Marc Mataró">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Marc Mataró | Arquitecte de solucions web PHP">
    <meta property="og:description" content="Desenvolupo solucions digitals a mida per a les teves necessitats.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ca_ES">
    <link rel="canonical" href="https://www.marcmataro.com">
    <title>Marc Mataró | Arquitecte de solucions web</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    
    <?php
    // Funció per obtenir el base URL del projecte
    function getBaseUrl() {
        // Retorna sempre el path correcte per a recursos estàtics
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $dir = str_replace('\\', '/', dirname($scriptName));
        if ($dir === '/' || $dir === '\\') return '';
        return $dir;
    }
    
    $baseUrl = getBaseUrl();
    
    // Carregar projectes de la base de dades
    try {
        require_once '_classes/connexio.php';
        require_once '_classes/projectes.php';
        
        $connexio = Connexio::getInstance();
        $db = $connexio->getConnexio();
        $gestorProjectes = new Projectes($db);
        
        // Obtenir projectes visibles (tant actius com en desenvolupament) en català
        $opcions = [
            'visible' => 1,
            // Temporalment sense filtre d'estat per veure tots els projectes visibles
            'ordenar' => 'data_creacio',
            'direccio' => 'DESC',
            'limit' => 6  // Mostrar només 6 projectes destacats
        ];
        
        $projectesDestacats = $gestorProjectes->obtenirAmbTraducio('ca', $opcions);
    } catch (Exception $e) {
        $projectesDestacats = []; // Array buit si hi ha error
        error_log("Error carregant projectes a index.php: " . $e->getMessage());
    }
    ?>
    
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "Marc Mataró",
      "jobTitle": "Arquitecte de solucions web",
      "skills": "PHP, programació web, desenvolupament web",
      "description": "Arquitecte de solucions web i programador PHP",
      "url": "https://www.marcmataro.com",
      "email": "contacte@marcmataro.com",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Mataró",
        "addressRegion": "Barcelona",
        "addressCountry": "ES"
      }
    }
    </script>
</head>
<body>
    <header role="banner">
        <nav class="nav-container" role="navigation" aria-label="Menú principal">
            <a href="index.php" class="logo" aria-label="Marc Mataró - Pàgina d'inici">
                <img src="<?php echo $baseUrl; ?>/img/LogoM.png" class="logom" alt="Logotip de Marc Mataró - Programador web PHP">
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
    </header>
    
    <main role="main">
        <section class="hero">
            <div class="hero-content">
                <h1 data-translate="title">Arquitecte de solucions web</h1>
                <p data-translate="subtitle">Desenvolupo solucions digitals a mida amb tecnologies modernes per a les teves necessitats específiques</p>
                <a href="#projectes" class="btn" data-translate="button">Veure els meus projectes</a>
            </div>
        </section>

        <!-- SECCIÓ: SOBRE MI -->
        <section id="sobre-mi" class="about section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>Sobre mi</h2>
                    <p>Més de 5 anys creant experiències digitals excepcionals</p>
                </div>
                <div class="about-content">
                    <div class="about-text">
                        <p>Sóc <strong>Marc Mataró</strong>, arquitecte de solucions web especialitzat en PHP. Amb una passió per crear aplicacions web eficients, escalables i amb un codi net, ajudo a empreses i professionals a transformar les seves idees en realitat digital.</p>
                        
                        <p>El meu enfocament combina el coneixement tècnic amb la comprensió de les necessitats del negoci, assegurant que cada projecte no només sigui tècnicament sòlid, sinó que també aporti valor real als usuaris i als objectius de negoci.</p>
                        
                        <div class="skills">
                            <h3>Les meves especialitzacions</h3>
                            <ul>
                                <li>Desenvolupament PHP (Laravel, Symfony)</li>
                                <li>Arquitectura d'aplicacions web escalables</li>
                                <li>Bases de dades MySQL i PostgreSQL</li>
                                <li>APIs RESTful i GraphQL</li>
                                <li>Optimització de rendiment web</li>
                                <li>Integracions de sistemes i tercers</li>
                            </ul>
                        </div>
                    </div>
                    <div class="about-image">
                        <img src="<?php echo $baseUrl; ?>/img/Me.jpg" alt="Marc Mataró - Programador web PHP" width="400" height="500">
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓ: PROJECTES -->
        <section id="projectes" class="projects section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>Els meus projectes</h2>
                    <p>Una mostra del meu treball i les meves capacitats tècniques</p>
                </div>
                
                <div class="projects-grid">
                    <?php if (!empty($projectesDestacats)): ?>
                        <?php foreach ($projectesDestacats as $projecte): ?>
                            <?php
                            // Processar imatge amb base URL automàtic
                            $imatgeProjecte = $baseUrl . '/img/placeholder-project.jpg';
                            if (!empty($projecte['imatge_portada'])) {
                                $nomFitxer = (strpos($projecte['imatge_portada'], '/') !== false) ? basename($projecte['imatge_portada']) : $projecte['imatge_portada'];
                                
                                // Totes les imatges estan a /img/Projects/
                                $rutaWeb = $baseUrl . '/img/Projects/' . $nomFitxer;
                                $rutaFisica = __DIR__ . '/img/Projects/' . $nomFitxer;
                                
                                if (file_exists($rutaFisica)) {
                                    $imatgeProjecte = $rutaWeb;
                                }
                            }
                            
                            // Processar tecnologies
                            $tecnologiesJson = !empty($projecte['tecnologies_principals']) ? json_decode($projecte['tecnologies_principals'], true) : [];
                            $tecnologies = is_array($tecnologiesJson) ? implode(', ', $tecnologiesJson) : 'Tecnologies diverses';
                            ?>
                            
                            <article class="project-card" itemscope itemtype="https://schema.org/CreativeWork">
                                <div class="project-image">
                                    <img src="<?php echo htmlspecialchars($imatgeProjecte); ?>" 
                                         alt="<?php echo htmlspecialchars($projecte['nom'] ?? 'Projecte'); ?>" 
                                         width="600" height="400" itemprop="image"
                                         onerror="this.src='<?php echo $baseUrl; ?>/img/placeholder-project.jpg'">
                                </div>
                                <div class="project-content">
                                    <h3 itemprop="name"><?php echo htmlspecialchars($projecte['nom'] ?? 'Projecte sense nom'); ?></h3>
                                    <p class="project-tech" itemprop="keywords"><?php echo htmlspecialchars($tecnologies); ?></p>
                                    <p class="project-description" itemprop="description"><?php echo htmlspecialchars($projecte['descripcio_curta'] ?? 'Descripció no disponible'); ?></p>
                                    
                                    <?php if (!empty($projecte['descripcio_detallada'])): ?>
                                        <div class="project-details">
                                            <?php 
                                            // Mostrar fragments de la descripció detallada com a característiques
                                            $detalls = explode('.', $projecte['descripcio_detallada']);
                                            if (count($detalls) > 1): ?>
                                                <ul class="project-features">
                                                    <?php foreach (array_slice($detalls, 0, 4) as $detall): ?>
                                                        <?php if (trim($detall)): ?>
                                                            <li><?php echo htmlspecialchars(trim($detall)); ?></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($projecte['url_demo']) || !empty($projecte['url_github'])): ?>
                                        <div class="project-links">
                                            <?php if (!empty($projecte['url_demo'])): ?>
                                                <a href="<?php echo htmlspecialchars($projecte['url_demo']); ?>" 
                                                   class="project-link" 
                                                   target="_blank" 
                                                   aria-label="Veure demo del projecte <?php echo htmlspecialchars($projecte['nom']); ?>">
                                                   Veure demo
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($projecte['url_github'])): ?>
                                                <a href="<?php echo htmlspecialchars($projecte['url_github']); ?>" 
                                                   class="project-link github" 
                                                   target="_blank" 
                                                   aria-label="Veure codi del projecte <?php echo htmlspecialchars($projecte['nom']); ?>">
                                                   <i class="fab fa-github"></i> GitHub
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <a href="#contacte" class="project-link" aria-label="Contactar sobre el projecte <?php echo htmlspecialchars($projecte['nom']); ?>">Més informació</a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Projectes estàtics per fallback -->
                        <article class="project-card" itemscope itemtype="https://schema.org/CreativeWork">
                            <div class="project-image">
                                <img src="<?php echo $baseUrl; ?>/img/placeholder-project.jpg" alt="Projectes en desenvolupament" width="600" height="400" itemprop="image">
                            </div>
                            <div class="project-content">
                                <h3 itemprop="name">Projectes en desenvolupament</h3>
                                <p class="project-tech" itemprop="keywords">PHP, JavaScript, MySQL</p>
                                <p class="project-description" itemprop="description">Estic treballant en nous projectes emocionants que estaràn disponibles aviat.</p>
                                <a href="#contacte" class="project-link">Contacta'm</a>
                            </div>
                        </article>
                    <?php endif; ?>
                </div>
                
                <div class="projects-cta">
                    <p>Vols veure més projectes o tens una idea que vols desenvolupar?</p>
                    <a href="#contacte" class="btn">Parlem del teu projecte</a>
                </div>
            </div>
        </section>

        <!-- NOVA SECCIÓ: BLOG -->
        <section id="blog" class="blog section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>Blog</h2>
                    <p>Articles i tutorials sobre desenvolupament web i tecnologies PHP</p>
                </div>
                
                <div class="blog-grid">
                    <article class="blog-card" itemscope itemtype="https://schema.org/BlogPosting">
                        <div class="blog-image">
                            <img src="<?php echo $baseUrl; ?>/img/blog/laravel-best-practices.jpg" alt="Millors pràctiques per a desenvolupament Laravel" width="600" height="400" itemprop="image">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <time datetime="2023-11-15" itemprop="datePublished">15 de Novembre, 2023</time>
                                <span class="blog-category" itemprop="articleSection">Laravel</span>
                            </div>
                            <h3 itemprop="headline">Millors pràctiques per a desenvolupament Laravel</h3>
                            <p itemprop="description">Descobreix com millorar la qualitat del teu codi Laravel amb aquestes pràctiques recomanades per experts.</p>
                            <a href="#" class="blog-link" itemprop="url">Llegir més</a>
                        </div>
                    </article>
                    
                    <article class="blog-card" itemscope itemtype="https://schema.org/BlogPosting">
                        <div class="blog-image">
                            <img src="<?php echo $baseUrl; ?>/img/blog/api-security.jpg" alt="Seguretat en APIs REST: Guia completa" width="600" height="400" itemprop="image">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <time datetime="2023-10-28" itemprop="datePublished">28 d'Octubre, 2023</time>
                                <span class="blog-category" itemprop="articleSection">Seguretat</span>
                            </div>
                            <h3 itemprop="headline">Seguretat en APIs REST: Guia completa</h3>
                            <p itemprop="description">Protegeix les teves APIs contra vulnerabilitats comunes amb aquesta guia de seguretat aplicada.</p>
                            <a href="#" class="blog-link" itemprop="url">Llegir més</a>
                        </div>
                    </article>
                    
                    <article class="blog-card" itemscope itemtype="https://schema.org/BlogPosting">
                        <div class="blog-image">
                            <img src="<?php echo $baseUrl; ?>/img/blog/php-8-features.jpg" alt="Novetats de PHP 8: Característiques i millores" width="600" height="400" itemprop="image">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <time datetime="2023-09-10" itemprop="datePublished">10 de Setembre, 2023</time>
                                <span class="blog-category" itemprop="articleSection">PHP</span>
                            </div>
                            <h3 itemprop="headline">Novetats de PHP 8: Característiques i millores</h3>
                            <p itemprop="description">Explora les noves característiques de PHP 8 i com aprofitar-les per millorar els teus projectes.</p>
                            <a href="#" class="blog-link" itemprop="url">Llegir més</a>
                        </div>
                    </article>
                </div>
                
                <div class="blog-cta">
                    <a href="#" class="btn">Veure tots els articles</a>
                </div>
            </div>
        </section>

        <!-- SECCIÓ: CONTACTE -->
        <section id="contacte" class="contact section-padding">
            <div class="container">
                <div class="section-header">
                    <h2>Contacte</h2>
                    <p>Parlem del teu proper projecte web</p>
                </div>
                
                <div class="contact-content">
                    <div class="contact-info">
                        <h3>Posem-nos en contacte</h3>
                        <p>Estic interessat en escoltar les teves idees i ajudar-te a transformar-les en solucions digitals efectives. No dubtis en posar-te en contacte amb mi.</p>
                        
                        <div class="contact-details">
                            <div class="contact-item">
                                <div class="contact-icon">📧</div>
                                <div>
                                    <h4>Email</h4>
                                    <a href="mailto:contacte@marcmataro.com">contacte@marcmataro.com</a>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">📱</div>
                                <div>
                                    <h4>Telèfon</h4>
                                    <a href="tel:+34600000000">+34 600 000 000</a>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">📍</div>
                                <div>
                                    <h4>Ubicació</h4>
                                    <p>Mataró, Barcelona</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="social-links">
                            <h4>Segueix-me</h4>
                            <div class="social-icons">
                                <a href="#" aria-label="LinkedIn de Marc Mataró">
                                    <svg xmlns="img/linkedin.svg" width="18" height="18" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16">
                                        <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/>
                                    </svg>
                                </a>
                                <a href="#" aria-label="GitHub de Marc Mataró">
                                    <svg xmlns="img/github.svg" width="18" height="18" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
                                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
                                    </svg>
                                </a>
                                <a href="#" aria-label="X de Marc Mataró">
                                    <svg xmlns="img/twitter-x.svg" width="18" height="18" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
                                        <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-form">
                        <h3>Envia'm un missatge</h3>
                        <form id="contactForm" action="#" method="POST">
                            <div class="form-group">
                                <label for="name">Nom</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Assumpte</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Missatge</label>
                                <textarea id="message" name="message" rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn">Enviar missatge</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2023 Marc Mataró. Tots els drets reservats.</p>
            <nav aria-label="Enllaços legals">
                <a href="privacitat.html">Política de privacitat</a>
                <a href="cookies.html">Política de cookies</a>
                <a href="avis-legal.html">Avís legal</a>
            </nav>
        </div>
    </footer>
    
    <!-- Language Selector -->
    <div class="language-selector-bottom">
        <button class="lang-toggle" aria-label="Canviar idioma" aria-haspopup="true" aria-expanded="false">
            <img src="<?php echo $baseUrl; ?>/img/cat.png" alt="Català" class="flag-icon">
            <span class="lang-text">CA</span>
            <i class="fas fa-chevron-up"></i>
        </button>
        <ul class="lang-dropdown" role="menu">
            <li role="none">
                <a href="?lang=ca" class="lang-option active" role="menuitem" data-lang="ca">
                    <img src="<?php echo $baseUrl; ?>/img/cat.png" alt="Català" class="flag-icon">
                    <span>Català</span>
                </a>
            </li>
            <li role="none">
                <a href="?lang=es" class="lang-option" role="menuitem" data-lang="es">
                    <img src="<?php echo $baseUrl; ?>/img/esp.png" alt="Español" class="flag-icon">
                    <span>Español</span>
                </a>
            </li>
            <li role="none">
                <a href="?lang=en" class="lang-option" role="menuitem" data-lang="en">
                    <img src="<?php echo $baseUrl; ?>/img/eng.png" alt="English" class="flag-icon">
                    <span>English</span>
                </a>
            </li>
        </ul>
    </div>
    
    <script src="js/script.js"></script>
    <script src="js/language-selector.js"></script>
</body>
</html>

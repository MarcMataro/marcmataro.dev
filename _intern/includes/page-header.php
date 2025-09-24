<?php
/**
 * Definicions de pàgines del panell de control
 * Cada pàgina té el seu títol, descripció i icona
 */

function getPageInfo($current_page) {
    $pages = [
        'dashboard' => [
            'title' => 'Dashboard',
            'description' => 'Visió general del teu lloc web i estadístiques principals',
            'icon' => 'fas fa-tachometer-alt',
            'breadcrumb' => [
                ['name' => 'Inici', 'url' => 'dashboard.php'],
                ['name' => 'Dashboard', 'url' => null]
            ]
        ],
        'entrades' => [
            'title' => 'Entrades de Blog',
            'description' => 'Gestiona les entrades del teu blog, crea contingut nou i edita publicacions existents',
            'icon' => 'fas fa-newspaper',
            'breadcrumb' => [
                ['name' => 'Inici', 'url' => 'dashboard.php'],
                ['name' => 'Entrades de Blog', 'url' => null]
            ]
        ],
        'projectes' => [
            'title' => 'Projectes',
            'description' => 'Administra el teu portafoli de projectes i treballs destacats',
            'icon' => 'fas fa-briefcase',
            'breadcrumb' => [
                ['name' => 'Inici', 'url' => 'dashboard.php'],
                ['name' => 'Projectes', 'url' => null]
            ]
        ],
        'media' => [
            'title' => 'Media Library',
            'description' => 'Organitza i gestiona tots els fitxers multimèdia del teu lloc web',
            'icon' => 'fas fa-images',
            'breadcrumb' => [
                ['name' => 'Inici', 'url' => 'dashboard.php'],
                ['name' => 'Media Library', 'url' => null]
            ]
        ],
        'configuracio' => [
            'title' => 'Configuració',
            'description' => 'Ajustos generals del sistema i preferències d\'usuari',
            'icon' => 'fas fa-cog',
            'breadcrumb' => [
                ['name' => 'Inici', 'url' => 'dashboard.php'],
                ['name' => 'Configuració', 'url' => null]
            ]
        ]
    ];
    
    return $pages[$current_page] ?? [
        'title' => 'Pàgina Desconeguda',
        'description' => 'La pàgina sol·licitada no està definida',
        'icon' => 'fas fa-question-circle',
        'breadcrumb' => [
            ['name' => 'Inici', 'url' => 'dashboard.php'],
            ['name' => 'Desconeguda', 'url' => null]
        ]
    ];
}

/**
 * Genera la capçalera HTML de la pàgina
 */
function renderPageHeader($current_page) {
    $pageInfo = getPageInfo($current_page);
    
    echo '<div class="page-header">';
    echo '<div class="page-header-content">';
    
    // Icona de la pàgina
    echo '<div class="page-header-icon">';
    echo '<i class="' . $pageInfo['icon'] . '"></i>';
    echo '</div>';
    
    // Títol i descripció
    echo '<div class="page-header-text">';
    echo '<h1>' . htmlspecialchars($pageInfo['title']) . '</h1>';
    echo '<p>' . htmlspecialchars($pageInfo['description']) . '</p>';
    
    // Breadcrumb
    if (!empty($pageInfo['breadcrumb'])) {
        echo '<nav class="page-breadcrumb">';
        foreach ($pageInfo['breadcrumb'] as $index => $crumb) {
            if ($index > 0) {
                echo '<span class="separator"><i class="fas fa-chevron-right"></i></span>';
            }
            
            if ($crumb['url']) {
                echo '<a href="' . htmlspecialchars($crumb['url']) . '">' . htmlspecialchars($crumb['name']) . '</a>';
            } else {
                echo '<span>' . htmlspecialchars($crumb['name']) . '</span>';
            }
        }
        echo '</nav>';
    }
    
    echo '</div>'; // page-header-text
    echo '</div>'; // page-header-content
    echo '</div>'; // page-header
}

/**
 * Obté només el títol de la pàgina per al <title> del document
 */
function getPageTitle($current_page) {
    $pageInfo = getPageInfo($current_page);
    return $pageInfo['title'] . ' - Panell de Control';
}

/**
 * Genera un header de pàgina personalitzat amb títol i descripció
 */
function generarPageHeader($title, $description = '', $icon = 'fas fa-file-alt', $breadcrumb = []) {
    echo '<div class="page-header">';
    echo '<div class="page-header-content">';
    
    // Breadcrumb per defecte
    if (empty($breadcrumb)) {
        $breadcrumb = [
            ['name' => 'Inici', 'url' => 'dashboard.php'],
            ['name' => $title, 'url' => null]
        ];
    }
    
    echo '<div class="page-header-text">';
    echo '<h1><i class="' . $icon . '"></i> ' . htmlspecialchars($title) . '</h1>';
    
    if (!empty($description)) {
        echo '<p class="page-description">' . htmlspecialchars($description) . '</p>';
    }
    
    // Mostrar breadcrumb
    if (!empty($breadcrumb)) {
        echo '<nav class="page-breadcrumb">';
        foreach ($breadcrumb as $index => $item) {
            if ($index > 0) echo '<span class="separator">›</span>';
            
            if ($item['url']) {
                echo '<a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['name']) . '</a>';
            } else {
                echo '<span class="current">' . htmlspecialchars($item['name']) . '</span>';
            }
        }
        echo '</nav>';
    }
    
    echo '</div>'; // page-header-text
    echo '</div>'; // page-header-content
    echo '</div>'; // page-header
}
?>
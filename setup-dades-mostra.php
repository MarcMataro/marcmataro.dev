<?php
/**
 * Script per insertar dades de mostra per testing
 */

require_once '_classes/connexio.php';
require_once '_classes/projectes.php';

echo "<h2>Inserir Dades de Mostra</h2>\n";

try {
    $db = Connexio::getInstance();
    $projectes = new Projectes($db->getConnexio());
    
    $dadesProjectes = [
        [
            'nom_ca' => 'Portafoli Personal',
            'nom_es' => 'Portfolio Personal',
            'nom_en' => 'Personal Portfolio',
            'slug_ca' => 'portafoli-personal',
            'slug_es' => 'portfolio-personal',
            'slug_en' => 'personal-portfolio',
            'descripcio_curta_ca' => 'Lloc web personal amb informació professional',
            'descripcio_curta_es' => 'Sitio web personal con información profesional',
            'descripcio_curta_en' => 'Personal website with professional information',
            'descripcio_detallada_ca' => 'Un lloc web personal desenvolupat amb PHP i MySQL per mostrar projectes i habilitats professionals. Inclou un panell d\'administració complet.',
            'descripcio_detallada_es' => 'Un sitio web personal desarrollado con PHP y MySQL para mostrar proyectos y habilidades profesionales. Incluye un panel de administración completo.',
            'descripcio_detallada_en' => 'A personal website developed with PHP and MySQL to showcase projects and professional skills. Includes a complete administration panel.',
            'estat' => 'actiu',
            'visible' => 1,
            'url_demo' => 'https://marcmataro.dev',
            'url_github' => 'https://github.com/marcmataro/portfolio',
            'imatge_portada' => 'portfolio.jpg',
            'tecnologies_principals' => '["PHP", "MySQL", "JavaScript", "CSS"]'
        ],
        [
            'nom_ca' => 'Aplicació de Tasques',
            'nom_es' => 'Aplicación de Tareas',
            'nom_en' => 'Task Manager App',
            'slug_ca' => 'app-tasques',
            'slug_es' => 'app-tareas',
            'slug_en' => 'task-manager',
            'descripcio_curta_ca' => 'Aplicació web per gestionar tasques diàries',
            'descripcio_curta_es' => 'Aplicación web para gestionar tareas diarias',
            'descripcio_curta_en' => 'Web application to manage daily tasks',
            'descripcio_detallada_ca' => 'Una aplicació web desenvolupada amb tecnologies modernes per organitzar i fer seguiment de tasques. Inclou funcionalitats avançades com categories, etiquetes i recordatoris.',
            'descripcio_detallada_es' => 'Una aplicación web desarrollada con tecnologías modernas para organizar y hacer seguimiento de tareas. Incluye funcionalidades avanzadas como categorías, etiquetas y recordatorios.',
            'descripcio_detallada_en' => 'A web application developed with modern technologies to organize and track tasks. Includes advanced features like categories, tags and reminders.',
            'estat' => 'desenvolupament',
            'visible' => 0,
            'url_demo' => null,
            'url_github' => 'https://github.com/marcmataro/task-manager',
            'imatge_portada' => 'task-manager.jpg',
            'tecnologies_principals' => '["React", "Node.js", "MongoDB"]'
        ],
        [
            'nom_ca' => 'Sistema de Gestió',
            'nom_es' => 'Sistema de Gestión',
            'nom_en' => 'Management System',
            'slug_ca' => 'sistema-gestio',
            'slug_es' => 'sistema-gestion',
            'slug_en' => 'management-system',
            'descripcio_curta_ca' => 'Sistema complet de gestió empresarial',
            'descripcio_curta_es' => 'Sistema completo de gestión empresarial',
            'descripcio_curta_en' => 'Complete business management system',
            'descripcio_detallada_ca' => 'Un sistema integral de gestió desenvolupat per optimitzar processos empresarials. Inclou mòduls de facturació, inventari i recursos humans.',
            'descripcio_detallada_es' => 'Un sistema integral de gestión desarrollado para optimizar procesos empresariales. Incluye módulos de facturación, inventario y recursos humanos.',
            'descripcio_detallada_en' => 'A comprehensive management system developed to optimize business processes. Includes billing, inventory and human resources modules.',
            'estat' => 'aturat',
            'visible' => 0,
            'url_demo' => null,
            'url_github' => null,
            'imatge_portada' => null,
            'tecnologies_principals' => '["Java", "Spring Boot", "PostgreSQL"]'
        ]
    ];
    
    echo "<h3>Insertant projectes de mostra...</h3>\n";
    
    foreach ($dadesProjectes as $index => $dades) {
        $resultat = $projectes->crear($dades);
        
        if ($resultat['success']) {
            echo "✅ Projecte " . ($index + 1) . " creat: " . $dades['nom_ca'] . " (ID: " . $resultat['id'] . ")\n";
        } else {
            echo "❌ Error creant projecte " . ($index + 1) . ": " . implode(', ', $resultat['errors']) . "\n";
        }
    }
    
    echo "\n<h3>Resum Final</h3>\n";
    
    $tots = $projectes->obtenirTots();
    echo "📊 Total projectes a la base de dades: " . count($tots) . "\n";
    
    echo "\n<h4>Projectes per estat:</h4>\n";
    $perEstat = [];
    foreach ($tots as $projecte) {
        $estat = $projecte['estat'];
        $perEstat[$estat] = ($perEstat[$estat] ?? 0) + 1;
    }
    
    foreach ($perEstat as $estat => $quantitat) {
        echo "- " . ucfirst($estat) . ": " . $quantitat . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n<hr>\n";
echo '<h3>Provar funcionalitats:</h3>';
echo '<a href="_intern/formulari-projecte.php" class="btn">Crear/Editar Projecte</a> | ';
echo '<a href="_intern/projectes.php" class="btn">Veure Projectes</a> | ';
echo '<a href="test-crud-projectes.php" class="btn">Test CRUD</a>';
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3, h4 { color: #333; }
.btn { 
    display: inline-block;
    padding: 8px 16px; 
    background: #007cba; 
    color: white; 
    text-decoration: none; 
    border-radius: 4px; 
    margin: 5px;
}
.btn:hover { background: #005a87; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>
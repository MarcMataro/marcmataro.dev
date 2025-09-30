<?php
/**
 * API REST per a la gestió d'idiomes del blog
 * 
 * Endpoints disponibles:
 * GET    /api/idiomes.php              - Llistar tots els idiomes
 * GET    /api/idiomes.php?id=1         - Obtenir idioma específic
 * POST   /api/idiomes.php              - Crear nou idioma
 * PUT    /api/idiomes.php?id=1         - Actualitzar idioma
 * DELETE /api/idiomes.php?id=1         - Eliminar idioma
 * POST   /api/idiomes.php?action=reorder - Reordenar idiomes
 */

require_once '../includes/auth.php';
require_once '../../_classes/connexio.php';
require_once '../../_classes/idiomes-blog.php';

// Configurar headers per JSON
header('Content-Type: application/json; charset=utf-8');

// Configurar CORS si cal
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestionar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Inicialitzar connexió i classe
    $db = Connexio::getInstance()->getConnexio();
    $idiomes = new IdiomasBlog($db);
    
    // Obtenir mètode HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $action = isset($_GET['action']) ? $_GET['action'] : null;
    
    switch ($method) {
        case 'GET':
            if ($id) {
                // Obtenir idioma específic
                $idioma = $idiomes->obtenirPerId($id);
                if ($idioma) {
                    echo json_encode(['success' => true, 'data' => $idioma]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Idioma no trobat']);
                }
            } else {
                // Llistar tots els idiomes
                $tots = $idiomes->obtenirTots();
                echo json_encode(['success' => true, 'data' => $tots]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'reorder' && isset($input['ordres'])) {
                // Reordenar idiomes
                $resultat = $idiomes->actualitzarOrdres($input['ordres']);
                echo json_encode($resultat);
            } else {
                // Crear nou idioma
                $resultat = $idiomes->crear($input);
                if ($resultat['success']) {
                    http_response_code(201);
                } else {
                    http_response_code(400);
                }
                echo json_encode($resultat);
            }
            break;
            
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID requerit']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $resultat = $idiomes->actualitzar($id, $input);
            
            if (!$resultat['success']) {
                http_response_code(400);
            }
            echo json_encode($resultat);
            break;
            
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID requerit']);
                break;
            }
            
            $resultat = $idiomes->eliminar($id);
            if (!$resultat['success']) {
                http_response_code(400);
            }
            echo json_encode($resultat);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Mètode no permès']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error API idiomes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error intern del servidor'
    ]);
}
?>
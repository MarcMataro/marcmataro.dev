<?php
/**
 * API REST per la gestió del blog
 * Endpoint unificat per totes les operacions del blog
 */

session_start();
require_once '../../_classes/connexio.php';
require_once '../../_classes/blog.php';
require_once '../../includes/auth.php';

// La autenticació ja es verifica al carregar auth.php

// Headers per a l'API REST
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Gestionar peticions OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Inicialitzar connexió i blog
    $db = Connexio::getInstance()->getConnexio();
    $blog = new Blog($db);
    
    // Obtenir el path de la petició
    $requestUri = $_SERVER['REQUEST_URI'];
    $parsedUrl = parse_url($requestUri, PHP_URL_PATH);
    
    // Extreure el path després de "api/blog/"
    if (preg_match('#/api/blog/(.*)#', $parsedUrl, $matches)) {
        $path = $matches[1];
    } else {
        $path = '';
    }
    
    $pathParts = explode('/', trim($path, '/'));
    
    $method = $_SERVER['REQUEST_METHOD'];
    $resource = $pathParts[0] ?? '';
    $id = $pathParts[1] ?? null;
    $action = $pathParts[2] ?? null;
    
    // Obtenir dades del body per POST/PUT
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    // Obtenir paràmetres GET
    $params = $_GET;
    
    switch ($resource) {
        case 'entrades':
            handleEntrades($blog, $method, $id, $action, $input, $params);
            break;
            
        case 'categories':
            handleCategories($blog, $method, $id, $action, $input, $params);
            break;
            
        case 'comentaris':
            handleComentaris($blog, $method, $id, $action, $input, $params);
            break;
            
        case 'usuaris':
            handleUsuaris($blog, $method, $id, $action, $input, $params);
            break;
            
        case 'tags':
            handleTags($blog, $method, $id, $action, $input, $params);
            break;
            
        case 'idiomes':
            handleIdiomes($blog, $method, $id, $action, $input, $params);
            break;
            
        case 'estadistiques':
            handleEstadistiques($blog, $method, $params);
            break;
            
        default:
            throw new Exception('Recurs no trobat', 404);
    }
    
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    http_response_code($statusCode);
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'code' => $statusCode
    ]);
}

/**
 * Gestió d'entrades
 */
function handleEntrades($blog, $method, $id, $action, $input, $params) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Obtenir entrada específica
                $entrada = $blog->obtenirEntrada($id);
                if (!$entrada) {
                    throw new Exception('Entrada no trobada', 404);
                }
                echo json_encode($entrada);
            } else {
                // Llistar entrades amb filtres
                $filtres = [
                    'estat' => $params['estat'] ?? null,
                    'idioma' => $params['idioma'] ?? null,
                    'autor_id' => $params['autor_id'] ?? null,
                    'categoria_id' => $params['categoria_id'] ?? null,
                    'limit' => intval($params['limit'] ?? 50),
                    'offset' => intval($params['offset'] ?? 0)
                ];
                
                $entrades = $blog->llistarEntrades($filtres);
                echo json_encode($entrades);
            }
            break;
            
        case 'POST':
            // Crear nova entrada
            $requiredFields = ['titols', 'continguts'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Camp obligatori: $field", 400);
                }
            }
            
            $dadesTotales = [
                'titols' => $input['titols'],
                'slugs' => $input['slugs'] ?? [],
                'extractes' => $input['extractes'] ?? [],
                'continguts' => $input['continguts'],
                'estat' => $input['estat'] ?? 'esborrany',
                'format' => $input['format'] ?? 'estandard',
                'destacat' => $input['destacat'] ?? false,
                'comentaris_activats' => $input['comentaris_activats'] ?? true,
                'autor_id' => $_SESSION['user_id'],
                'categories' => $input['categories'] ?? [],
                'tags' => $input['tags'] ?? []
            ];
            
            $entradaId = $blog->crearEntrada($dadesTotales);
            
            echo json_encode([
                'success' => true,
                'id' => $entradaId,
                'message' => 'Entrada creada correctament'
            ]);
            break;
            
        case 'PUT':
            if (!$id) {
                throw new Exception('ID d\'entrada requerit', 400);
            }
            
            $success = $blog->actualitzarEntrada($id, $input);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Entrada actualitzada' : 'Error actualitzant entrada'
            ]);
            break;
            
        case 'DELETE':
            if (!$id) {
                throw new Exception('ID d\'entrada requerit', 400);
            }
            
            $success = $blog->eliminarEntrada($id);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Entrada eliminada' : 'Error eliminant entrada'
            ]);
            break;
    }
}

/**
 * Gestió de categories
 */
function handleCategories($blog, $method, $id, $action, $input, $params) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $categoria = $blog->obtenirCategoria($id);
                if (!$categoria) {
                    throw new Exception('Categoria no trobada', 404);
                }
                echo json_encode($categoria);
            } else {
                $idioma = $params['idioma'] ?? 'ca';
                $categories = $blog->llistarCategories($idioma);
                echo json_encode($categories);
            }
            break;
            
        case 'POST':
            $requiredFields = ['nom', 'idioma'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Camp obligatori: $field", 400);
                }
            }
            
            $categoriaId = $blog->crearCategoria($input);
            
            echo json_encode([
                'success' => true,
                'id' => $categoriaId,
                'message' => 'Categoria creada correctament'
            ]);
            break;
            
        case 'PUT':
            if (!$id) {
                throw new Exception('ID de categoria requerit', 400);
            }
            
            $success = $blog->actualitzarCategoria($id, $input);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Categoria actualitzada' : 'Error actualitzant categoria'
            ]);
            break;
            
        case 'DELETE':
            if (!$id) {
                throw new Exception('ID de categoria requerit', 400);
            }
            
            $success = $blog->eliminarCategoria($id);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Categoria eliminada' : 'Error eliminant categoria'
            ]);
            break;
    }
}

/**
 * Gestió de comentaris
 */
function handleComentaris($blog, $method, $id, $action, $input, $params) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $comentari = $blog->obtenirComentari($id);
                if (!$comentari) {
                    throw new Exception('Comentari no trobat', 404);
                }
                echo json_encode($comentari);
            } else {
                $filtres = [
                    'estat' => $params['estat'] ?? 'pendent',
                    'entrada_id' => $params['entrada_id'] ?? null,
                    'limit' => intval($params['limit'] ?? 50),
                    'offset' => intval($params['offset'] ?? 0)
                ];
                
                $comentaris = $blog->llistarComentaris($filtres);
                echo json_encode($comentaris);
            }
            break;
            
        case 'PUT':
            if (!$id || !$action) {
                throw new Exception('ID i acció de comentari requerits', 400);
            }
            
            $success = false;
            $message = '';
            
            switch ($action) {
                case 'aprovar':
                    $success = $blog->aprovarComentari($id);
                    $message = 'Comentari aprovat';
                    break;
                    
                case 'rebutjar':
                    $success = $blog->rebutjarComentari($id);
                    $message = 'Comentari rebutjat';
                    break;
                    
                case 'spam':
                    $success = $blog->marcarSpam($id);
                    $message = 'Comentari marcat com spam';
                    break;
                    
                case 'eliminar':
                    $success = $blog->eliminarComentari($id);
                    $message = 'Comentari eliminat';
                    break;
                    
                default:
                    throw new Exception('Acció no vàlida', 400);
            }
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? $message : 'Error processant comentari'
            ]);
            break;
    }
}

/**
 * Gestió d'usuaris
 */
function handleUsuaris($blog, $method, $id, $action, $input, $params) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $usuari = $blog->obtenirUsuari($id);
                if (!$usuari) {
                    throw new Exception('Usuari no trobat', 404);
                }
                // No retornar dades sensibles
                unset($usuari['password']);
                echo json_encode($usuari);
            } else {
                $filtres = [
                    'rol' => $params['rol'] ?? null,
                    'estat' => $params['estat'] ?? 'actiu',
                    'limit' => intval($params['limit'] ?? 50),
                    'offset' => intval($params['offset'] ?? 0)
                ];
                
                $usuaris = $blog->llistarUsuaris($filtres);
                echo json_encode($usuaris);
            }
            break;
            
        case 'POST':
            $requiredFields = ['nom', 'email', 'password', 'rol'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Camp obligatori: $field", 400);
                }
            }
            
            $usuariId = $blog->crearUsuari($input);
            
            echo json_encode([
                'success' => true,
                'id' => $usuariId,
                'message' => 'Usuari creat correctament'
            ]);
            break;
            
        case 'PUT':
            if (!$id) {
                throw new Exception('ID d\'usuari requerit', 400);
            }
            
            $success = $blog->actualitzarUsuari($id, $input);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Usuari actualitzat' : 'Error actualitzant usuari'
            ]);
            break;
            
        case 'DELETE':
            if (!$id) {
                throw new Exception('ID d\'usuari requerit', 400);
            }
            
            $success = $blog->eliminarUsuari($id);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Usuari eliminat' : 'Error eliminant usuari'
            ]);
            break;
    }
}

/**
 * Gestió de tags
 */
function handleTags($blog, $method, $id, $action, $input, $params) {
    switch ($method) {
        case 'GET':
            $idioma = $params['idioma'] ?? 'ca';
            $tags = $blog->llistarTags($idioma);
            echo json_encode($tags);
            break;
            
        case 'POST':
            $requiredFields = ['nom', 'idioma'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Camp obligatori: $field", 400);
                }
            }
            
            $tagId = $blog->crearTag($input);
            
            echo json_encode([
                'success' => true,
                'id' => $tagId,
                'message' => 'Tag creat correctament'
            ]);
            break;
    }
}

/**
 * Gestió d'idiomes
 */
function handleIdiomes($blog, $method, $id, $action, $input, $params) {
    switch ($method) {
        case 'GET':
            $idiomes = $blog->obtenirIdiomesActius();
            echo json_encode($idiomes);
            break;
            
        case 'POST':
            $requiredFields = ['codi', 'nom'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Camp obligatori: $field", 400);
                }
            }
            
            $idiomaId = $blog->crearIdioma($input);
            
            echo json_encode([
                'success' => true,
                'id' => $idiomaId,
                'message' => 'Idioma creat correctament'
            ]);
            break;
    }
}

/**
 * Gestió d'estadístiques
 */
function handleEstadistiques($blog, $method, $params) {
    if ($method === 'GET') {
        $estadistiques = $blog->obtenirEstadistiques();
        echo json_encode($estadistiques);
    } else {
        throw new Exception('Mètode no permès', 405);
    }
}
?>
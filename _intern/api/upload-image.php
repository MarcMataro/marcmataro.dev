
<?php
// Depuració: mostra errors PHP a la resposta (elimina-ho en producció)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * API per pujar imatges del editor TinyMCE
 */

// Protecció d'autenticació
require_once '../includes/auth.php';

// Configuració
$uploadDir = '../../img/blog/';
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Headers JSON
header('Content-Type: application/json');

try {
    // Verificar que és una petició POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Només es permeten peticions POST');
    }
    
    // Verificar que s'ha enviat un fitxer
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No s\'ha rebut cap imatge vàlida');
    }
    
    $file = $_FILES['image'];
    
    // Validar mida
    if ($file['size'] > $maxFileSize) {
        throw new Exception('La imatge és massa gran. Màxim 5MB.');
    }
    
    // Validar tipus
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipus de fitxer no permès. Només JPG, PNG, GIF i WebP.');
    }
    
    // Crear directori si no existeix
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generar nom únic
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('blog_') . '_' . time() . '.' . strtolower($extension);
    $filepath = $uploadDir . $filename;
    
    // Moure fitxer
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Error movent el fitxer al directori de destinació');
    }
    
    // Optimitzar imatge (opcional)
    optimizeImage($filepath, $mimeType);
    
    // URL de la imatge
    $imageUrl = '/marcmataro.dev/img/blog/' . $filename;
    
    // Resposta d'èxit
    echo json_encode([
        'success' => true,
        'url' => $imageUrl,
        'filename' => $filename,
        'size' => filesize($filepath)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Optimitzar imatge (reduir qualitat si és necessari)
 */
function optimizeImage($filepath, $mimeType) {
    try {
        $maxWidth = 1200;
        $quality = 85;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filepath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filepath);
                break;
            default:
                return; // No optimitzar WebP o altres
        }
        
        if (!$image) return;
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Redimensionar si és massa gran
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = ($height * $maxWidth) / $width;
            
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparència per PNG
            if ($mimeType === 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefill($newImage, 0, 0, $transparent);
            }
            
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Guardar imatge optimitzada
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($newImage, $filepath, $quality);
                    break;
                case 'image/png':
                    imagepng($newImage, $filepath, 9);
                    break;
                case 'image/gif':
                    imagegif($newImage, $filepath);
                    break;
            }
            
            imagedestroy($newImage);
        }
        
        imagedestroy($image);
        
    } catch (Exception $e) {
        // Si falla l'optimització, mantenir la imatge original
        error_log("Error optimitzant imatge: " . $e->getMessage());
    }
}

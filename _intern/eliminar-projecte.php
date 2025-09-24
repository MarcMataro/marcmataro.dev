<?php
/**
 * Script d'eliminació de projectes
 * 
 * Gestiona l'eliminació segura de projectes amb verificació d'autenticació
 */

session_start();
require_once '../_classes/connexio.php';
require_once '../_classes/projectes.php';
require_once 'includes/auth.php';

// Verificar autenticació
verificarAuth();

// Verificar que és una petició POST amb ID vàlid
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: projectes.php?error=peticio_invalida');
    exit;
}

$projecteId = (int)$_POST['id'];

try {
    // Crear connexió i gestor
    $db = new Connexio();
    $gestorProjectes = new Projectes($db->getConnection());
    
    // Eliminar el projecte
    $resultat = $gestorProjectes->eliminar($projecteId);
    
    if ($resultat['success']) {
        header('Location: projectes.php?success=projecte_eliminat');
    } else {
        header('Location: projectes.php?error=error_eliminar');
    }
    
} catch (Exception $e) {
    error_log("Error eliminant projecte: " . $e->getMessage());
    header('Location: projectes.php?error=error_sistema');
}

exit;
?>
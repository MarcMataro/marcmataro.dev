<?php
/**
 * Protecció simplificada d'autenticació
 * Inclou aquest fitxer al començament de cada pàgina protegida
 */

// Iniciar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticació bàsica
if (!isset($_SESSION['autenticat']) || !$_SESSION['autenticat']) {
    $_SESSION['error_access'] = 'Has d\'iniciar sessió per accedir a aquesta pàgina';
    header('Location: index.php');
    exit;
}

// Verificar timeout simple (2 hores)
if (isset($_SESSION['temps_login']) && (time() - $_SESSION['temps_login']) > 7200) {
    session_destroy();
    session_start();
    $_SESSION['error_timeout'] = 'La teva sessió ha expirat. Si us plau, inicia sessió de nou.';
    header('Location: index.php');
    exit;
}

// Actualitzar activitat
$_SESSION['temps_activitat'] = time();

// Variables globals de l'usuari
$usuari_autenticat = [
    'id' => $_SESSION['usuari_id'] ?? 0,
    'nom' => $_SESSION['usuari_nom'] ?? '',
    'cognoms' => $_SESSION['usuari_cognoms'] ?? '',
    'email' => $_SESSION['usuari_email'] ?? '',
    'rol' => $_SESSION['usuari_rol'] ?? 'lector',
    'avatar' => $_SESSION['usuari_avatar'] ?? null,
    'nom_complet' => ($_SESSION['usuari_nom'] ?? '') . ' ' . ($_SESSION['usuari_cognoms'] ?? '')
];
?>
<?php
// Gestió segura de logout
session_start();

// Verificar que hi ha una sessió activa
if (isset($_SESSION['autenticat']) && $_SESSION['autenticat']) {
    // Log de l'acció de logout (opcional)
    $usuari_nom = $_SESSION['usuari_nom'] ?? 'Desconegut';
    error_log("Logout realitzat per l'usuari: " . $usuari_nom . " - IP: " . $_SERVER['REMOTE_ADDR']);
}

// Destruir totes les dades de la sessió
$_SESSION = array();

// Eliminar la cookie de sessió del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sessió completament
session_destroy();

// Iniciar nova sessió per al missatge de confirmació
session_start();
$_SESSION['logout_success'] = 'Has tancat la sessió correctament.';

// Redirigir al formulari de login
header('Location: index.php');
exit;
?>
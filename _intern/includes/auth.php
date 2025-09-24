<?php
/**
 * Fitxer de protecció d'autenticació
 * Inclou aquest fitxer al començament de cada pàgina que necessiti autenticació
 */

// Configuració de seguretat de sessions (només si no hi ha sessió activa)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Posar a 1 en producció amb HTTPS
}

// Iniciar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si l'usuari està autenticat
if (!isset($_SESSION['autenticat']) || !$_SESSION['autenticat']) {
    // Neteja output buffer si existeix
    if (ob_get_length()) {
        ob_end_clean();
    }
    
    // Destruir qualsevol dada de sessió que pugui existir
    session_unset();
    session_destroy();
    
    // Iniciar nova sessió per a missatges d'error
    session_start();
    $_SESSION['error_access'] = 'Has d\'iniciar sessió per accedir a aquesta pàgina';
    
    // Redirigir al formulari de login
    header('Location: index.php');
    exit;
}

// Verificar que existeixen les dades mínimes necessàries
$required_session_vars = ['usuari_id', 'usuari_nom', 'usuari_email', 'usuari_rol', 'temps_login'];
foreach ($required_session_vars as $var) {
    if (!isset($_SESSION[$var])) {
        if (ob_get_length()) {
            ob_end_clean();
        }
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// Verificar timeout de sessió (2 hores)
$timeout = 2 * 60 * 60; // 2 hores en segons
if (isset($_SESSION['temps_login']) && (time() - $_SESSION['temps_login']) > $timeout) {
    // Sessió expirada
    if (ob_get_length()) {
        ob_end_clean();
    }
    session_unset();
    session_destroy();
    
    // Iniciar nova sessió per a missatges d'error
    session_start();
    $_SESSION['error_timeout'] = 'La teva sessió ha expirat. Si us plau, inicia sessió de nou.';
    
    header('Location: index.php');
    exit;
}

// Verificar inactivitat (30 minuts)
$inactivity_timeout = 30 * 60; // 30 minuts
if (isset($_SESSION['temps_activitat']) && (time() - $_SESSION['temps_activitat']) > $inactivity_timeout) {
    // Inactivitat prolongada
    if (ob_get_length()) {
        ob_end_clean();
    }
    session_unset();
    session_destroy();
    
    session_start();
    $_SESSION['error_inactivity'] = 'Sessió tancada per inactivitat. Si us plau, inicia sessió de nou.';
    
    header('Location: index.php');
    exit;
}

// Regenerar ID de sessió periòdicament per seguretat
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Cada 30 minuts
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Actualitzar temps d'activitat
$_SESSION['temps_activitat'] = time();

// Variables globals de l'usuari autenticat
$usuari_autenticat = [
    'id' => $_SESSION['usuari_id'],
    'nom' => $_SESSION['usuari_nom'],
    'cognoms' => $_SESSION['usuari_cognoms'] ?? '',
    'email' => $_SESSION['usuari_email'],
    'rol' => $_SESSION['usuari_rol'],
    'avatar' => $_SESSION['usuari_avatar'] ?? null,
    'nom_complet' => $_SESSION['usuari_nom'] . ' ' . ($_SESSION['usuari_cognoms'] ?? '')
];

// Verificar permisos per rol (opcional - per a futures funcionalitats)
function verificarPermis($rol_minim = 'lector') {
    global $usuari_autenticat;
    
    $jerarquia_rols = [
        'lector' => 1,
        'editor' => 2,
        'admin' => 3,
        'superadmin' => 4
    ];
    
    $rol_actual = $jerarquia_rols[$usuari_autenticat['rol']] ?? 0;
    $rol_requerit = $jerarquia_rols[$rol_minim] ?? 5;
    
    return $rol_actual >= $rol_requerit;
}

// Funcions auxiliars per gestió de sessions
function esAdmin() {
    global $usuari_autenticat;
    return in_array($usuari_autenticat['rol'], ['admin', 'superadmin']);
}

function esSuperadmin() {
    global $usuari_autenticat;
    return $usuari_autenticat['rol'] === 'superadmin';
}

/**
 * Funció de verificació d'autenticació
 * Comprova si l'usuari està autenticat i té permisos adequats
 */
function verificarAuth($rol_minim = 'lector') {
    // La verificació ja s'ha fet al principi del fitxer
    // Aquesta funció només comprova permisos addicionals si cal
    if ($rol_minim !== 'lector') {
        if (!verificarPermis($rol_minim)) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            session_unset();
            session_destroy();
            header('Location: index.php?error=insufficient_permissions');
            exit;
        }
    }
    return true;
}
?>
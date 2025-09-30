<?php
// Iniciar sessió per gestionar errors i autenticació
session_start();

// Recuperar diferents tipus d'errors i missatges si n'hi ha
$error = '';
$success = '';

if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
} elseif (isset($_SESSION['error_access'])) {
    $error = $_SESSION['error_access'];
    unset($_SESSION['error_access']);
} elseif (isset($_SESSION['error_timeout'])) {
    $error = $_SESSION['error_timeout'];
    unset($_SESSION['error_timeout']);
} elseif (isset($_SESSION['error_inactivity'])) {
    $error = $_SESSION['error_inactivity'];
    unset($_SESSION['error_inactivity']);
} elseif (isset($_SESSION['logout_success'])) {
    $success = $_SESSION['logout_success'];
    unset($_SESSION['logout_success']);
}

// Si ja està autenticat, redirigir al dashboard
if (isset($_SESSION['autenticat']) && $_SESSION['autenticat']) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/img/Logo reduit.png" type="image/png">
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="preload" href="css/pindex.css" as="style">
    <link href="../css/normalize.css" rel="stylesheet">
    <link href="css/pindex.css" rel="stylesheet">
    <title>Emmaalcala.com - Panell d'administració</title>
</head>
<body>
    <div class="formLogin">
        <img src="../img/LogoM.png" class="imgLogin">
        <div class="contenidor">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form action="dashboard.php" method="POST">
                <fieldset>
                    <legend>Panell d'administració</legend>
                    <div class="entrada">
                        <label>Nom d'usuari</label><br><input type="text" name="NomUsuari" placeholder="Nom d'usuari" required>
                    </div>
                    <div class="entrada">
                        <label>Contrasenya</label><br><input type="password" name="Contrassenya" placeholder="Contrassenya" required>
                    </div>
                    <div class="entrada-boto">
                        <button type="submit" value="Accedir">Accedir</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</body>
</html>
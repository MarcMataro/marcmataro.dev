<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/img/Logo reduit.png" type="image/png">
    <link rel="preload" href="css/pindex.css" as="style">
    <link rel="preload" href="../css/Normalize.css" as="style">
    <link href="css/pindex.css" rel="stylesheet">
    <link href="../css/Normalize.css" rel="stylesheet">
    <title>Emmaalcala.com - Panell d'administració</title>
</head>
<body>
    <div class="formLogin">
        <img src="../img/LogoM.png" class="imgLogin">
        <div class="contenidor">
            <form action="main.php" method="POST">
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
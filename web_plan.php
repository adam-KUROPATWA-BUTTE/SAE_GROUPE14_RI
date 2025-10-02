<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/web_plan.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
    <title>Plan du site</title>
</head>
<body>
    <header>
    <div class="top-bar">
        <img src="img/logo.png" alt="Logo" style="height:100px;">
            <div class="right-buttons">
                <button>fr</button>
                <button onclick="window.location.href='login.php'">Se connecter</button>
            </div>
        </div>
    </header>

    <main style="padding:2em;">
        <h1>Plan du site</h1>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="dashboard.php">Tableau de bord</a></li>
            <li><a href="settings.php">Paramètrage</a></li>
            <li><a href="folders.php">Dossiers</a></li>
            <li><a href="help.php">Aide</a></li>
            <li><a href="web_plan.php">Plan du site</a></li>
            <li><a href="login.php">Connexion / Inscription</a></li>
        </ul>
    </main>
</body>
</html>

<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
    <title>Service des relations internationnales AMU</title>
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
    
        <nav class="menu">
            <button onclick="window.location.href='index.php'">Accueil</button>
            <button onclick="window.location.href='dashboard.php'">Tableau de bord</button>
            <button onclick="window.location.href='settings.php'">Paramètrage</button>
            <button onclick="window.location.href='folders.php'">Dossiers</button>
            <button onclick="window.location.href='help.php'" >Aide</button>
            <button onclick="window.location.href='web_plan.php'">Plan du site</button>
        </nav>
    </header>
</body>
</html>

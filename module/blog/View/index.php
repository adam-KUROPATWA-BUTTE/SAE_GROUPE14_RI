<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Service des relations internationales AMU</title>
    <base href="http://localhost:8080/">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
</head>
<body>
    <header>
        <div class="top-bar">
            <img src="img/logo.png" alt="Logo" style="height:100px;">
            <div class="right-buttons">
                <button>fr</button>
                <button onclick="window.location.href='index.php?page=login'">Se connecter</button>
            </div>
        </div>
        <nav class="menu">
            <button onclick="window.location.href='index.php?page=home'">Accueil</button>
            <button onclick="window.location.href='index.php?page=dashboard'">Tableau de bord</button>
            <button onclick="window.location.href='index.php?page=settings'">Paramètrage</button>
            <button onclick="window.location.href='index.php?page=folders'">Dossiers</button>
            <button onclick="window.location.href='index.php?page=help'">Aide</button>
            <button onclick="window.location.href='index.php?page=web_plan'">Plan du site</button>
        </nav>
    </header>
    <main>
        <h1>Bienvenue sur le service des relations internationales AMU</h1>
        <p>Utilisez le menu pour naviguer.</p>
    </main>
</body>
</html>
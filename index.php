<?php
// Tu pourras ajouter du PHP ici plus tard si besoin
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
    <title>Service des relations internationales AMU</title>
</head>
<body>
<!-- Header -->
<header>
    <div class="top-bar">
        <img src="img/logo.png" alt="Logo AMU" style="height:100px;">
        <div class="right-buttons">
            <button>fr</button>
            <button onclick="window.location.href='login.php'">Se connecter</button>
        </div>
    </div>
    <nav class="menu">
        <button class="active" onclick="window.location.href='index.php'">Accueil</button>
        <button onclick="window.location.href='dashboard.php'">Tableau de bord</button>
        <button onclick="window.location.href='settings.php'">Paramétrage</button>
        <button onclick="window.location.href='folders.php'">Dossiers</button>
        <button onclick="window.location.href='help.php'">Aide</button>
        <button onclick="window.location.href='web_plan.php'">Plan du site</button>
    </nav>
</header>

<!-- Bandeau bleu pleine largeur -->
<section class="hero-section">
    <img src="img/amu.png" alt="Logo AMU"
         style="height:80px; position:absolute; top:20px; left:20px;">

</section>
<!-- Image pub avec texte par-dessus -->
<section class="pub-section">
    <img src="img/pub.webp" alt="Publicité AMU">
    <div class="pub-text">“Aix-Marseille Université, une université ouverte sur le monde”</div>
</section>



<!-- Contenu principal (dans le carré blanc centré) -->
<main>
    <div class="dashboard-container">
        <div class="card">
            <h2>Complétude des dossiers</h2>

            <!-- Légende -->
            <div class="legend">
                <div class="legend-item">
                    <span class="legend-color complet"></span> Complet
                </div>
                <div class="legend-item">
                    <span class="legend-color incomplet"></span> Incomplet
                </div>
            </div>

            <!-- Donut Chart -->
            <div class="chart-container">
                <div class="donut-chart">
                    <svg width="300" height="300">
                        <!-- cercle fond jaune (incomplet) -->
                        <circle r="140" cx="150" cy="150" fill="transparent" stroke="#EBC55E" stroke-width="40"></circle>
                        <!-- cercle bleu (complet) -->
                        <circle r="140" cx="150" cy="150" fill="transparent" stroke="#2B91BB" stroke-width="40"
                                stroke-dasharray="0 880" stroke-linecap="round"></circle>
                    </svg>
                    <div class="chart-center">
                        <div class="chart-percentage">0%</div>
                        <div class="chart-label">Complet</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
// filepath: /amuhome/n24004659/SAE_GROUPE14_RI/index.php

// Affichage des erreurs pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Récupère la page demandée via l'URL, ex: index.php?page=dashboard
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'dashboard':
        require_once __DIR__ . '/module/blog/Controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;
    case 'login':
        require_once __DIR__ . '/module/blog/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->index();
        break;
    case 'settings':
        require_once __DIR__ . '/module/blog/Controllers/SettingsController.php';
        $controller = new SettingsController();
        $controller->index();
        break;
    case 'folders':
        require_once __DIR__ . '/module/blog/Controllers/FoldersController.php';
        $controller = new FoldersController();
        $controller->index();
        break;
    case 'help':
        require_once __DIR__ . '/module/blog/Controllers/HelpController.php';
        $controller = new HelpController();
        $controller->index();
        break;
    case 'web_plan':
        require_once __DIR__ . '/module/blog/Controllers/WebPlanController.php';
        $controller = new WebPlanController();
        $controller->index();
        break;
    case 'home':
    default:
        // Page d'accueil (vue simple ou via un contrôleur si tu veux)
        require_once __DIR__ . '/module/blog/Controllers/IndexController.php';
        $controller = new IndexController();
        $controller->index();
        break;
}

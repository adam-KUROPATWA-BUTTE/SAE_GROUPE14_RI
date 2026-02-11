<?php
// Calculs pour le graphique Donut
$radius = 130;
$circumference = 2 * pi() * $radius;
$dashArray = ($completionPercentage / 100) * $circumference;

$isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $t([
        'fr' => 'Service des relations internationales de l\'AMU. Informations pour étudiants internationaux, échanges universitaires et partenariats.',
        'en' => 'International Relations Service of AMU. Info for international students, university exchanges, and partnerships.'
    ]) ?>">
    <title><?= $t([
            'fr' => 'Accueil - Service des relations internationales AMU',
            'en' => 'Home - International Relations Service AMU'
        ]) ?></title>

    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/homepage.css">
    <link rel="stylesheet" href="styles/chatbot.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
</head>

<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">
<header>
    <div class="top-bar">
        <img class="logo_amu" src="img/logo.png" alt="AMU Logo">

        <div class="right-buttons">
            <div class="lang-dropdown">
                <button class="dropbtn"><?= htmlspecialchars($lang) ?></button>
                <div class="dropdown-content">
                    <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                    <a href="#" onclick="changeLang('en'); return false;">English</a>
                </div>
            </div>

            <?php if ($isLoggedIn) : ?>
                <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'logout']) ?>'">
                    <?= $t(['fr' => 'Se déconnecter','en' => 'Log out']) ?>
                </button>
            <?php else : ?>
                <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'login']) ?>'">
                    <?= $t(['fr' => 'Se connecter','en' => 'Log in']) ?>
                </button>
            <?php endif; ?>

            <button id="theme-toggle" title="Enable tritanopia accessibility mode">
                <span class="toggle-switch"></span>
            </button>
        </div>
    </div>

    <nav class="menu">
        <button class="active" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-admin']) ?>'"><?= $t(['fr' => 'Accueil','en' => 'Home']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'dashboard-admin']) ?>'"><?= $t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'partners-admin']) ?>'"><?= $t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'"><?= $t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'web_plan-admin']) ?>'"><?= $t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
    </nav>
</header>

<section class="hero-section">
    <img class="hero_logo" src="img/amu.png" alt="AMU Logo">
</section>

<section class="pub-section">
    <img id="pub_amu"
         src="<?= $isTritanopia ? 'img/etudiants_daltoniens.png' : 'img/image_etudiants.png' ?>"
         alt="AMU Promotion">
    <div class="pub-text">
        <?= $t([
            'fr' => 'Aix-Marseille Université, une université ouverte sur le monde',
            'en' => 'Aix-Marseille University, a university open to the world'
        ]) ?>
    </div>
</section>

<main>
    <div class="dashboard-container">
        <div class="card">
            <h2><?= $t(['fr' => 'Complétude du dossier','en' => 'File Completeness']) ?></h2>

            <div class="chart-container">
                <div class="donut-chart">
                    <svg width="300" height="300">
                        <circle id="circle_incomplet" r="<?= $radius ?>" cx="150" cy="150" fill="transparent" stroke-width="40"></circle>
                        <circle id="circle_complet"
                                r="<?= $radius ?>"
                                cx="150"
                                cy="150"
                                fill="transparent"
                                stroke-width="40"
                                stroke-dasharray="<?= $dashArray ?> <?= $circumference ?>"
                                stroke-linecap="round">
                        </circle>
                    </svg>

                    <div class="chart-center">
                        <div class="chart-percentage"><?= (int)round($completionPercentage) ?>%</div>
                        <div class="chart-label"><?= $t(['fr' => 'Complet','en' => 'Complete']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div id="help-bubble" onclick="toggleHelpPopup()">💬</div>

<div id="help-popup" class="chat-popup">
    <div class="help-popup-header">
        <span>Assistant</span>
        <button onclick="toggleHelpPopup()">✖</button>
    </div>
    <div id="chat-messages" class="chat-messages"></div>
    <div id="quick-actions" class="quick-actions"></div>
</div>

<div id="app-config" 
    data-lang="<?= htmlspecialchars($lang) ?>" 
    data-role="admin"
    style="display:none;">
</div>

<script src="js/main.js"></script>
<script src="js/chatbot.js"></script>

<footer>
    <p>&copy; 2026 - Aix-Marseille Université.</p>
    <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
        <img class="insta" src="img/instagram.png" alt="Instagram">
    </a>
</footer>
</body>
</html>
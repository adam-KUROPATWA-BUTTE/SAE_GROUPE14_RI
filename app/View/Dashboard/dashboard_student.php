<?php
/**
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var string $progressStyle
 * @var string $status
 */
$isTritanopia = (isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t(['fr' => 'Tableau de bord (Étudiant)', 'en' => 'Student Dashboard']) ?></title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/chatbot.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

<header>
    <div class="top-bar">
        <img src="img/logo.png" alt="Logo AMU" class="logo_amu">
        <div class="right-buttons">
            <div class="lang-dropdown">
                <button class="dropbtn"><?= htmlspecialchars($lang) ?></button>
                <div class="dropdown-content">
                    <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                    <a href="#" onclick="changeLang('en'); return false;">English</a>
                </div>
            </div>
        </div>
    </div>
    <nav class="menu">
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-student']) ?>'">
            <?= $t(['fr' => 'Accueil','en' => 'Home']) ?>
        </button>
        <button class="active" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'dashboard-student']) ?>'">
            <?= $t(['fr' => 'Mon Tableau de bord','en' => 'My Dashboard']) ?>
        </button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'partners-student']) ?>'">
            <?= $t(['fr' => 'Partenaires','en' => 'Partners']) ?>
        </button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-student']) ?>'">
            <?= $t(['fr' => 'Dossiers','en' => 'Folders']) ?>
        </button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'web_plan-student']) ?>'">
            <?= $t(['fr' => 'Plan du site','en' => 'Sitemap']) ?>
        </button>
    </nav>
</header>

<main>
    <h1><?= $t(['fr' => 'Suivi du dossier', 'en' => 'File Tracking']) ?></h1>

    <div class="progress-container">
        <div class="progress-line" style="<?= $progressStyle ?>"></div>

        <div class="progress-step <?= in_array($status, ['depot', 'instruction', 'decision'], true) ? 'active' : '' ?>">
            <div class="progress-icon"><img src="img/depot.png" alt="Dépôt"></div>
            <div class="progress-circle"></div>
            <span><?= $t(['fr' => 'Dépôt de la demande', 'en' => 'Application Submitted']) ?></span>
        </div>

        <div class="progress-step <?= in_array($status, ['instruction', 'decision'], true) ? 'active' : '' ?>">
            <div class="progress-icon"><img src="img/rafraichir.png" alt="Instruction"></div>
            <div class="progress-circle"></div>
            <span><?= $t(['fr' => 'Instruction en cours', 'en' => 'Under Review']) ?></span>
        </div>

        <div class="progress-step <?= $status === 'decision' ? 'active' : '' ?>">
            <div class="progress-icon"><img src="img/decision.png" alt="Décision"></div>
            <div class="progress-circle"></div>
            <span><?= $t(['fr' => 'Décision prise', 'en' => 'Decision Made']) ?></span>
        </div>
    </div>

    <div class="contact-info-box">
        <p class="contact-title"><?= $t(['fr' => 'Une question ou besoin d’assistance ?', 'en' => 'A question or need assistance?']) ?></p>
        <p><?= $t(['fr' => 'Pour toute information complémentaire...', 'en' => 'For any additional information...']) ?></p>
        <p class="contact-email"><a href="mailto:relations.internationale@amu-univ.fr">relations.internationale@amu-univ.fr</a></p>
    </div>
</main>

<div id="help-bubble" onclick="toggleHelpPopup()">💬</div>
<div id="help-popup" class="chat-popup">
    <div class="help-popup-header">
        <span><?= $t(['fr' => 'Assistant', 'en' => 'Assistant']) ?></span>
        <button onclick="toggleHelpPopup()">✖</button>
    </div>
    <div id="chat-messages" class="chat-messages"></div>
    <div id="quick-actions" class="quick-actions"></div>
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
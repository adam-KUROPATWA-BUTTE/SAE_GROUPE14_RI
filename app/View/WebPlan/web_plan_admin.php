<?php
$isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/web_plan.css">
    <link rel="stylesheet" href="styles/chatbot.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
    <title><?= $t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></title>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

<header>
    <div class="top-bar">
        <img class="logo_amu" src="img/logo.png" alt="Logo AMU">
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
</header>

<main>
    <h1><?= $t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></h1>
    <ul>
        <?php foreach ($links as $link) :
            $url = strval($link['url']);
            $label = strval($link['label']);
            ?>
            <li>
                <a href="<?= htmlspecialchars($buildUrl($url)) ?>">
                    <?= htmlspecialchars($t([
                        'fr' => $label,
                        'en' => $translateLabel($label)
                    ])) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</main>

<div id="help-bubble">💬</div>
<div id="help-popup" class="chat-popup">
    <div class="help-popup-header">
        <span>Assistant</span>
        <button>✖</button>
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
<?php
/**
 * @var string $lang
 * @var string $titre
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var bool $success
 * @var string|null $errorMessage
 */
$isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre) ?></title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/partners.css">
    <link rel="stylesheet" href="styles/chatbot.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

<header>
    <div class="top-bar">
        <img class="logo_amu" src="img/logo.png" alt="Logo">
        <div class="right-buttons">
            <div class="lang-dropdown">
                <button class="dropbtn" id="current-lang"><?= htmlspecialchars($lang) ?></button>
                <div class="dropdown-content">
                    <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                    <a href="#" onclick="changeLang('en'); return false;">English</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="menu">
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-admin']) ?>'"><?= $t(['fr' => 'Accueil','en' => 'Home']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'dashboard-admin']) ?>'"><?= $t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
        <button class="active" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'partners-admin']) ?>'"><?= $t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'"><?= $t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'web_plan-admin']) ?>'"><?= $t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
    </nav>
</header>

<main>
    <h1><?= htmlspecialchars($titre) ?></h1>
    
    <?php if ($success) : ?>
        <p id="success-message" class="success-message">
            <?= $t(['fr' => 'Partenaire ajouté avec succès.', 'en' => 'Partner successfully added.']) ?>
        </p>
    <?php elseif (!empty($errorMessage)) : ?>
        <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <div class="partners-actions">
        <button class="btn-add-partner">
            <span class="btn-plus">+</span>
            <?= $t(['fr' => 'Ajouter', 'en' => 'Add']) ?>
        </button>
        <div id="partner-form-container" class="partner-form hidden">
            <form method="post" action="">
                <div class="form-group">
                    <label for="name"><?= $t(['fr' => 'Continent', 'en' => 'Continent']) ?></label>
                    <input type="text" id="name" name="name" required placeholder="<?= $t(['fr' => 'Ex: Europe', 'en' => 'Ex: Europe']) ?>">
                </div>

                <div class="form-group">
                    <label for="country"><?= $t(['fr' => 'Pays', 'en' => 'Country']) ?></label>
                    <input type="text" id="country" name="country" required placeholder="<?= $t(['fr' => 'Ex: France', 'en' => 'Ex: France']) ?>">
                </div>

                <div class="form-group">
                    <label for="city"><?= $t(['fr' => 'Ville', 'en' => 'City']) ?></label>
                    <input type="text" id="city" name="city" required placeholder="<?= $t(['fr' => 'Ex: Marseille', 'en' => 'Ex: Marseille']) ?>">
                </div>

                <div class="form-group">
                    <label for="institution"><?= $t(['fr' => 'Universités et institutions', 'en' => 'Universities and institutions']) ?></label>
                    <input type="text" id="institution" name="institution" required placeholder="<?= $t(['fr' => 'Ex: Aix-Marseille Université', 'en' => 'Ex: Aix-Marseille University']) ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save"><?= $t(['fr' => 'Enregistrer', 'en' => 'Save']) ?></button>
                    <button type="button" class="btn-cancel"><?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?></button>
                </div>
            </form>
        </div>
    </div>

    <p><?= $t([
            'fr' => 'Veuillez trouver la liste des partenaires d’AMU en cliquant sur ce lien :',
            'en' => 'Please find the list of AMU\'s partners by clicking on this link:'
        ]) ?></p>
    <p class="lien">
        <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
            Universites-et-reseaux-partenaires
        </a>
    </p>

    <img id="Université_partenaires"
         src="img/<?= $isTritanopia ? 'University_green.png' : 'University.png' ?>"
         alt="Partner Universities">

</main>

<footer>
    <p>&copy; 2026 - Aix-Marseille Université.</p>
    <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
        <img class="insta" src="img/instagram.png" alt="Instagram">
    </a>
</footer>

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
<script src="js/partners.js"></script>

</body>
</html>
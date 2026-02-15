<?php
/**
 * Layout de base pour toutes les pages
 *
 * @var string $lang
 * @var string $title
 * @var string $content - Le contenu HTML de la page
 * @var array<string> $styles - Fichiers CSS additionnels
 * @var array<string> $scripts - Fichiers JS additionnels
 * @var string $activeMenu - Menu actif ('home', 'dashboard', 'partners', 'folders', 'sitemap')
 * @var string $userRole - 'admin' ou 'student'
 * @var Closure(array<string, string>): string $t - Fonction de traduction
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 */

$isTritanopia = (isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Styles de base -->
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/chatbot.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>

    <!-- Styles additionnels -->
    <?php foreach ($styles ?? [] as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

<?php include __DIR__ . '/header.php'; ?>

<main>
    <?php
    // Flash messages
    if (isset($_SESSION['message'])): ?>
        <div class="message">
            <?= htmlspecialchars(strval($_SESSION['message'])); ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<?php include __DIR__ . '/chatbot.php'; ?>

<?php include __DIR__ . '/footer.php'; ?>

<!-- Scripts de base -->
<script src="js/main.js"></script>
<script src="js/chatbot.js"></script>

<!-- Scripts additionnels -->
<?php foreach ($scripts ?? [] as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
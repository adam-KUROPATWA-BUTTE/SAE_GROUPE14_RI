<?php

/**
 * Layout spécifique pour les pages Home
 * Sans header (inclus dans le contenu) et SANS <main>
 *
 * @var string $lang
 * @var string $title
 * @var string $content
 * @var array<string> $styles
 * @var array<string> $scripts
 * @var string $userRole
 * @var string|null $metaDescription
 */

$isTritanopia = isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if ($metaDescription !== null): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>

    <title><?= htmlspecialchars($title) ?></title>

    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/chatbot.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>

    <?php foreach ($styles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
</head>

<body class="<?= $isTritanopia ? 'tritanopie' : '' ?> home-page">

<?php if (isset($_SESSION['message'])): ?>
    <div class="message" style="max-width: 1200px; margin: 20px auto;">
        <?= htmlspecialchars((string) $_SESSION['message']); ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<?= $content ?>

<?php include __DIR__ . '/chatbot.php'; ?>

<!-- Footer inline pour home -->
<footer>
    <p>&copy; 2026 - Aix-Marseille Université</p>

    <div class="footer-links">
        <a href="https://www.instagram.com/relationsinternationales_amu/"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Instagram">
            <img class="insta" src="img/instagram.png" alt="Instagram">
        </a>
    </div>
</footer>

<script src="js/main.js"></script>
<script src="js/chatbot.js"></script>

<?php foreach ($scripts as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; ?>

</body>
</html>
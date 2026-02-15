<?php
/**
 * Layout minimal sans header, avec <main>
 * Utilisé quand le header est personnalisé mais qu'on veut garder le container <main>
 *
 * @var string $lang
 * @var string $title
 * @var string $content
 * @var array<string> $styles
 * @var array<string> $scripts
 * @var string $userRole
 * @var string|null $metaDescription
 */

$isTritanopia = (isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (isset($metaDescription)): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>
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

<!-- Le header est dans $content si nécessaire -->

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
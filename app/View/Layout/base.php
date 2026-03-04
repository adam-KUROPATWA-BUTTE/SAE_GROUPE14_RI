<?php
/**
 * Layout de base pour toutes les pages
 *
 * @var string $lang
 * @var string $title
 * @var string $content
 * @var array<string> $styles
 * @var array<string> $scripts
 * @var string $activeMenu
 * @var string $userRole
 * @var bool|null $noMain       — si true, le contenu n'est pas enveloppé dans <main>
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 */

$isTritanopia = isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true;
$noMain       = $noMain ?? false;
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
    <?php foreach ($styles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?><?= $noMain ? ' home-page' : '' ?>">

<?php include __DIR__ . '/header.php'; ?>

<?php if ($noMain): ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message" style="max-width:1200px;margin:20px auto;">
            <?= htmlspecialchars((string) $_SESSION['message']); ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <?= $content ?>

<?php else: ?>

    <main class="<?= $activeMenu ?>">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?= htmlspecialchars((string) $_SESSION['message']); ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

<?php endif; ?>

<?php include __DIR__ . '/chatbot.php'; ?>
<?php include __DIR__ . '/footer.php'; ?>

<!-- Scripts de base -->
<script src="js/main.js"></script>
<script src="js/chatbot.js"></script>

<!-- Scripts additionnels -->
<?php foreach ($scripts as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; ?>

</body>
</html>
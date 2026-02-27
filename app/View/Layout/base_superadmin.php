<?php global $currentPage;
/**
 * Layout Super Admin
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
$isLoggedIn   = isset($_SESSION['user_role']);

$t = function (array $frEn) use ($lang): string {
    return ($lang ?? 'fr') === 'en' ? $frEn['en'] : $frEn['fr'];
};

$buildUrl = function (string $path, array $params = []) use ($lang): string {
    $params['lang'] = $lang ?? 'fr';
    $separator = (strpos($path, '?') === false) ? '?' : '&';
    return $path . $separator . http_build_query($params);
};
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'fr') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if (!empty($metaDescription)): ?>
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

<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

<header>
    <div class="top-bar">
        <img class="logo_amu" src="img/logo.png" alt="Logo AMU">

        <div class="right-buttons">
            <div class="lang-dropdown">
                <button class="dropbtn"><?= htmlspecialchars($lang ?? 'fr') ?></button>
                <div class="dropdown-content">
                    <a href="?page=super-admin&lang=fr">Français</a>
                    <a href="?page=super-admin&lang=en">English</a>
                </div>
            </div>

            <?php if ($isLoggedIn): ?>
                <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'logout']) ?>'">
                    <?= $t(['fr' => 'Se déconnecter', 'en' => 'Log out']) ?>
                </button>
            <?php else: ?>
                <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'login']) ?>'">
                    <?= $t(['fr' => 'Se connecter', 'en' => 'Log in']) ?>
                </button>
            <?php endif; ?>

            <button id="theme-toggle" title="Enable tritanopia accessibility mode">
                <span class="toggle-switch"></span>
            </button>
        </div>
    </div>
</header>

<main>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <?= htmlspecialchars((string) $_SESSION['message']); ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>

<script src="js/main.js"></script>

<?php foreach ($scripts as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; ?>

</body>
</html>
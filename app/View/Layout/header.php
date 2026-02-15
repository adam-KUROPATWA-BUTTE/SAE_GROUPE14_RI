<?php
/**
 * Header commun
 *
 * @var string $lang
 * @var string $activeMenu
 * @var string $userRole - 'admin' ou 'student'
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 */
?>
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

    <nav class="menu">
        <?php
        // Déterminer le suffixe selon le rôle
        $suffix = $userRole === 'admin' ? '-admin' : '-student';

        // Menus disponibles
        $menus = [
            'home' => ['fr' => 'Accueil', 'en' => 'Home'],
            'dashboard' => ['fr' => ($userRole === 'admin' ? 'Tableau de bord' : 'Mon Tableau de bord'), 'en' => ($userRole === 'admin' ? 'Dashboard' : 'My Dashboard')],
            'partners' => ['fr' => 'Partenaires', 'en' => 'Partners'],
            'folders' => ['fr' => 'Dossiers', 'en' => 'Folders'],
            'web_plan' => ['fr' => 'Plan du site', 'en' => 'Sitemap']
        ];

        foreach ($menus as $key => $labels):
            $isActive = $activeMenu === $key;
            $page = $key . $suffix;
            ?>
            <button
                <?= $isActive ? 'class="active"' : '' ?>
                onclick="window.location.href='<?= $buildUrl('index.php', ['page' => $page]) ?>'">
                <?= $t($labels) ?>
            </button>
        <?php endforeach; ?>

        <!-- Bouton déconnexion -->
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'logout']) ?>'">
            <?= $t(['fr' => 'Déconnexion', 'en' => 'Logout']) ?>
        </button>
    </nav>
</header>
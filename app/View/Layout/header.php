<?php
/**
 * Header commun
 *
 * @var string $lang
 * @var string $activeMenu
 * @var string $userRole - 'admin' ou 'student'
 * @var Closure(array<string, string>): string $t
 */


// Récupérer la page actuelle
$currentPage = $_GET['page'] ?? 'home-' . ($userRole === 'admin' ? 'admin' : 'student');
?>
<header>
    <div class="top-bar">
        <img class="logo_amu" src="img/logo.png" alt="Logo AMU">
        <div class="right-buttons">
            <div class="lang-dropdown">
                <button class="dropbtn"><?= htmlspecialchars($lang) ?></button>
                <div class="dropdown-content">
                    <a href="?page=<?= urlencode($currentPage) ?>&lang=fr">Français</a>
                    <a href="?page=<?= urlencode($currentPage) ?>&lang=en">English</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="menu">
        <?php
        $suffix = $userRole === 'admin' ? '-admin' : '-student';

        $menus = [
            'home' => [
                'fr' => 'Accueil',
                'en' => 'Home'
            ],
            'dashboard' => [
                'fr' => ($userRole === 'admin' ? 'Tableau de bord' : 'Mon Tableau de bord'),
                'en' => ($userRole === 'admin' ? 'Dashboard' : 'My Dashboard')
            ],
            'partners' => [
                'fr' => 'Destinations',
                'en' => 'Destinations'
            ],
            'folders' => [
                'fr' => ($userRole === 'admin' ? 'Dossiers' : 'Mon Dossier'),
                'en' => ($userRole === 'admin' ? 'Folders' : 'My Folder')
            ],
        ];
        if ($userRole === 'admin') {
            $menus['messages'] = ['fr' => 'Messages', 'en' => 'Messages'];
        }

        if ($userRole === 'student') {
            $menus['contact'] = [
                'fr' => 'Contact',
                'en' => 'Contact'
            ];
        }

        foreach ($menus as $key => $labels):
            $isActive = $activeMenu === $key;
            $page = $key . $suffix;
            $url = 'index.php?page=' . urlencode($page) . '&lang=' . urlencode($lang);

            if ($key === 'partners' && $userRole === 'student'):
                $urlAmu = 'index.php?page=partners-student&partner=amu&lang=' . urlencode($lang);
                $urlIut = 'index.php?page=partners-student&partner=iut&lang=' . urlencode($lang);
                ?>
                <div class="dropdown <?= $isActive ? 'active' : '' ?>">
                    <button <?= $isActive ? 'class="active"' : '' ?>>
                        <?= $t($labels) ?>
                    </button>
                    <div class="dropdown-content">
                        <a href="<?= htmlspecialchars($urlAmu) ?>">AMU</a>
                        <a href="<?= htmlspecialchars($urlIut) ?>">IUT</a>
                    </div>
                </div>
            <?php else: ?>
                <button
                    <?= $isActive ? 'class="active"' : '' ?>
                    onclick="window.location.href='<?= htmlspecialchars($url) ?>'">
                    <?= $t($labels) ?>
                </button>
            <?php endif;
        endforeach; ?>
    </nav>
</header>
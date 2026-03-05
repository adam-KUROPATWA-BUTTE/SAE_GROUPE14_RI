<?php
/**
 * Header commun
 *
 * @var string $lang
 * @var string $activeMenu
 * @var string $userRole - 'admin', 'student', 'coordinateur_etude', 'coordinateur_stage', 'chef_departement'
 * @var Closure(array<string, string>): string $t
 */

if (empty($userRole) && !empty($_SESSION['role'])) {
    $userRole = $_SESSION['role'];
}

if (!isset($t) || !is_callable($t)) {
    $lang = $lang ?? $_SESSION['lang'] ?? 'fr';
    $t = function(array $frEn) use ($lang): string {
        return $frEn[$lang] ?? $frEn['fr'] ?? '';
    };
}

if (!isset($lang)) {
    $lang = $_SESSION['lang'] ?? 'fr';
}

$isCoordinateur = in_array($userRole, ['coordinateur_etude', 'coordinateur_stage', 'chef_departement', 'coordinateur'], true);

$currentPage = $_GET['page'] ?? (
$userRole === 'admin'        ? 'home-admin'        :
    ($isCoordinateur             ? 'home-coordinateur' : 'home-student')
);
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

            <?php
            $isHomePage = in_array($activeMenu, ['home', 'home-coordinateur'], true);
            ?>

            <?php if ($isHomePage): ?>
                <?php if (isset($_SESSION['role'])): ?>
                    <button onclick="window.location.href='index.php?page=logout&lang=<?= urlencode($lang) ?>'">
                        <?= $t(['fr' => 'Se déconnecter', 'en' => 'Log out']) ?>
                    </button>
                <?php else: ?>
                    <button onclick="window.location.href='index.php?page=login&lang=<?= urlencode($lang) ?>'">
                        <?= $t(['fr' => 'Se connecter', 'en' => 'Log in']) ?>
                    </button>
                <?php endif; ?>

                <button id="theme-toggle" title="Enable tritanopia accessibility mode">
                    <span class="toggle-switch"></span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <nav class="menu">

        <?php if ($userRole === 'coordinateur') : ?>

            <button
                <?= $activeMenu === 'home-coordinateur' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=home-coordinateur&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => 'Accueil', 'en' => 'Home']) ?>
            </button>
            <button
                <?= $activeMenu === 'coordinateur-etude' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=coordinateur-etude&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => "Coordinateur d'étude", 'en' => 'Study Coordinator']) ?>
            </button>
            <button
                <?= $activeMenu === 'coordinateur-stage' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=coordinateur-stage&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => 'Coordinateur de stage', 'en' => 'Internship Coordinator']) ?>
            </button>

        <?php elseif ($userRole === 'coordinateur_etude') : ?>

            <button
                <?= $activeMenu === 'home-coordinateur' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=home-coordinateur&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => 'Accueil', 'en' => 'Home']) ?>
            </button>
            <button
                <?= $activeMenu === 'coordinateur-etude' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=coordinateur-etude&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => "Coordinateur d'étude", 'en' => 'Study Coordinator']) ?>
            </button>

        <?php elseif ($userRole === 'coordinateur_stage') : ?>

            <button
                <?= $activeMenu === 'home-coordinateur' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=home-coordinateur&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => 'Accueil', 'en' => 'Home']) ?>
            </button>
            <button
                <?= $activeMenu === 'coordinateur-stage' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=coordinateur-stage&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => 'Coordinateur de stage', 'en' => 'Internship Coordinator']) ?>
            </button>

        <?php elseif ($userRole === 'chef_departement') : ?>

            <button
                <?= $activeMenu === 'home-coordinateur' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=home-coordinateur&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => 'Accueil', 'en' => 'Home']) ?>
            </button>
            <button
                <?= $activeMenu === 'chef-departement' ? 'class="active"' : '' ?>
                    onclick="window.location.href='index.php?page=chef-departement&lang=<?= urlencode($lang) ?>'">
                <?= $t(['fr' => "Chef de département", 'en' => 'Department Head']) ?>
            </button>

        <?php else : ?>

            <?php
            $suffix = $userRole === 'admin' ? '-admin' : '-student';

            $menus = [
                'home' => [
                    'fr' => 'Accueil',
                    'en' => 'Home',
                ],
                'dashboard' => [
                    'fr' => ($userRole === 'admin' ? 'Tableau de bord' : 'Mon Tableau de bord'),
                    'en' => ($userRole === 'admin' ? 'Dashboard' : 'My Dashboard'),
                ],
                'partners' => [
                    'fr' => 'Destinations',
                    'en' => 'Destinations',
                ],
                'folders' => [
                    'fr' => ($userRole === 'admin' ? 'Dossiers' : 'Mon Dossier'),
                    'en' => ($userRole === 'admin' ? 'Folders' : 'My Folder'),
                ],
            ];

            if ($userRole === 'admin') {
                $menus['messages'] = ['fr' => 'Messages', 'en' => 'Messages'];
            }

            if ($userRole === 'student') {
                $menus['contact'] = ['fr' => 'Contact', 'en' => 'Contact'];
            }

            foreach ($menus as $key => $labels):
                $isActive = $activeMenu === $key;
                $page     = $key . $suffix;
                $url      = 'index.php?page=' . urlencode($page) . '&lang=' . urlencode($lang);

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
                <?php else : ?>
                    <button
                        <?= $isActive ? 'class="active"' : '' ?>
                            onclick="window.location.href='<?= htmlspecialchars($url) ?>'">
                        <?= $t($labels) ?>
                    </button>
                <?php endif;
            endforeach; ?>

        <?php endif; ?>

    </nav>
</header>

<?php
/**
 * Home Admin - Header personnalisé + Layout Home
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var float|int $completionPercentage
 * @var bool $isLoggedIn
 * @var array $statistics Tableau contenant toutes les statistiques
 */

$isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);

ob_start();
?>

    <!-- Header personnalisé pour Home Admin -->
    <header>
        <div class="top-bar">
            <img class="logo_amu" src="img/logo.png" alt="AMU Logo">

            <div class="right-buttons">
                <div class="lang-dropdown">
                    <button class="dropbtn"><?= htmlspecialchars($lang) ?></button>
                    <div class="dropdown-content">
                        <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                        <a href="#" onclick="changeLang('en'); return false;">English</a>
                    </div>
                </div>

                <?php if ($isLoggedIn) : ?>
                    <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'logout']) ?>'">
                        <?= $t(['fr' => 'Se déconnecter','en' => 'Log out']) ?>
                    </button>
                <?php else : ?>
                    <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'login']) ?>'">
                        <?= $t(['fr' => 'Se connecter','en' => 'Log in']) ?>
                    </button>
                <?php endif; ?>

                <button id="theme-toggle" title="Enable tritanopia accessibility mode">
                    <span class="toggle-switch"></span>
                </button>
            </div>
        </div>

        <nav class="menu">
            <button class="active" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-admin']) ?>'"><?= $t(['fr' => 'Accueil','en' => 'Home']) ?></button>
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'dashboard-admin']) ?>'"><?= $t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'partners-admin']) ?>'"><?= $t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'"><?= $t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'web_plan-admin']) ?>'"><?= $t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
        </nav>
    </header>

    <section class="hero-section">
        <img class="hero_logo" src="img/amu.png" alt="AMU Logo">
    </section>

    <section class="pub-section">
        <img id="pub_amu"
             src="<?= $isTritanopia ? 'img/etudiants_daltoniens.png' : 'img/image_etudiants.png' ?>"
             alt="AMU Promotion">
        <div class="pub-text">
            <?= $t([
                'fr' => 'Aix-Marseille Université, une université ouverte sur le monde',
                'en' => 'Aix-Marseille University, a university open to the world'
            ]) ?>
        </div>
    </section>

    <section class="stats-section">
        <button class="carousel-btn prev" onclick="changeSlide(-1)">‹</button>
        <button class="carousel-btn next" onclick="changeSlide(1)">›</button>

        <div class="stats-carousel">
            <div class="carousel-container">
                <!-- Slide 1: Dossiers complets/incomplets -->
                <div class="stat-slide active">
                    <h2><?= $t(['fr' => 'État des dossiers','en' => 'Folder Status']) ?></h2>
                    <div class="stat-content">
                        <div class="stat-item complete">
                            <div class="stat-number"><?= $statistics['complete_folders'] ?? 0 ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Dossiers complets','en' => 'Complete folders']) ?></div>
                        </div>
                        <div class="stat-item incomplete">
                            <div class="stat-number"><?= $statistics['incomplete_folders'] ?? 0 ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Dossiers incomplets','en' => 'Incomplete folders']) ?></div>
                        </div>
                    </div>
                    <div class="completion-bar">
                        <div class="completion-fill" style="width: <?= round(($statistics['complete_folders'] ?? 0) / max(($statistics['total_folders'] ?? 1), 1) * 100) ?>%"></div>
                    </div>
                    <div class="stat-percentage">
                        <?= round(($statistics['complete_folders'] ?? 0) / max(($statistics['total_folders'] ?? 1), 1) * 100) ?>%
                        <?= $t(['fr' => 'de complétion','en' => 'completion']) ?>
                    </div>
                </div>

                <!-- Slide 2: Pays les plus demandés -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Pays les plus demandés','en' => 'Most Requested Countries']) ?></h2>
                    <div class="stat-content ranking">
                        <?php
                        $topCountries = $statistics['top_countries'] ?? [];
                        foreach (array_slice($topCountries, 0, 5) as $index => $country):
                            ?>
                            <div class="ranking-item">
                                <span class="rank"><?= $index + 1 ?></span>
                                <span class="country-name"><?= htmlspecialchars($country['name']) ?></span>
                                <span class="country-count"><?= $country['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Slide 3: Répartition par genre -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Répartition par genre','en' => 'Gender Distribution']) ?></h2>
                    <div class="stat-content">
                        <?php
                        $totalGender = ($statistics['gender']['male'] ?? 0) + ($statistics['gender']['female'] ?? 0);
                        $malePercentage = $totalGender > 0 ? round(($statistics['gender']['male'] ?? 0) / $totalGender * 100) : 0;
                        $femalePercentage = $totalGender > 0 ? round(($statistics['gender']['female'] ?? 0) / $totalGender * 100) : 0;
                        ?>
                        <div class="stat-item male">
                            <div class="stat-number"><?= $statistics['gender']['male'] ?? 0 ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Étudiants','en' => 'Male students']) ?></div>
                            <div class="stat-percentage-small"><?= $malePercentage ?>%</div>
                        </div>
                        <div class="stat-item female">
                            <div class="stat-number"><?= $statistics['gender']['female'] ?? 0 ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Étudiantes','en' => 'Female students']) ?></div>
                            <div class="stat-percentage-small"><?= $femalePercentage ?>%</div>
                        </div>
                    </div>
                    <div class="gender-bar">
                        <div class="gender-male" style="width: <?= $malePercentage ?>%"></div>
                        <div class="gender-female" style="width: <?= $femalePercentage ?>%"></div>
                    </div>
                </div>

                <!-- Slide 4: Répartition par département -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Répartition par département','en' => 'Distribution by Department']) ?></h2>
                    <div class="stat-content departments">
                        <?php
                        $departments = $statistics['departments'] ?? [];
                        $maxDept = !empty($departments) ? max(array_column($departments, 'count')) : 1;
                        foreach (array_slice($departments, 0, 5) as $dept):
                            $barWidth = round(($dept['count'] / max($maxDept, 1)) * 100);
                            ?>
                            <div class="dept-item">
                                <span class="dept-name"><?= htmlspecialchars($dept['name']) ?></span>
                                <div class="dept-bar-container">
                                    <div class="dept-bar" style="width: <?= $barWidth ?>%"></div>
                                </div>
                                <span class="dept-count"><?= $dept['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="carousel-dots">
                <span class="dot active" onclick="goToSlide(0)"></span>
                <span class="dot" onclick="goToSlide(1)"></span>
                <span class="dot" onclick="goToSlide(2)"></span>
                <span class="dot" onclick="goToSlide(3)"></span>
            </div>
        </div>
    </section>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="admin"
         style="display:none;">
    </div>

    <script>
        let currentSlide = 0;
        let autoSlideInterval;

        function showSlide(n) {
            const slides = document.querySelectorAll('.stat-slide');
            const dots = document.querySelectorAll('.dot');

            if (n >= slides.length) currentSlide = 0;
            if (n < 0) currentSlide = slides.length - 1;

            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            currentSlide += direction;
            showSlide(currentSlide);
            resetAutoSlide();
        }

        function goToSlide(n) {
            currentSlide = n;
            showSlide(currentSlide);
            resetAutoSlide();
        }

        function autoSlide() {
            currentSlide++;
            showSlide(currentSlide);
        }

        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(autoSlide, 5000);
        }

        autoSlideInterval = setInterval(autoSlide, 5000);

        const carousel = document.querySelector('.stats-carousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', () => {
                clearInterval(autoSlideInterval);
            });

            carousel.addEventListener('mouseleave', () => {
                resetAutoSlide();
            });
        }
    </script>

<?php
$content = ob_get_clean();

$title = $t([
    'fr' => 'Accueil - Service des relations internationales AMU',
    'en' => 'Home - International Relations Service AMU'
]);
$styles = ['styles/homepage.css'];
$scripts = [];
$activeMenu = 'home';
$userRole = 'admin';

$metaDescription = $t([
    'fr' => 'Service des relations internationales de l\'AMU. Informations pour étudiants internationaux, échanges universitaires et partenariats.',
    'en' => 'International Relations Service of AMU. Info for international students, university exchanges, and partnerships.'
]);

include __DIR__ . '/../Layout/base_home.php';
<?php

/**
 * Home Admin - Header personnalisé + Layout Home
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var float|int $completionPercentage
 * @var bool $isLoggedIn
 * @var array{
 * complete_folders?: int,
 * incomplete_folders?: int,
 * total_folders?: int,
 * top_countries?: list<array{name: string, count: int}>,
 * gender?: array{
 * male?: int,
 * female?: int
 * },
 * departments?: list<array{name: string, count: int}>,
 * incoming_students?: int,
 * outgoing_students?: int,
 * top_continents?: list<array{name: string, count: int}>,
 * europe_countries_count?: int,
 * non_europe_countries_count?: int,
 * total_countries_count?: int
 * } $statistics
 */

$isTritanopia = !empty($_SESSION['tritanopia']) && ((bool) $_SESSION['tritanopia'] === true);

ob_start();
?>

    <header>
        <div class="top-bar">
            <img class="logo_amu" src="img/logo.png" alt="AMU Logo">

            <div class="right-buttons">
                <div class="lang-dropdown">
                    <button class="dropbtn"><?= htmlspecialchars($lang) ?></button>
                    <div class="dropdown-content">
                        <a href="#" onclick="window.mainApp.changeLang('fr'); return false;">Français</a>
                        <a href="#" onclick="window.mainApp.changeLang('en'); return false;">English</a>
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
            <button class="active" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-admin']) ?>'">
                <?= $t(['fr'=> 'Accueil','en' => 'Home']) ?>
            </button>

            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'dashboard-admin']) ?>'">
                <?= $t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?>
            </button>

            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'partners-admin']) ?>'">
                <?= $t(['fr' => 'Partenaires','en' => 'Partners']) ?>
            </button>

            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                <?= $t(['fr' => 'Dossiers','en' => 'Folders']) ?>
            </button>

            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'messages-admin']) ?>'">
                <?= $t(['fr' => 'Messages','en' => 'Messages']) ?>
            </button>

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
        <button class="carousel-btn prev" onclick="window.carousel.changeSlide(-1)">‹</button>
        <button class="carousel-btn next" onclick="window.carousel.changeSlide(1)">›</button>

        <div class="stats-carousel">
            <div class="carousel-container">

                <!-- Slide 1 : État des dossiers -->
                <div class="stat-slide active">
                    <h2><?= $t(['fr' => 'État des dossiers','en' => 'Folder Status']) ?></h2>

                    <?php
                    $completeFolders = (int) ($statistics['complete_folders'] ?? 0);
                    $totalFolders = max((int) ($statistics['total_folders'] ?? 1), 1);
                    $completionRate = round($completeFolders / $totalFolders * 100);
                    ?>

                    <div class="stat-content">
                        <div class="stat-item complete">
                            <div class="stat-number"><?= $completeFolders ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Dossiers complets','en' => 'Complete folders']) ?></div>
                        </div>
                        <div class="stat-item incomplete">
                            <div class="stat-number"><?= (int) ($statistics['incomplete_folders'] ?? 0) ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Dossiers incomplets','en' => 'Incomplete folders']) ?></div>
                        </div>
                    </div>

                    <div class="completion-bar">
                        <div class="completion-fill" style="width: <?= $completionRate ?>%"></div>
                    </div>

                    <div class="stat-percentage">
                        <?= $completionRate ?>%
                        <?= $t(['fr' => 'de complétion','en' => 'completion']) ?>
                    </div>
                </div>


                <!-- Slide 2 : Répartition par département -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Répartition par département','en' => 'Distribution by Department']) ?></h2>

                    <div class="stat-content departments">
                        <?php
                        $departments = $statistics['departments'] ?? [];
                        $maxDept = !empty($departments)
                            ? max(array_map(static fn($d) => (int) $d['count'], $departments))
                            : 1;

                        foreach (array_slice($departments, 0, 5) as $dept):
                            $count = (int) $dept['count'];
                            $barWidth = round($count / max($maxDept, 1) * 100);
                            ?>
                            <div class="dept-item">
                                <span class="dept-name"><?= htmlspecialchars($dept['name']) ?></span>
                                <div class="dept-bar-container">
                                    <div class="dept-bar" style="width: <?= $barWidth ?>%"></div>
                                </div>
                                <span class="dept-count"><?= $count ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Slide 3 : Répartition par genre -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Répartition par genre','en' => 'Gender Distribution']) ?></h2>

                    <?php
                    $genderStats = $statistics['gender'] ?? ['male' => 0, 'female' => 0];

                    $maleCount = (int) ($genderStats['male'] ?? 0);
                    $femaleCount = (int) ($genderStats['female'] ?? 0);

                    $totalGender = $maleCount + $femaleCount;

                    $malePercentage = $totalGender > 0
                        ? round($maleCount / $totalGender * 100)
                        : 0;

                    $femalePercentage = $totalGender > 0
                        ? round($femaleCount / $totalGender * 100)
                        : 0;
                    ?>

                    <div class="stat-content">
                        <div class="stat-item male">
                            <div class="stat-number"><?= $maleCount ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Étudiants','en' => 'Male students']) ?></div>
                            <div class="stat-percentage-small"><?= $malePercentage ?>%</div>
                        </div>
                        <div class="stat-item female">
                            <div class="stat-number"><?= $femaleCount ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Étudiantes','en' => 'Female students']) ?></div>
                            <div class="stat-percentage-small"><?= $femalePercentage ?>%</div>
                        </div>
                    </div>

                    <div class="gender-bar">
                        <div class="gender-male" style="width: <?= $malePercentage ?>%"></div>
                        <div class="gender-female" style="width: <?= $femalePercentage ?>%"></div>
                    </div>
                </div>
                <!-- Slide 4 : Mobilité étudiantes (entrants / sortants) -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Mobilité étudiante','en' => 'Student Mobility']) ?></h2>

                    <?php
                    $incomingStudents = (int) ($statistics['incoming_students'] ?? 0);
                    $outgoingStudents = (int) ($statistics['outgoing_students'] ?? 0);
                    $totalMobility    = $incomingStudents + $outgoingStudents;
                    $incomingPct      = $totalMobility > 0 ? round($incomingStudents / $totalMobility * 100) : 0;
                    $outgoingPct      = $totalMobility > 0 ? round($outgoingStudents / $totalMobility * 100) : 0;
                    ?>

                    <div class="stat-content">
                        <div class="stat-item incoming">
                            <div class="stat-number"><?= $incomingStudents ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Étudiants entrants','en' => 'Incoming students']) ?></div>
                            <div class="stat-percentage-small"><?= $incomingPct ?>%</div>
                        </div>
                        <div class="stat-item outgoing">
                            <div class="stat-number"><?= $outgoingStudents ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Étudiants sortants','en' => 'Outgoing students']) ?></div>
                            <div class="stat-percentage-small"><?= $outgoingPct ?>%</div>
                        </div>
                    </div>

                    <div class="gender-bar">
                        <div class="gender-male" style="width: <?= $incomingPct ?>%"></div>
                        <div class="gender-female" style="width: <?= $outgoingPct ?>%"></div>
                    </div>
                </div>

                <!-- Slide 5 : Classement par continent -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Classement par continent','en' => 'Ranking by Continent']) ?></h2>

                    <div class="stat-content ranking">
                        <?php
                        $topContinents = $statistics['top_continents'] ?? [];
                        foreach (array_slice($topContinents, 0, 6) as $index => $continent):
                            ?>
                            <div class="ranking-item">
                                <span class="rank"><?= $index + 1 ?></span>
                                <span class="country-name"><?= htmlspecialchars($continent['name']) ?></span>
                                <span class="country-count"><?= (int) $continent['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>


                <!-- Slide 6 : Répartition Europe / Hors Europe -->
                <div class="stat-slide">
                    <h2><?= $t(['fr' => 'Répartition Europe / Hors Europe','en' => 'Europe vs. Non-Europe']) ?></h2>

                    <?php
                    $europeCount    = (int) ($statistics['europe_countries_count'] ?? 0);
                    $nonEuropeCount = (int) ($statistics['non_europe_countries_count'] ?? 0);
                    $totalCountries = max($europeCount + $nonEuropeCount, 1);
                    $europePct      = round($europeCount    / $totalCountries * 100);
                    $nonEuropePct   = round($nonEuropeCount / $totalCountries * 100);
                    ?>

                    <div class="stat-content">
                        <div class="stat-item complete">
                            <div class="stat-number"><?= $europeCount ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Pays européens','en' => 'European countries']) ?></div>
                            <div class="stat-percentage-small"><?= $europePct ?>%</div>
                        </div>
                        <div class="stat-item incomplete">
                            <div class="stat-number"><?= $nonEuropeCount ?></div>
                            <div class="stat-label"><?= $t(['fr' => 'Pays hors Europe','en' => 'Non-European countries']) ?></div>
                            <div class="stat-percentage-small"><?= $nonEuropePct ?>%</div>
                        </div>
                    </div>

                    <div class="gender-bar">
                        <div class="gender-male" style="width: <?= $europePct ?>%"></div>
                        <div class="gender-female" style="width: <?= $nonEuropePct ?>%"></div>
                    </div>
                </div>
                <!-- Slide 7 : Pays les plus demandés -->
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
                                <span class="country-count"><?= (int) $country['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>


            <div class="carousel-dots">
                <span class="dot active" onclick="window.carousel.goToSlide(0)"></span>
                <span class="dot" onclick="window.carousel.goToSlide(1)"></span>
                <span class="dot" onclick="window.carousel.goToSlide(2)"></span>
                <span class="dot" onclick="window.carousel.goToSlide(3)"></span>
                <span class="dot" onclick="window.carousel.goToSlide(4)"></span>
                <span class="dot" onclick="window.carousel.goToSlide(5)"></span>
                <span class="dot" onclick="window.carousel.goToSlide(6)"></span>
            </div>
        </div>
    </section>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="admin"
         style="display:none;">
    </div>

<?php
$content = ob_get_clean();

$title = $t([
    'fr' => 'Accueil - Service des relations internationales AMU',
    'en' => 'Home - International Relations Service AMU'
]);

$styles = ['styles/homepage.css'];
$scripts = ['js/carousel.js'];
$activeMenu = 'home';
$userRole = 'admin';

$metaDescription = $t([
    'fr' => 'Service des relations internationales de l\'AMU. Informations pour étudiants internationaux, échanges universitaires et partenariats.',
    'en' => 'International Relations Service of AMU. Info for international students, university exchanges, and partnerships.'
]);

include __DIR__ . '/../Layout/base_home.php';
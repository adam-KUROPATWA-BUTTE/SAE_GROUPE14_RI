<?php
namespace View;

class HomePage
{
    private bool $isLoggedIn;
    private string $lang;
    private float $completionPercentage;

    public function __construct(bool $isLoggedIn = false, string $lang = 'fr', float $completionPercentage = 0)
    {
        $this->isLoggedIn = $isLoggedIn;
        $this->lang = $lang;
        $this->completionPercentage = $completionPercentage;
    }

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        return $path . '?' . http_build_query($params);
    }

    public function render(): void
    {
        $radius = 130;
        $circumference = 2 * pi() * $radius;
        $dashArray = ($this->completionPercentage / 100) * $circumference;
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="<?= $this->t([
                'fr' => 'Service des relations internationales de l\'AMU. Informations pour étudiants internationaux, échanges universitaires et partenariats.',
                'en' => 'International Relations Service of AMU. Info for international students, university exchanges, and partnerships.'
            ]) ?>">
            <meta name="keywords" content="AMU, international, étudiant, mobilité, Aix-Marseille, université, relations internationales">
            <meta name="author" content="Groupe 14 - SAE 2024">
            <title><?= $this->t([
                    'fr' => 'Accueil - Service des relations internationales AMU',
                    'en' => 'Home - International Relations Service AMU'
                ]) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>

        <body>
        <!-- HEADER -->
        <header>
            <div class="top-bar">
                <img id="logo_amu" src="img/logo.png" alt="Logo AMU">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbouton"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>

                    <?php if ($this->isLoggedIn): ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page'=>'logout']) ?>'">
                            <?= $this->t(['fr'=>'Se déconnecter','en'=>'Log out']) ?>
                        </button>
                    <?php else: ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page'=>'login']) ?>'">
                            <?= $this->t(['fr'=>'Se connecter','en'=>'Log in']) ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="menu">
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr'=>'Accueil','en'=>'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard') ?>'"><?= $this->t(['fr'=>'Tableau de bord','en'=>'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/settings') ?>'"><?= $this->t(['fr'=>'Paramétrage','en'=>'Settings']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders') ?>'"><?= $this->t(['fr'=>'Dossiers','en'=>'Files']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'"><?= $this->t(['fr'=>'Plan du site','en'=>'Sitemap']) ?></button>
            </nav>
        </header>

        <!-- HERO SECTION -->
        <section class="hero-section">
            <img id="hero-img" src="img/amu.png" alt="Logo AMU" >
        </section>

        <!-- PUBLICITÉ -->
        <section class="pub-section">
            <img id="pub_amu" src="img/pub.jpg" alt="Publicité AMU">
            <div class="pub-text"><?= $this->t([
                    'fr' => '« Aix-Marseille Université, une université ouverte sur le monde »',
                    'en' => '“Aix-Marseille University, a university open to the world”'
                ]) ?></div>
        </section>

        <!-- MAIN -->
        <main>
            <div class="dashboard-container">
                <div class="card">
                    <h2><?= $this->t(['fr'=>'Complétude du dossier','en'=>'File Completeness']) ?></h2>

                    <div class="legend">
                        <div class="legend-item">
                            <span class="legend-color complet"></span> <?= $this->t(['fr'=>'Complet','en'=>'Complete']) ?>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color incomplet"></span> <?= $this->t(['fr'=>'Incomplet','en'=>'Incomplete']) ?>
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="donut-chart">
                            <svg width="300" height="300">
                                <circle r="<?= $radius ?>" cx="150" cy="150" fill="transparent" stroke="#EBC55E" stroke-width="40"></circle>
                                <circle r="<?= $radius ?>" cx="150" cy="150" fill="transparent" stroke="#2B91BB" stroke-width="40"
                                        stroke-dasharray="<?= $dashArray ?> <?= $circumference ?>"
                                        stroke-linecap="round"></circle>
                            </svg>
                            <div class="chart-center">
                                <div class="chart-percentage"><?= round($this->completionPercentage) ?>%</div>
                                <div class="chart-label"><?= $this->t(['fr'=>'Complet','en'=>'Complete']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bulle d'aide en bas à droite -->
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>

        <!-- Contenu du popup d'aide -->
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr'=>'Aide', 'en'=>'Help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->t(['fr'=>'Bienvenue ! Comment pouvons-nous vous aider ?', 'en'=>'Welcome! How can we help you?']) ?></p>
                <ul>
                    <li><a id="page_complete" href="index.php?page=help" target="_blank"><?= $this->t(['fr'=>'Page d’aide complète', 'en'=>'Full help page']) ?></a></li>
                </ul>
            </div>
        </div>

        <!-- FOOTER -->
        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
        </footer>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const rightButtons = document.querySelector('.right-buttons');
                const navMenu = document.querySelector('nav.menu');
                const menuToggle = document.querySelector('.menu-toggle');

                document.addEventListener("DOMContentLoaded", () => {
                    const rightButtons = document.querySelector('.right-buttons');
                    const navMenu = document.querySelector('nav.menu');
                    const menuToggle = document.querySelector('.menu-toggle');

                    function cloneButtonsForMobile() {
                        const existingClone = navMenu.querySelector('.menu-right-buttons');
                        if (existingClone) existingClone.remove();

                        if (window.innerWidth <= 810) {
                            const clonedButtons = rightButtons.cloneNode(true);
                            clonedButtons.classList.add('menu-right-buttons');
                            navMenu.insertBefore(clonedButtons, navMenu.firstChild);
                        }
                    }

                    cloneButtonsForMobile();
                    window.addEventListener('resize', cloneButtonsForMobile);

                    menuToggle.addEventListener('click', () => {
                        navMenu.classList.toggle('active');
                    });
                });



            document.addEventListener("DOMContentLoaded", () => {
                const menuToggle = document.createElement('button');
                menuToggle.classList.add('menu-toggle');
                menuToggle.innerHTML = '☰';
                document.querySelector('.right-buttons').appendChild(menuToggle);

                const navMenu = document.querySelector('nav.menu');
                menuToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });
            });
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
            }

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
        </script>
        </body>
        </html>
        <?php
    }
}
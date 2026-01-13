<?php

// phpcs:disable Generic.Files.LineLength

namespace View\HomePage;

/**
 * Class HomePageAdmin
 *
 * View responsible for displaying the admin homepage.
 * It includes navigation, accessibility options (tritanopia),
 * a completeness donut chart, and an integrated chatbot.
 */
class HomePageAdmin
{
    /** @var bool Indicates whether the user is logged in */
    private bool $isLoggedIn;

    /** @var string Current language ('fr' or 'en') */
    private string $lang;

    /** @var float Percentage of file completion */
    private float $completionPercentage;

    /**
     * Constructor.
     *
     * @param bool   $isLoggedIn           Whether the admin is logged in
     * @param string $lang                 Current language
     * @param float  $completionPercentage Completion percentage for the donut chart
     */
    public function __construct(bool $isLoggedIn = false, string $lang = 'fr', float $completionPercentage = 0)
    {
        $this->isLoggedIn = $isLoggedIn;
        $this->lang = $lang;
        $this->completionPercentage = $completionPercentage;
    }

    /**
     * Returns the translated string based on the current language.
     *
     * @param array $frEn ['fr' => '...', 'en' => '...']
     * @return string
     */
    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    /**
     * Builds a URL while preserving the current language.
     *
     * @param string $path   Base path
     * @param array  $params Additional query parameters
     * @return string
     */
    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        return $path . '?' . http_build_query($params);
    }

    /**
     * Renders the admin homepage HTML.
     *
     * @return void
     */
    public function render(): void
    {
        // Donut chart calculations
        $radius = 130;
        $circumference = 2 * pi() * $radius;
        $dashArray = ($this->completionPercentage / 100) * $circumference;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $this->lang = $_SESSION['lang'] ?? 'fr';

        // Handle tritanopia (color blindness) mode
        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = $_GET['tritanopia'] === '1';
        }
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
            <meta name="keywords" content="AMU, international, student, mobility, Aix-Marseille, university, international relations">
            <meta name="author" content="Group 14 - SAE 2024">
            <title><?= $this->t([
                    'fr' => 'Accueil - Service des relations internationales AMU',
                    'en' => 'Home - International Relations Service AMU'
                ]) ?></title>

            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/homepage.css">
            <link rel="stylesheet" href="styles/chatbot.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>

        <body class="<?= !empty($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'tritanopie' : '' ?>">
        <!-- HEADER -->
        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="AMU Logo">

                <div class="right-buttons">
                    <!-- Language selector -->
                    <div class="lang-dropdown">
                        <button class="dropbtn"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>

                    <!-- Login / Logout -->
                    <?php if ($this->isLoggedIn) : ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('/logout') ?>'">
                            <?= $this->t(['fr' => 'Se déconnecter','en' => 'Log out']) ?>
                        </button>
                    <?php else : ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('/login') ?>'">
                            <?= $this->t(['fr' => 'Se connecter','en' => 'Log in']) ?>
                        </button>
                    <?php endif; ?>

                    <!-- Tritanopia toggle -->
                    <button id="theme-toggle" title="Enable tritanopia accessibility mode">
                        <span class="toggle-switch"></span>
                    </button>
                </div>
            </div>

            <!-- Navigation menu -->
            <nav class="menu">
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-admin') ?>'"><?= $this->t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners-admin') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'"><?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-admin') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
            </nav>
        </header>

        <!-- HERO SECTION -->
        <section class="hero-section">
            <img class="hero_logo" src="img/amu.png" alt="AMU Logo">
        </section>

        <!-- PROMOTIONAL SECTION -->
        <section class="pub-section">
            <img id="pub_amu"
                 src="<?= (!empty($_SESSION['tritanopia']) && $_SESSION['tritanopia']) ? 'img/etudiants_daltoniens.png' : 'img/image_etudiants.png' ?>"
                 alt="AMU Promotion">
            <div class="pub-text">
                <?= $this->t([
                    'fr' => 'Aix-Marseille Université, une université ouverte sur le monde',
                    'en' => 'Aix-Marseille University, a university open to the world'
                ]) ?>
            </div>
        </section>

        <!-- MAIN CONTENT -->
        <main>
            <div class="dashboard-container">
                <div class="card">
                    <h2><?= $this->t(['fr' => 'Complétude du dossier','en' => 'File Completeness']) ?></h2>

                    <div class="chart-container">
                        <div class="donut-chart">
                            <svg width="300" height="300">
                                <circle id="circle_incomplet" r="<?= $radius ?>" cx="150" cy="150" fill="transparent" stroke-width="40"></circle>
                                <circle id="circle_complet"
                                        r="<?= $radius ?>"
                                        cx="150"
                                        cy="150"
                                        fill="transparent"
                                        stroke-width="40"
                                        stroke-dasharray="<?= $dashArray ?> <?= $circumference ?>"
                                        stroke-linecap="round">
                                </circle>
                            </svg>

                            <div class="chart-center">
                                <div class="chart-percentage"><?= round($this->completionPercentage) ?>%</div>
                                <div class="chart-label"><?= $this->t(['fr' => 'Complet','en' => 'Complete']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Chatbot -->
        <div id="help-bubble" onclick="toggleHelpPopup()">💬</div>

        <div id="help-popup" class="chat-popup">
            <div class="help-popup-header">
                <span>Assistant</span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div id="quick-actions" class="quick-actions"></div>
        </div>

        <!-- Chatbot configuration -->
        <script>
            const CHAT_CONFIG = {
                lang: '<?= $this->lang ?>',
                role: '<?= (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'admin' : 'student' ?>'
            };
        </script>

        <script src="js/chatbot.js"></script>

        <!-- Language & accessibility scripts -->
        <script>
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

            document.addEventListener("DOMContentLoaded", () => {
                const themeToggle = document.getElementById('theme-toggle');

                if (document.body.classList.contains('tritanopie')) {
                    themeToggle.classList.add('active');
                }

                themeToggle.addEventListener('click', function () {
                    document.body.classList.toggle('tritanopie');
                    this.classList.toggle('active');

                    const url = new URL(window.location.href);
                    url.searchParams.set(
                        'tritanopia',
                        document.body.classList.contains('tritanopie') ? '1' : '0'
                    );
                    window.location.href = url.toString();
                });
            });
        </script>

        <footer>
            <p>&copy; 2026 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img class="insta" src="img/instagram.png" alt="Instagram">
            </a>
        </footer>
        </body>
        </html>
        <?php
    }
}

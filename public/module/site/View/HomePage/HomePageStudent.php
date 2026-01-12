<?php

namespace View\HomePage;

/**
 * Class HomePageStudent
 *
 * View responsible for displaying the student homepage.
 * Includes navigation, accessibility options (tritanopia),
 * a hero section, promotional banner, and chatbot integration.
 */
class HomePageStudent
{
    /** @var bool Indicates whether the student is logged in */
    private bool $isLoggedIn;

    /** @var string Current language ('fr' or 'en') */
    private string $lang;

    /**
     * Constructor.
     *
     * @param bool   $isLoggedIn Whether the student is logged in
     * @param string $lang       Current language
     */
    public function __construct(bool $isLoggedIn = false, string $lang = 'fr')
    {
        $this->isLoggedIn = $isLoggedIn;
        $this->lang = $lang;
    }

    /**
     * Returns the translated string based on current language.
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
     * Renders the student homepage HTML.
     *
     * @return void
     */
    public function render(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle language switch
        if (isset($_GET['lang'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $this->lang = $_SESSION['lang'] ?? $this->lang;

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
            <title><?= $this->t(['fr' => 'Espace Étudiant - AMU', 'en' => 'Student Area - AMU']) ?></title>
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
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-student') ?>'"><?= $this->t(['fr' => 'Mon Tableau de bord','en' => 'My Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners-student') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-student') ?>'"><?= $this->t(['fr' => 'Mon Dossier','en' => 'My Folder']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-student') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
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

        <!-- CHATBOT -->
        <div id="help-bubble" onclick="toggleHelpPopup()">💬</div>
        <div id="help-popup" class="chat-popup">
            <div class="help-popup-header">
                <span>Assistant</span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div id="quick-actions" class="quick-actions"></div>
        </div>

        <script>
            // Chatbot config
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

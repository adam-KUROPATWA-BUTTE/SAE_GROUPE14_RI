<?php

// phpcs:disable Generic.Files.LineLength

namespace View\Partners;

/**
 * Class PartnersPageStudent
 *
 * Student view for displaying AMU partner universities.
 * Shows partner list, language selector, accessibility toggle (tritanopia), and chatbot.
 */
class PartnersPageStudent
{
    /** @var string Page title */
    private string $titre;

    /** @var string Current language ('fr' or 'en') */
    private string $lang;

    /**
     * Constructor.
     *
     * @param string $titre Page title
     * @param string $lang  Current language
     */
    public function __construct(string $titre, string $lang = 'fr')
    {
        $this->titre = $titre;
        $this->lang = $lang;
    }

    /**
     * Build a URL while preserving the current language.
     *
     * @param string               $path   Base path
     * @param array<string, mixed> $params Additional query parameters
     * @return string
     */
    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        return $path . '?' . http_build_query($params);
    }

    /**
     * Translate text based on current language.
     *
     * @param array{fr: string, en: string} $frEn ['fr' => '...', 'en' => '...']
     * @return string
     */
    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    /**
     * Render the student partners page HTML.
     *
     * @return void
     */
    public function render(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Correction Level 9: Strict handling of mixed $_GET
        if (isset($_GET['lang'])) {
            $langParam = strval($_GET['lang']);
            if (in_array($langParam, ['fr', 'en'], true)) {
                $_SESSION['lang'] = $langParam;
            }
        }

        // Correction Level 9: Strict assignment from mixed $_SESSION
        $this->lang = isset($_SESSION['lang']) ? strval($_SESSION['lang']) : 'fr';

        // Handle tritanopia (color-blind) mode
        if (isset($_GET['tritanopia'])) {
            $tritaParam = strval($_GET['tritanopia']);
            $_SESSION['tritanopia'] = ($tritaParam === '1');
        }

        // Prepare strict boolean for view to avoid mixed access in template
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);

        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($this->titre) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/partners.css">
            <link rel="stylesheet" href="styles/chatbot.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="Logo">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn" id="current-lang"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-student') ?>'"><?= $this->t(['fr' => 'Mon Tableau de bord','en' => 'My Dashboard']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/partners-student') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-student') ?>'"><?= $this->t(['fr' => 'Mon Dossier','en' => 'My Folder']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-student') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
            </nav>
        </header>

        <main>
            <h1><?= htmlspecialchars($this->titre) ?></h1>
            <div class="partners-actions">
                <button class="btn-add-partner">
                    <span class="btn-plus">+</span>
                    <?= $this->t(['fr' => 'Ajouter', 'en' => 'Add']) ?>
                </button>

            </div>
            <p>
                <?= $this->t([
                    'fr' => 'Veuillez trouver la liste des partenaires d’AMU en cliquant sur ce lien :',
                    'en' => 'Please find the list of AMU\'s partners by clicking on this link:'
                ]) ?>
            </p>

            <p class="lien">
                <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
                    Universites-et-reseaux-partenaires
                </a>
            </p>

            <img id="Université_partenaires"
                 src="img/<?= $isTritanopia ? 'University_green.png' : 'University.png' ?>"
                 alt="Partner Universities">
        </main>

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
            // Chatbot configuration
            const CHAT_CONFIG = {
                lang: '<?= $this->lang ?>',
                role: '<?= (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'admin' : 'student' ?>'
            };

            // Language dropdown toggle
            document.getElementById('current-lang').addEventListener('click', function(event) {
                event.stopPropagation();
                document.querySelector('.right-buttons').classList.toggle('show');
            });

            document.addEventListener('click', () => {
                document.querySelector('.right-buttons').classList.remove('show');
            });

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

            // Responsive menu toggle
            document.addEventListener("DOMContentLoaded", () => {
                const menuToggle = document.createElement('button');
                menuToggle.classList.add('menu-toggle');
                menuToggle.innerHTML = '☰';
                document.querySelector('.right-buttons').appendChild(menuToggle);

                const navMenu = document.querySelector('nav.menu');
                menuToggle.addEventListener('click', () => navMenu.classList.toggle('active'));
            });
        </script>

        <script src="js/chatbot.js"></script>

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
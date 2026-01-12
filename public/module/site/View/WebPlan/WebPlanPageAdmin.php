<?php

namespace View\WebPlan;

/**
 * Class WebPlanPageAdmin
 *
 * Admin view for displaying the site map / web plan.
 * Displays a list of links for navigation and integrates language selection,
 * tritanopia mode, and chatbot.
 */
class WebPlanPageAdmin
{
    /** @var array List of links (each link: ['url' => string, 'label' => string]) */
    private array $links;

    /** @var string Current language ('fr' or 'en') */
    private string $lang;

    /**
     * Constructor.
     *
     * @param array $links Array of links for the site map
     * @param string $lang Current language
     */
    public function __construct(array $links = [], string $lang = 'fr')
    {
        $this->links = $links;
        $this->lang = $lang;
    }

    /**
     * Translate text based on current language.
     *
     * @param array $frEn ['fr' => '...', 'en' => '...']
     * @return string
     */
    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    /**
     * Build URL while preserving the current language.
     *
     * @param string $url Base URL
     * @return string URL with language parameter
     */
    private function buildUrl(string $url): string
    {
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $sep . 'lang=' . urlencode($this->lang);
    }

    /**
     * Translate a French label to English.
     * Default to the original if translation not found.
     *
     * @param string $label French label
     * @return string Translated label
     */
    private function translateLabel(string $label): string
    {
        $map = [
            'Accueil' => 'Home',
            'Tableau de bord' => 'Dashboard',
            'Partenaires' => 'Partners',
            'Dossiers' => 'Folders',
            'Connexion / Inscription' => 'Login / Register',
        ];

        return $map[$label] ?? $label;
    }

    /**
     * Render the admin web plan page HTML.
     *
     * @return void
     */
    public function render(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/web_plan.css">
            <link rel="stylesheet" href="styles/chatbot.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
            <title><?= $this->t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></title>
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'tritanopie' : '' ?>">

        <!-- HEADER -->
        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="Logo AMU">
                <div class="right-buttons">
                    <!-- Language selector -->
                    <div class="lang-dropdown">
                        <button class="dropbtn"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main>
            <h1><?= $this->t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></h1>
            <ul>
                <?php foreach ($this->links as $link) : ?>
                    <li>
                        <a href="<?= htmlspecialchars($this->buildUrl($link['url'])) ?>">
                            <?= htmlspecialchars($this->t([
                                'fr' => $link['label'],
                                'en' => $this->translateLabel($link['label'])
                            ])) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </main>

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

            // Close dropdown if clicked elsewhere
            document.addEventListener('click', function() {
                document.querySelector('.right-buttons').classList.remove('show');
            });

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
        </script>

        <script src="js/chatbot.js"></script>

        <!-- FOOTER -->
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

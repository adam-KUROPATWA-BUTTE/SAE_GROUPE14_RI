<?php

namespace View\WebPlan;

class WebPlanPageAdmin
{
    private array $links;
    private string $lang;

    public function __construct(array $links = [], string $lang = 'fr')
    {
        $this->links = $links;
        $this->lang = $lang;
    }

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    private function buildUrl(string $url): string
    {
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $sep . 'lang=' . urlencode($this->lang);
    }

    public function render(): void
    {
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
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="Logo AMU">
                <div class="right-buttons">
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

        <main>
            <h1><?= $this->t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></h1>
            <ul>
                <?php foreach ($this->links as $link) : ?>
                    <li><a href="<?= htmlspecialchars($this->buildUrl($link['url'])) ?>"><?=
                            htmlspecialchars($this->t([
                                'fr' => $link['label'], // labels en dur en fr dans Model, on peut compléter en dur ici si besoin
                                'en' => $this->translateLabel($link['label'])
                            ]))
                                    ?></a></li>
                <?php endforeach; ?>
            </ul>



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
        </div>

        <script>
            const CHAT_CONFIG = {
                lang: '<?= $this->lang ?>',
                role: '<?= (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'admin' : 'student' ?>'
            };
        </script>
        <script src="js/chatbot.js"></script>
        <script>
            document.getElementById('current-lang').addEventListener('click', function(event) {
                event.stopPropagation();
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.toggle('show');
            });

            document.addEventListener('click', function() {
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.remove('show');
            });

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

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
}
<?php

namespace View\HomePage;

class HomePageStudent
{
    private bool $isLoggedIn;
    private string $lang;

    // Pas de pourcentage global ici, c'est pour l'admin
    public function __construct(bool $isLoggedIn = false, string $lang = 'fr')
    {
        $this->isLoggedIn = $isLoggedIn;
        $this->lang = $lang;
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
                    <?php if ($this->isLoggedIn) : ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('/logout') ?>'">
                            <?= $this->t(['fr' => 'Se déconnecter','en' => 'Log out']) ?>
                        </button>
                    <?php else : ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('/login') ?>'">
                            <?= $this->t(['fr' => 'Se connecter','en' => 'Log in']) ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <button id="theme-toggle" title="Mode tritanopie"><span class="toggle-switch"></span></button>

            <nav class="menu">
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-student') ?>'"><?= $this->t(['fr' => 'Mon Tableau de bord','en' => 'My Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners-student') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-student') ?>'"><?= $this->t(['fr' => 'Mon Dossier','en' => 'My Folder']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-student') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
            </nav>
        </header>

        <section class="hero-section"><img class="hero_logo" src="img/amu.png" alt="Logo AMU"></section>
        
        <section class="pub-section">
            <img id="pub_amu" src="img/pub.jpg" alt="Publicité AMU">
            <div class="pub-text"><?= $this->t([
                    'fr' => '« Bienvenue sur votre portail de mobilité internationale »',
                    'en' => '“Welcome to your international mobility portal”'
                ]) ?></div>
        </section>


        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img class="insta" src="img/instagram.png" alt="Instagram">
            </a>
        </footer>

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
        <script src="js/chatbot.js">
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
            // Ajoutez ici le reste de vos scripts JS existants
        </script>
        </body>
        </html>
        <?php
    }
}
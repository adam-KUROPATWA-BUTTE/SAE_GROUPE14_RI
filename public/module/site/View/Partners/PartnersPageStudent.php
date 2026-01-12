<?php

// phpcs:disable Generic.Files.LineLength

namespace View\Partners;

class PartnersPageStudent
{
    private string $titre;
    private string $lang;

    public function __construct(string $titre, string $lang = 'fr')
    {
        $this->titre = $titre;
        $this->lang = $lang;
    }

    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        return $path . '?' . http_build_query($params);
    }

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    public function render(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = $_GET['tritanopia'] === '1';
        }
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
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
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
            <h1>Universités Partenaires</h1>
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
                 src="img/<?= !empty($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'University_green.png' : 'University.png' ?>"
                 alt="Université partenaires">

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
                event.stopPropagation(); // empêcher la propagation au document
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.toggle('show');
            });

            // Fermer le dropdown si clic ailleurs sur la page
            document.addEventListener('click', function() {
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.remove('show');
            });

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

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

<?php

// phpcs:disable Generic.Files.LineLength

namespace View\Partners;

/**
 * Class PartnersPageAdmin
 *
 * Admin view for managing AMU partner universities.
 * Displays the list of partners, a form to add new partners,
 * language selector, accessibility toggle (tritanopia), and chatbot.
 */
class PartnersPageAdmin
{
    /** @var string Page title */
    private string $titre;

    /** @var string Current language ('fr' or 'en') */
    private string $lang;

    /** @var string|null Optional error message */
    public ?string $errorMessage = null;

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
     * Render the admin partners page HTML.
     *
     * @return void
     */
    public function render(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle tritanopia (color-blind) mode
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
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'tritanopie' : '' ?>">

        <!-- HEADER -->
        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="Logo">
                <div class="right-buttons">
                    <!-- Language selector -->
                    <div class="lang-dropdown">
                        <button class="dropbtn" id="current-lang"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation menu -->
            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-admin') ?>'"><?= $this->t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/partners-admin') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'"><?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-admin') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
            </nav>
        </header>

        <!-- MAIN CONTENT -->
        <main>
            <h1><?= htmlspecialchars($this->titre) ?></h1>
            <?php if (isset($_GET['success'])) : ?>
                <p id="success-message" class="success-message">
                    <?= $this->t(['fr' => 'Partenaire ajouté avec succès.', 'en' => 'Partner successfully added.']) ?>
                </p>
            <?php elseif (!empty($this->errorMessage)) : ?>
                <p class="error-message"><?= htmlspecialchars($this->errorMessage) ?></p>
            <?php endif; ?>

            <!-- Add partner form -->
            <div class="partners-actions">
                <button class="btn-add-partner">
                    <span class="btn-plus">+</span>
                    <?= $this->t(['fr' => 'Ajouter', 'en' => 'Add']) ?>
                </button>
                <div id="partner-form-container" class="partner-form hidden">
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="name"><?= $this->t(['fr' => 'Continent', 'en' => 'Continent']) ?></label>
                            <input type="text" id="name" name="name" required placeholder="<?= $this->t(['fr' => 'Ex: Europe', 'en' => 'Ex: Europe']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="country"><?= $this->t(['fr' => 'Pays', 'en' => 'Country']) ?></label>
                            <input type="text" id="country" name="country" required placeholder="<?= $this->t(['fr' => 'Ex: France', 'en' => 'Ex: France']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="city"><?= $this->t(['fr' => 'Ville', 'en' => 'City']) ?></label>
                            <input type="text" id="city" name="city" required placeholder="<?= $this->t(['fr' => 'Ex: Marseille', 'en' => 'Ex: Marseille']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="institution"><?= $this->t(['fr' => 'Universités et institutions', 'en' => 'Universities and institutions']) ?></label>
                            <input type="text" id="institution" name="institution" required placeholder="<?= $this->t(['fr' => 'Ex: Aix-Marseille Université', 'en' => 'Ex: Aix-Marseille University']) ?>">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save"><?= $this->t(['fr' => 'Enregistrer', 'en' => 'Save']) ?></button>
                            <button type="button" class="btn-cancel"><?= $this->t(['fr' => 'Annuler', 'en' => 'Cancel']) ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- External partner list link -->
            <p><?= $this->t([
                    'fr' => 'Veuillez trouver la liste des partenaires d’AMU en cliquant sur ce lien :',
                    'en' => 'Please find the list of AMU\'s partners by clicking on this link:'
                ]) ?></p>
            <p class="lien">
                <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
                    Universites-et-reseaux-partenaires
                </a>
            </p>

            <!-- Partner image -->
            <img id="Université_partenaires"
                 src="img/<?= !empty($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'University_green.png' : 'University.png' ?>"
                 alt="Partner Universities">

        </main>

        <!-- FOOTER -->
        <footer>
            <p>&copy; 2026 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img class="insta" src="img/instagram.png" alt="Instagram">
            </a>
        </footer>

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

                // Add partner form toggle
                const addPartnerBtn = document.querySelector('.btn-add-partner');
                const partnerForm = document.getElementById('partner-form-container');
                const cancelBtn = document.querySelector('.btn-cancel');

                addPartnerBtn.addEventListener('click', () => {
                    addPartnerBtn.style.display = 'none';
                    partnerForm.classList.remove('hidden');
                });

                cancelBtn.addEventListener('click', () => {
                    partnerForm.classList.add('hidden');
                    addPartnerBtn.style.display = 'inline-flex';
                });

                // Auto-hide success message
                const successMsg = document.getElementById('success-message');
                if (successMsg) {
                    setTimeout(() => {
                        successMsg.style.transition = 'opacity 0.5s ease';
                        successMsg.style.opacity = '0';
                        setTimeout(() => successMsg.remove(), 500);
                    }, 3000);
                }
            });
        </script>

        <script src="js/chatbot.js"></script>

        </body>
        </html>
        <?php
    }
}

<?php

// phpcs:disable Generic.Files.LineLength

namespace View\Partners;

/**
 * Class PartnersPageAdmin
 *
 * Admin view for managing AMU partner universities.
 * Logic is now separated: PHP for structure, JS (partners.js) for interactivity.
 */
class PartnersPageAdmin
{
    private string $titre;
    private string $lang;
    public ?string $errorMessage = null;

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

        if (isset($_GET['lang'])) {
            $langParam = strval($_GET['lang']);
            if (in_array($langParam, ['fr', 'en'], true)) {
                $_SESSION['lang'] = $langParam;
            }
        }
        $this->lang = isset($_SESSION['lang']) ? strval($_SESSION['lang']) : 'fr';

        if (isset($_GET['tritanopia'])) {
            $tritaParam = strval($_GET['tritanopia']);
            $_SESSION['tritanopia'] = ($tritaParam === '1');
        }
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
                            <a href="#">Français</a>
                            <a href="#">English</a>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-admin') ?>'"><?= $this->t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/partners-admin') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'"><?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-admin') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
            </nav>
        </header>

        <main>
            <h1><?= htmlspecialchars($this->titre) ?></h1>
            <?php if (isset($_GET['success'])) : ?>
                <p id="success-message" class="success-message">
                    <?= $this->t(['fr' => 'Partenaire ajouté avec succès.', 'en' => 'Partner successfully added.']) ?>
                </p>
            <?php elseif (!empty($this->errorMessage)) : ?>
                <p class="error-message"><?= htmlspecialchars($this->errorMessage) ?></p>
            <?php endif; ?>

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

            <p><?= $this->t([
                    'fr' => 'Veuillez trouver la liste des partenaires d’AMU en cliquant sur ce lien :',
                    'en' => 'Please find the list of AMU\'s partners by clicking on this link:'
                ]) ?></p>
            <p class="lien">
                <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
                    Universites-et-reseaux-partenaires
                </a>
            </p>

            <img id="Université_partenaires"
                 src="img/<?= $isTritanopia ? 'University_green.png' : 'University.png' ?>"
                 alt="Partner Universities">

        </main>

        <footer>
            <p>&copy; 2026 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img class="insta" src="img/instagram.png" alt="Instagram">
            </a>
        </footer>

        <div id="help-bubble">💬</div>
        <div id="help-popup" class="chat-popup">
            <div class="help-popup-header">
                <span>Assistant</span>
                <button>✖</button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div id="quick-actions" class="quick-actions"></div>
        </div>

        <div id="app-config" 
             data-lang="<?= htmlspecialchars($this->lang) ?>" 
             data-role="admin"
             style="display:none;">
        </div>

        <script src="js/main.js"></script>
        <script src="js/chatbot.js"></script>
        <script src="js/partners.js"></script>

        </body>
        </html>
        <?php
    }
}
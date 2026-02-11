<?php

// phpcs:disable Generic.Files.LineLength

namespace View\WebPlan;

/**
 * Class WebPlanPageStudent
 * * Logic is now separated: PHP for structure, JS (main.js) for interactivity.
 */
class WebPlanPageStudent
{
    /** @var array<int, array{url: string, label: string}> */
    private array $links;
    private string $lang;

    /**
     * @param array<int, array{url: string, label: string}> $links
     */
    public function __construct(array $links = [], string $lang = 'fr')
    {
        $this->links = $links;
        $this->lang = $lang;
    }

    /**
     * @param array{fr: string, en: string} $frEn
     */
    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    private function buildUrl(string $url): string
    {
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $sep . 'lang=' . urlencode($this->lang);
    }

    private function translateLabel(string $label): string
    {
        $map = [
            'Accueil' => 'Home',
            'Mon Tableau de bord' => 'My Dashboard',
            'Partenaires' => 'Partners',
            'Mon Dossier' => 'My Folder',
            'Connexion / Inscription' => 'Login / Register',
        ];
        return $map[$label] ?? $label;
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
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/web_plan.css">
            <link rel="stylesheet" href="styles/chatbot.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
            <title><?= $this->t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></title>
        </head>
        <body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="Logo AMU">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#">Français</a>
                            <a href="#">English</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <h1><?= $this->t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></h1>
            <ul>
                <?php foreach ($this->links as $link) :
                    $url = strval($link['url']);
                    $label = strval($link['label']);
                    ?>
                    <li>
                        <a href="<?= htmlspecialchars($this->buildUrl($url)) ?>">
                            <?= htmlspecialchars($this->t([
                                'fr' => $label,
                                'en' => $this->translateLabel($label)
                            ])) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </main>

        <div id="help-bubble">💬</div>
        <div id="help-popup" class="chat-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr' => 'Assistant', 'en' => 'Assistant']) ?></span>
                <button>✖</button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div id="quick-actions" class="quick-actions"></div>
        </div>

        <div id="app-config" 
             data-lang="<?= htmlspecialchars($this->lang) ?>" 
             data-role="student"
             style="display:none;">
        </div>

        <script src="js/main.js"></script>
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
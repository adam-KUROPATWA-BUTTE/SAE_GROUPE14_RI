<?php

// phpcs:disable Generic.Files.LineLength

namespace View\WebPlan;

/**
 * Class WebPlanPageStudent
 */
class WebPlanPageStudent
{
    /** @var array<int, array{url: string, label: string}> List of links */
    private array $links;

    /** @var string Current language */
    private string $lang;

    /**
     * @param array<int, array{url: string, label: string}> $links
     * @param string                                        $lang
     */
    public function __construct(array $links = [], string $lang = 'fr')
    {
        $this->links = $links;
        $this->lang = $lang;
    }

    /** @param array{fr: string, en: string} $frEn */
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
                <?php foreach ($this->links as $link) :
                    // Correction Level 9: Direct access without ??
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

        <div id="help-bubble" onclick="toggleHelpPopup()">💬</div>
        <div id="help-popup" class="chat-popup">
            <div class="help-popup-header"><span>Assistant</span><button onclick="toggleHelpPopup()">✖</button></div>
            <div id="chat-messages" class="chat-messages"></div>
        </div>

        <script>
            const CHAT_CONFIG = { lang: '<?= $this->lang ?>', role: 'student' };
            document.getElementById('current-lang').addEventListener('click', function(e) {
                e.stopPropagation(); document.querySelector('.right-buttons').classList.toggle('show');
            });
            document.addEventListener('click', function() {
                document.querySelector('.right-buttons').classList.remove('show');
            });
            function changeLang(l) {
                const u = new URL(window.location.href); u.searchParams.set('lang', l); window.location.href = u.toString();
            }
        </script>
        <script src="js/chatbot.js"></script>
        <footer><p>&copy; 2026 - Aix-Marseille Université.</p></footer>
        </body>
        </html>
        <?php
    }
}
<?php

// phpcs:disable Generic.Files.LineLength

namespace View\Dashboard;

/**
 * Class DashboardPageStudent
 */
class DashboardPageStudent
{
    /** @var array<string, mixed> */
    private array $dossier;

    /** @var string */
    private string $lang;

    /**
     * @param array<string, mixed>|null $dossier
     * @param string                    $lang
     */
    public function __construct(?array $dossier = null, string $lang = 'fr')
    {
        $this->dossier = $dossier ?? [];
        $this->lang = $lang;
    }

    /**
     * Builds a URL safely handling query parameters.
     *
     * @param string               $path   Base path (e.g., 'index.php').
     * @param array<string, mixed> $params Associative array of query parameters.
     * @return string The constructed URL.
     */
    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        $separator = (strpos($path, '?') === false) ? '?' : '&';
        return $path . $separator . http_build_query($params);
    }

    /**
     * @param array{fr: string, en: string} $frEn
     */
    private function t(array $frEn): string
    {
        return ($this->lang === 'en') ? $frEn['en'] : $frEn['fr'];
    }

    public function render(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $status = strval($this->dossier['status'] ?? 'depot');
        $steps = ['depot', 'instruction', 'decision'];

        $currentStepIndex = array_search($status, $steps, true);
        if ($currentStepIndex === false) {
            $currentStepIndex = 0;
        }

        $totalSteps = count($steps); // Always 3
        $currentStepInt = (int)$currentStepIndex;

        // Correction Level 9: Removed useless condition ($totalSteps > 1) as 3 > 1 is strictly true.
        $progressPercentage = ($currentStepInt / ($totalSteps - 1)) * 100;
        $progressStyle = "width: {$progressPercentage}%;";

        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->t(['fr' => 'Tableau de bord (Étudiant)', 'en' => 'Student Dashboard']) ?></title>
            <link rel="stylesheet" href="styles/dashboard.css">
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/chatbot.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body class="<?= (isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true) ? 'tritanopie' : '' ?>">
        
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo AMU" class="logo_amu">
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
            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'home-student']) ?>'">
                    <?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?>
                </button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'dashboard-student']) ?>'">
                    <?= $this->t(['fr' => 'Mon Tableau de bord','en' => 'My Dashboard']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'partners-student']) ?>'">
                    <?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-student']) ?>'">
                    <?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'web_plan-student']) ?>'">
                    <?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?>
                </button>
            </nav>
        </header>

        <main>
            <h1><?= $this->t(['fr' => 'Suivi du dossier', 'en' => 'File Tracking']) ?></h1>

            <div class="progress-container">
                <div class="progress-line" style="<?= $progressStyle ?>"></div>

                <div class="progress-step <?= in_array($status, ['depot', 'instruction', 'decision'], true) ? 'active' : '' ?>">
                    <div class="progress-icon"><img src="img/depot.png" alt="Dépôt"></div>
                    <div class="progress-circle"></div>
                    <span><?= $this->t(['fr' => 'Dépôt de la demande', 'en' => 'Application Submitted']) ?></span>
                </div>

                <div class="progress-step <?= in_array($status, ['instruction', 'decision'], true) ? 'active' : '' ?>">
                    <div class="progress-icon"><img src="img/rafraichir.png" alt="Instruction"></div>
                    <div class="progress-circle"></div>
                    <span><?= $this->t(['fr' => 'Instruction en cours', 'en' => 'Under Review']) ?></span>
                </div>

                <div class="progress-step <?= $status === 'decision' ? 'active' : '' ?>">
                    <div class="progress-icon"><img src="img/decision.png" alt="Décision"></div>
                    <div class="progress-circle"></div>
                    <span><?= $this->t(['fr' => 'Décision prise', 'en' => 'Decision Made']) ?></span>
                </div>
            </div>

            <div class="contact-info-box">
                <p class="contact-title"><?= $this->t(['fr' => 'Une question ou besoin d’assistance ?', 'en' => 'A question or need assistance?']) ?></p>
                <p><?= $this->t(['fr' => 'Pour toute information complémentaire...', 'en' => 'For any additional information...']) ?></p>
                <p class="contact-email"><a href="mailto:relations.internationale@amu-univ.fr">relations.internationale@amu-univ.fr</a></p>
            </div>
        </main>

        <div id="help-bubble" onclick="toggleHelpPopup()">💬</div>
        <div id="help-popup" class="chat-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr' => 'Assistant', 'en' => 'Assistant']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div id="quick-actions" class="quick-actions"></div>
        </div>

        <script>
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
        </script>
        
        <script>
            const CHAT_CONFIG = {
                lang: '<?= $this->lang ?>',
                role: 'student'
            };
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
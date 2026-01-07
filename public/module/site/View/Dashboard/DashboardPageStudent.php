<?php

namespace View\Dashboard;

/**
 * Class DashboardPageStudent
 *
 * View responsible for displaying the student dashboard.
 * It visualizes the progress of the student's file (Depot -> Instruction -> Decision).
 */
class DashboardPageStudent
{
    private array $dossier;
    private string $lang;

    /**
     * Constructor.
     *
     * @param array|null $dossier Data regarding the student's folder (status, etc.).
     * @param string     $lang    Current language ('fr' or 'en').
     */
    public function __construct(?array $dossier = null, string $lang = 'fr')
    {
        $this->dossier = $dossier ?? [];
        $this->lang = $lang;
    }

    /**
     * Builds a URL safely handling query parameters.
     *
     * @param string $path   Base path (usually 'index.php').
     * @param array  $params Associative array of query parameters.
     * @return string The constructed URL.
     */
    private function buildUrl(string $path, array $params = []): string
    {
        // Add language to parameters
        $params['lang'] = $this->lang;

        // Determine separator based on whether the path already has a query string
        $separator = (strpos($path, '?') === false) ? '?' : '&';

        return $path . $separator . http_build_query($params);
    }

    /**
     * Translates a string based on the current language.
     *
     * @param array $frEn Array ['fr' => '...', 'en' => '...'].
     * @return string The translated string.
     */
    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    /**
     * Renders the HTML content of the dashboard.
     */
    public function render(): void
    {
        // Determine current status and progress percentage
        $status = $this->dossier['status'] ?? 'depot';
        $steps = ['depot', 'instruction', 'decision'];
        
        // Find the index of the current status (0, 1, or 2)
        $currentStepIndex = array_search($status, $steps);
        if ($currentStepIndex === false) {
            $currentStepIndex = 0; // Default to first step if unknown
        }

        // Calculate CSS width for the progress bar line
        $progressWidth = ($currentStepIndex / (count($steps) - 1)) * 100 . '%';
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->lang === 'en' ? 'Student Dashboard' : 'Tableau de bord (Étudiant)' ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/dashboard.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo" style="height:80px;">
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
                <button onclick="window.location.href='<?= $this->buildUrl('index.php?page=home') ?>'">
                    <?= $this->t(['fr'=>'Accueil','en'=>'Home']) ?>
                </button>

                <button class="active"
                        onclick="window.location.href='<?= $this->buildUrl('index.php?page=dashboard-student') ?>'">
                    <?= $this->t(['fr'=>'Tableau de bord','en'=>'Dashboard']) ?>
                </button>

                <button onclick="window.location.href='<?= $this->buildUrl('index.php?page=partners') ?>'">
                    <?= $this->t(['fr'=>'Partenaires','en'=>'Partners']) ?>
                </button>

                <button onclick="window.location.href='<?= $this->buildUrl('index.php?page=folders-student') ?>'">
                    <?= $this->t(['fr'=>'Dossiers','en'=>'Folders']) ?>
                </button>

                <button onclick="window.location.href='<?= $this->buildUrl('index.php?page=web_plan') ?>'">
                    <?= $this->t(['fr'=>'Plan du site','en'=>'Site Map']) ?>
                </button>
            </nav>


        </header>

        <main>
            <h1 style="text-align:center; margin-top:40px;">
                <?= $this->t(['fr' => 'Suivi du dossier', 'en' => 'File Tracking']) ?>
            </h1>

            <div class="progress-container">
                <div class="progress-line" style="width: <?= $progressWidth ?>;"></div>

                <div class="progress-step <?= in_array($status, ['depot', 'instruction', 'decision']) ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/depot.png" alt="Dépôt">
                    </div>
                    <span><?= $this->t(['fr' => 'Dépôt de la demande', 'en' => 'Application Submitted']) ?></span>
                </div>

                <div class="progress-step <?= in_array($status, ['instruction', 'decision']) ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/rafraichir.png" alt="Instruction">
                    </div>
                    <span><?= $this->t(['fr' => 'Instruction en cours', 'en' => 'Under Review']) ?></span>
                </div>

                <div class="progress-step <?= $status === 'decision' ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/decision.png" alt="Décision">
                    </div>
                    <span><?= $this->t(['fr' => 'Décision prise', 'en' => 'Decision Made']) ?></span>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img class="insta" src="img/instagram.png" alt="Instagram">
            </a>
            
            <script>
                /**
                 * Helper to switch language by reloading the page with new query param.
                 */
                function changeLang(lang) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('lang', lang);
                    window.location.href = url.toString();
                }
            </script>
        </body>
        </html>
        <?php
    }
}
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Determine current status and progress percentage
        $status = $this->dossier['status'] ?? 'depot';
        $steps = ['depot', 'instruction', 'decision'];

        // Find the index of the current status (0, 1, or 2)
        $currentStepIndex = array_search($status, $steps);
        if ($currentStepIndex === false) {
            $currentStepIndex = 0; // Default to first step if unknown
        }

        // Calculate CSS width for the progress bar line
        $progressWidth = ($currentStepIndex / (count($steps) - 1)) * (100 * 2 / 3) . '%';
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
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
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
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('index.php?page=dashboard-student') ?>'"><?= $this->t(['fr' => 'Mon Tableau de bord','en' => 'My Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners-student') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-student') ?>'"><?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-student') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
            </nav>



        </header>

        <main>

            <h1>
                <?= $this->t(['fr' => 'Suivi du dossier', 'en' => 'File Tracking']) ?>
            </h1>

            <div class="progress-container">
                <div class="progress-line" <?= $progressWidth ?>></div>

                <div class="progress-step <?= in_array($status, ['depot', 'instruction', 'decision']) ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/depot.png" alt="Dépôt">
                    </div>
                    <div class="progress-circle"></div>
                    <span><?= $this->t(['fr' => 'Dépôt de la demande', 'en' => 'Application Submitted']) ?></span>
                </div>

                <div class="progress-step <?= in_array($status, ['instruction', 'decision']) ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/rafraichir.png" alt="Instruction">
                    </div>
                    <div class="progress-circle"></div>
                    <span><?= $this->t(['fr' => 'Instruction en cours', 'en' => 'Under Review']) ?></span>
                </div>

                <div class="progress-step <?= $status === 'decision' ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/decision.png" alt="Décision">
                    </div>
                    <div class="progress-circle"></div>
                    <span><?= $this->t(['fr' => 'Décision prise', 'en' => 'Decision Made']) ?></span>
                </div>
            </div>
            <div class="contact-info-box">
                <p class="contact-title">
                    <?= $this->t([
                        'fr' => 'Une question ou besoin d’assistance ?',
                        'en' => 'A question or need assistance?'
                    ]) ?>
                </p>

                <p>
                    <?= $this->t([
                        'fr' => 'Pour toute information complémentaire concernant votre dossier, vous pouvez contacter le service des relations internationales à l’adresse suivante :',
                        'en' => 'For any additional information regarding your application, you may contact the International Relations Office at the following address:'
                    ]) ?>
                </p>

                <p class="contact-email">
                    <a href="mailto:relations.internationale@amu-univ.fr">
                        relations.internationale@amu-univ.fr
                    </a>
                </p>
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
                /**
                 * Helper to switch language by reloading the page with new query param.
                 */
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
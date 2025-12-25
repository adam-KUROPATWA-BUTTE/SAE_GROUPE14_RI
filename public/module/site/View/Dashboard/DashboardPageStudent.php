<?php
namespace View\Dashboard;

class DashboardPageStudent
{
    private $dossier;
    private string $lang;

    public function __construct(?array $dossier = null, string $lang = 'fr')
    {
        $this->dossier = $dossier ?? [];
        $this->lang = $lang;
    }

    private function buildUrl(string $path): string
    {
        return $path . '?lang=' . urlencode($this->lang);
    }

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    public function render(): void
    {
        $status = $this->dossier['status'] ?? 'depot';
        $steps = ['depot', 'instruction', 'decision'];
        $currentStepIndex = array_search($status, $steps);
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
                <?= $this->lang === 'en' ? 'File Tracking' : 'Suivi du dossier ' ?>
            </h1>

            <div class="progress-container">
                <div class="progress-line" style="width: <?= $progressWidth ?>;"></div>

                <div class="progress-step <?= in_array($status, ['depot', 'instruction', 'decision']) ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/depot.png" alt="Dépôt">
                    </div>
                    <span>Dépôt de la demande</span>
                </div>

                <div class="progress-step <?= in_array($status, ['instruction', 'decision']) ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/rafraichir.png" alt="Instruction">
                    </div>
                    <span>Instruction en cours</span>
                </div>

                <div class="progress-step <?= $status === 'decision' ? 'active' : '' ?>">
                    <div class="progress-icon">
                        <img src="/img/decision.png" alt="Décision">
                    </div>
                    <span>Décision prise</span>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img class="insta" src="img/instagram.png" alt="Instagram">
            </a>
        <script>
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

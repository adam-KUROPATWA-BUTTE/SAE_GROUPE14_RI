<?php
namespace View;

class SettingsPage
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
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($this->titre) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/settings.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img id="logo_amu" src="img/logo.png" alt="Logo" style="height:100px;">
                <div class="right-buttons">
                    <div class="lang-dropdown" style="float:right; margin-top: 30px; margin-right: 20px;">
                        <button class="dropbtn" id="current-lang"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'">
                    <?= $this->t(['fr'=>'Accueil','en'=>'Home']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard') ?>'">
                    <?= $this->t(['fr'=>'Tableau de bord','en'=>'Dashboard']) ?>
                </button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/settings') ?>'">
                    <?= $this->t(['fr'=>'Paramétrage','en'=>'Settings']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders') ?>'">
                    <?= $this->t(['fr'=>'Dossiers','en'=>'Folders']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'">
                    <?= $this->t(['fr'=>'Plan du site','en'=>'Site Map']) ?>
                </button>
            </nav>
        </header>

        <main>
            <h1><?= htmlspecialchars($this->titre) ?></h1>

            <p>
                <?= $this->t([
                    'fr' => 'Veuillez trouver la liste des partenaires d’AMU en cliquant sur ce lien :',
                    'en' => 'Please find the list of AMU\'s partners by clicking on this link:'
                ]) ?>
            </p>
            <p>
                <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
                    Universites-et-reseaux-partenaires
                </a>
            </p>
        </main>

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
        </footer>

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

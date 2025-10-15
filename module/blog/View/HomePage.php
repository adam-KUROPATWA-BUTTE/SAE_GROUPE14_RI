<?php
namespace View;

class HomePage
{
    private bool $isLoggedIn;
    private string $lang;

    public function __construct(bool $isLoggedIn = false, string $lang = 'fr')
    {
        $this->isLoggedIn = $isLoggedIn;
        $this->lang = $lang;
    }

    // Méthode helper pour construire les URLs avec param lang
    private function buildUrl(string $path): string
    {
        return $path . '?lang=' . urlencode($this->lang);
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title><?= $this->lang === 'en' ? 'Home - AMU International Relations' : 'Accueil - Relations Internationales AMU' ?></title>
            <link rel="stylesheet" href="styles/index.css" />
            <link rel="icon" type="image/png" href="img/favicon.webp" />
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo AMU" style="height:100px;">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn" id="current-lang"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                    <?php if ($this->isLoggedIn): ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('/logout') ?>'">
                            <?= $this->lang === 'en' ? 'Log out' : 'Se déconnecter' ?>
                        </button>
                    <?php else: ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('/login') ?>'">
                            <?= $this->lang === 'en' ? 'Log in' : 'Se connecter' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="menu">
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/') ?>'">
                    <?= $this->lang === 'en' ? 'Home' : 'Accueil' ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard') ?>'">
                    <?= $this->lang === 'en' ? 'Dashboard' : 'Tableau de bord' ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/settings') ?>'">
                    <?= $this->lang === 'en' ? 'Settings' : 'Paramétrage' ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders') ?>'">
                    <?= $this->lang === 'en' ? 'Folders' : 'Dossiers' ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'">
                    <?= $this->lang === 'en' ? 'Site Map' : 'Plan du site' ?>
                </button>
            </nav>
        </header>

        <main>
            <h1><?= $this->lang === 'en' ? 'Welcome to AMU International Relations' : 'Bienvenue au Service des relations internationales AMU' ?></h1>
            <!-- Ajoute ici le contenu principal de la homepage -->
        </main>

        <script>
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
        </script>

        <footer>
            <p>&copy; 2025 - <?= $this->lang === 'en' ? 'Aix-Marseille University' : 'Aix-Marseille Université' ?>.</p>
        </footer>
        </body>
        </html>
        <?php
    }
}

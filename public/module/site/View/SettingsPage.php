<?php
namespace View;

class SettingsPage
{
    private string $titre;
    private array $data;
    private string $lang;

    public function __construct(string $titre, array $data, string $lang = 'fr')
    {
        $this->titre = $titre;
        $this->data = $data;
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
                <img id="logo_amu" src="img/logo.png" alt="Logo">
                <div class="right-buttons">
                    <div class="lang-dropdown">
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

            <div class="sub-menu">
                <a href="<?= $this->buildUrl('index.php', ['page'=>'settings', 'type'=>'universites']) ?>">
                    <?= $this->t(['fr'=>'Universités','en'=>'Universities']) ?>
                </a>
                <a href="<?= $this->buildUrl('index.php', ['page'=>'settings', 'type'=>'campagnes']) ?>">
                    <?= $this->t(['fr'=>'Campagnes','en'=>'Campaigns']) ?>
                </a>
                <a href="<?= $this->buildUrl('index.php', ['page'=>'settings', 'type'=>'partenaires']) ?>">
                    <?= $this->t(['fr'=>'Partenaires','en'=>'Partners']) ?>
                </a>
                <a href="<?= $this->buildUrl('index.php', ['page'=>'settings', 'type'=>'destinations']) ?>">
                    <?= $this->t(['fr'=>'Destinations','en'=>'Destinations']) ?>
                </a>
            </div>

            <table>
                <thead>
                <tr>
                    <?php if ($this->titre === $this->t(['fr'=>'Paramétrage','en'=>'Settings'])): ?>
                        <th>Code</th><th><?= $this->t(['fr'=>'Université','en'=>'University']) ?></th><th><?= $this->t(['fr'=>'Pays','en'=>'Country']) ?></th><th><?= $this->t(['fr'=>'Partenaire','en'=>'Partner']) ?></th>
                    <?php elseif ($this->titre === $this->t(['fr'=>'Campagnes','en'=>'Campaigns'])): ?>
                        <th>Code</th><th><?= $this->t(['fr'=>'Nom','en'=>'Name']) ?></th><th><?= $this->t(['fr'=>'Statut','en'=>'Status']) ?></th>
                    <?php elseif ($this->titre === $this->t(['fr'=>'Partenaires','en'=>'Partners'])): ?>
                        <th><?= $this->t(['fr'=>'Nos partenaires','en'=>'Our Partners']) ?></th><th><?= $this->t(['fr'=>'Cadre','en'=>'Framework']) ?></th>
                    <?php elseif ($this->titre === $this->t(['fr'=>'Destinations','en'=>'Destinations'])): ?>
                        <th>Code</th><th><?= $this->t(['fr'=>'Université','en'=>'University']) ?></th><th><?= $this->t(['fr'=>'Pays','en'=>'Country']) ?></th><th><?= $this->t(['fr'=>'Partenaire','en'=>'Partner']) ?></th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->data as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </main>

        <!-- Bulle d'aide en bas à droite -->
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>

        <!-- Contenu du popup d'aide -->
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr'=>'Aide','en'=>'Help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->t(['fr'=>'Bienvenue ! Comment pouvons-nous vous aider ?','en'=>'Welcome! How can we help you?']) ?></p>
                <ul>
                    <li><a id="page_complete" href="index.php?page=help" target="_blank"><?= $this->t(['fr'=>'Page d’aide complète','en'=>'Full help page']) ?></a></li>
                </ul>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const menuToggle = document.createElement('button');
                menuToggle.classList.add('menu-toggle');
                menuToggle.innerHTML = '☰';
                document.querySelector('.right-buttons').appendChild(menuToggle);

                const navMenu = document.querySelector('nav.menu');
                menuToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });
            });
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
            }
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
        </script>

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
        </footer>
        </body>
        </html>
        <?php
    }
}
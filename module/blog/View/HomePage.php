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

    public function render(): void
    {
        // Texte traduits basiques (à améliorer selon besoins)
        $texts = [
            'fr' => [
                'lang_label' => 'fr',
                'complétude' => 'Complétude des dossiers',
                'complet' => 'Complet',
                'incomplet' => 'Incomplet',
                'help_welcome' => 'Bienvenue ! Comment pouvons-nous vous aider ?',
                'help_page' => 'Page d’aide complète',
                'login' => 'Se connecter',
                'logout' => 'Se déconnecter',
                'home' => 'Accueil',
                'dashboard' => 'Tableau de bord',
                'settings' => 'Paramétrage',
                'folders' => 'Dossiers',
                'site_map' => 'Plan du site',
                'help' => 'Aide',
                'ad_text' => '“Aix-Marseille Université, une université ouverte sur le monde”',
                'copyright' => '© 2025 - Aix-Marseille Université.'
            ],
            'en' => [
                'lang_label' => 'en',
                'complétude' => 'Files completeness',
                'complet' => 'Complete',
                'incomplet' => 'Incomplete',
                'help_welcome' => 'Welcome! How can we help you?',
                'help_page' => 'Full help page',
                'login' => 'Log in',
                'logout' => 'Log out',
                'home' => 'Home',
                'dashboard' => 'Dashboard',
                'settings' => 'Settings',
                'folders' => 'Folders',
                'site_map' => 'Site map',
                'help' => 'Help',
                'ad_text' => '“Aix-Marseille University, a university open to the world”',
                'copyright' => '© 2025 - Aix-Marseille University.'
            ],
        ];

        $t = $texts[$this->lang] ?? $texts['fr'];
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="Service des relations internationales d'Aix-Marseille Université.">
            <meta name="keywords" content="Aix-Marseille Université, AMU, Relations Internationales, Étudiants internationaux, Échanges universitaires">
            <meta name="author" content="Groupe 14 - SAE 2024">
            <title><?= htmlspecialchars($t['home']) ?> - Service des relations internationales AMU</title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>

        <body>
        <!-- Header -->
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo AMU" style="height:100px;">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn" id="current-lang"><?= htmlspecialchars($t['lang_label']) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                    <?php if ($this->isLoggedIn): ?>
                        <button onclick="window.location.href='index.php?page=logout'"><?= htmlspecialchars($t['logout']) ?></button>
                    <?php else: ?>
                        <button onclick="window.location.href='index.php?page=login'"><?= htmlspecialchars($t['login']) ?></button>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="menu">
                <button class="active" onclick="window.location.href='index.php'"><?= htmlspecialchars($t['home']) ?></button>
                <button onclick="window.location.href='/dashboard'"><?= htmlspecialchars($t['dashboard']) ?></button>
                <button onclick="window.location.href='/settings'"><?= htmlspecialchars($t['settings']) ?></button>
                <button onclick="window.location.href='/folders'"><?= htmlspecialchars($t['folders']) ?></button>
                <button onclick="window.location.href='/help'"><?= htmlspecialchars($t['help']) ?></button>
                <button onclick="window.location.href='/web_plan'"><?= htmlspecialchars($t['site_map']) ?></button>
            </nav>
        </header>

        <section class="hero-section">
            <img src="img/amu.png" alt="Logo AMU" style="height:80px; position:absolute; top:20px; left:20px;">
        </section>

        <section class="pub-section">
            <img src="img/pub.jpg" alt="Publicité AMU">
            <div class="pub-text"><?= htmlspecialchars($t['ad_text']) ?></div>
        </section>

        <main>
            <div class="dashboard-container">
                <div class="card">
                    <h2><?= htmlspecialchars($t['complétude']) ?></h2>
                    <div class="legend">
                        <div class="legend-item">
                            <span class="legend-color complet"></span> <?= htmlspecialchars($t['complet']) ?>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color incomplet"></span> <?= htmlspecialchars($t['incomplet']) ?>
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="donut-chart">
                            <svg width="300" height="300">
                                <circle r="130" cx="150" cy="150" fill="transparent" stroke="#EBC55E" stroke-width="40"></circle>
                                <circle r="130" cx="150" cy="150" fill="transparent" stroke="#2B91BB" stroke-width="40"
                                        stroke-dasharray="0 880" stroke-linecap="round"></circle>
                            </svg>
                            <div class="chart-center">
                                <div class="chart-percentage">0%</div>
                                <div class="chart-label"><?= htmlspecialchars($t['complet']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bulle d'aide en bas à droite -->
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>

        <!-- Contenu du popup d'aide -->
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= htmlspecialchars($t['help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= htmlspecialchars($t['help_welcome']) ?></p>
                <ul>
                    <li><a href="index.php?page=help" target="_blank"><?= htmlspecialchars($t['help_page']) ?></a></li>
                </ul>
            </div>
        </div>

        <script>
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
            }
        </script>

        <footer>
            <p><?= htmlspecialchars($t['copyright']) ?></p>
        </footer>
        </body>
        </html>
        <?php
    }
}

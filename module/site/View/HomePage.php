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

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        return $path . '?' . http_build_query($params);
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="<?= $this->t([
                'fr' => 'Service des relations internationales de l\'AMU. Informations pour étudiants internationaux, échanges universitaires et partenariats.',
                'en' => 'International Relations Service of AMU. Info for international students, university exchanges, and partnerships.'
            ]) ?>">
            <meta name="keywords" content="AMU, international, étudiant, mobilité, Aix-Marseille, université, relations internationales">
            <meta name="author" content="Groupe 14 - SAE 2024">
            <title><?= $this->t([
                'fr' => 'Accueil - Service des relations internationales AMU',
                'en' => 'Home - International Relations Service AMU'
            ]) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>

        <body>
        <header>
            <div class="top-bar">
                <img id="logo_amu" src="img/logo.png" alt="Logo AMU">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>

                    <?php if ($this->isLoggedIn): ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page'=>'logout']) ?>'">
                            <?= $this->t(['fr'=>'Se déconnecter','en'=>'Log out']) ?>
                        </button>
                    <?php else: ?>
                        <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page'=>'login']) ?>'">
                            <?= $this->t(['fr'=>'Se connecter','en'=>'Log in']) ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="menu">
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr'=>'Accueil','en'=>'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard') ?>'"><?= $this->t(['fr'=>'Tableau de bord','en'=>'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/settings') ?>'"><?= $this->t(['fr'=>'Paramétrage','en'=>'Settings']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders') ?>'"><?= $this->t(['fr'=>'Dossiers','en'=>'Files']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'"><?= $this->t(['fr'=>'Plan du site','en'=>'Sitemap']) ?></button>
            </nav>
        </header>

        <section class="hero-section">
            <img src="img/amu.png" alt="Logo AMU" style="height:80px; position:absolute; top:20px; left:20px;">
        </section>

        <section class="pub-section">
            <img src="img/pub.jpg" alt="Publicité AMU">
            <div class="pub-text"><?= $this->t([
                'fr' => '« Aix-Marseille Université, une université ouverte sur le monde »',
                'en' => '“Aix-Marseille University, a university open to the world”'
            ]) ?></div>
        </section>

        <main>
            <div class="dashboard-container">
                <div class="card">
                    <h2><?= $this->t(['fr'=>'Complétude du dossier','en'=>'File Completeness']) ?></h2>

                    <div class="legend">
                        <div class="legend-item">
                            <span class="legend-color complet"></span> <?= $this->t(['fr'=>'Complet','en'=>'Complete']) ?>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color incomplet"></span> <?= $this->t(['fr'=>'Incomplet','en'=>'Incomplete']) ?>
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
                                <div class="chart-label"><?= $this->t(['fr'=>'Complet','en'=>'Complete']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div id="help-bubble" onclick="toggleHelpPopup()" style="position:fixed; bottom:20px; right:20px; cursor:pointer; font-size:2em; z-index:1000;">❓</div>

        <div id="help-popup" style="display:none; position:fixed; bottom:60px; right:20px; width:300px; background:#fff; border:1px solid #ccc; padding:10px; box-shadow: 0 0 10px rgba(0,0,0,0.3); z-index:1001;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ddd; padding-bottom:5px; margin-bottom:10px;">
                <span><?= $this->t(['fr'=>'Aide','en'=>'Help']) ?></span>
                <button onclick="toggleHelpPopup()" style="background:none; border:none; font-size:1.2em; cursor:pointer;">✖</button>
            </div>
            <div>
                <p><?= $this->t(['fr'=>'Bienvenue ! Comment pouvons-nous vous aider ?','en'=>'Welcome! How can we help you?']) ?></p>
                <ul>
                    <li><a href="<?= $this->buildUrl('index.php', ['page'=>'help']) ?>" target="_blank"><?= $this->t(['fr'=>'Page d’aide complète','en'=>'Full help page']) ?></a></li>
                </ul>
            </div>
        </div>

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
        </footer>

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
        </body>
        </html>
        <?php
    }
}

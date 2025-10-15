<?php
namespace View;

class HomePage
{
    private bool $isLoggedIn;

    public function __construct(bool $isLoggedIn = false)
    {
        $this->isLoggedIn = $isLoggedIn;
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="Service des relations internationales d'Aix-Marseille Université.">
            <meta name="keywords" content="Aix-Marseille Université, AMU, Relations Internationales, Étudiants internationaux, Échanges universitaires">
            <meta name="author" content="Groupe 14 - SAE 2024">
            <title>Accueil - Service des relations internationales AMU</title>
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
                        <button class="dropbtn">fr</button>
                        <div class="dropdown-content">
                            <a href="#">Français</a>
                            <a href="#">English</a>
                        </div>
                    </div>
                    <?php if ($this->isLoggedIn): ?>
                        <button onclick="window.location.href='index.php?page=logout'">Se déconnecter</button>
                    <?php else: ?>
                        <button onclick="window.location.href='index.php?page=login'">Se connecter</button>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="menu">
                <button class="active" onclick="window.location.href='/'">Accueil</button>
                <button onclick="window.location.href='/dashboard'">Tableau de bord</button>
                <button onclick="window.location.href='/settings'">Paramétrage</button>
                <button onclick="window.location.href='/folders'">Dossiers</button>
                <button onclick="window.location.href='/web_plan'">Plan du site</button>
            </nav>
        </header>

        <section class="hero-section">
            <img src="img/amu.png" alt="Logo AMU" style="height:80px; position:absolute; top:20px; left:20px;">
        </section>

        <section class="pub-section">
            <img src="img/pub.jpg" alt="Publicité AMU">
            <div class="pub-text">“Aix-Marseille Université, une université ouverte sur le monde”</div>
        </section>

        <main>
            <div class="dashboard-container">
                <div class="card">
                    <h2>Complétude des dossiers</h2>
                    <div class="legend">
                        <div class="legend-item">
                            <span class="legend-color complet"></span> Complet
                        </div>
                        <div class="legend-item">
                            <span class="legend-color incomplet"></span> Incomplet
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
                                <div class="chart-label">Complet</div>
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
                <span>Aide</span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p>Bienvenue ! Comment pouvons-nous vous aider ?</p>
                <ul>
                    <li><a href="index.php?page=help" target="_blank">Page d’aide complète</a></li>
                </ul>
            </div>
        </div>

        <script>
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
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

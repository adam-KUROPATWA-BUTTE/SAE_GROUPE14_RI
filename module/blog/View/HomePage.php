<?php
namespace View;

class HomePage
{
    private bool $isLoggedIn;

    // Constructeur : on passe l'état de connexion
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
        <meta name="description" content="Service des relations internationales d'Aix-Marseille Université. Informations pour les étudiants internationaux, échanges universitaires et partenariats internationaux.">
        <meta name="keywords" content="
            Aix-Marseille Université, AMU, Relations Internationales, Étudiants internationaux, Échanges universitaires,
            Partenariats internationaux, Campus international, Mobilité étudiante, Bourses, Cursus universitaire,
            Études supérieures France, Université française, Admission AMU, Programmes internationaux, Stages internationaux,
            Séjour étudiant, Dossiers étudiants, Assistance administrative, Orientation internationale, Vie étudiante, Recherche académique
        ">
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
                    <!-- Menu langues -->
                    <div class="lang-dropdown">
                        <button class="dropbtn">fr</button>
                        <div class="dropdown-content">
                            <a href="#">Français</a>
                            <a href="#">English</a>
                        </div>
                    </div>

                    <!-- Bouton connexion / déconnexion -->
                    <?php if ($this->isLoggedIn): ?>
                        <button onclick="window.location.href='index.php?page=logout'">Se déconnecter</button>
                    <?php else: ?>
                        <button onclick="window.location.href='index.php?page=login'">Se connecter</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Menu principal -->
            <nav class="menu">
                <button class="active" onclick="window.location.href='index.php'">Accueil</button>
                <button onclick="window.location.href='/dashboard'">Tableau de bord</button>
                <button onclick="window.location.href='/settings'">Paramétrage</button>
                <button onclick="window.location.href='/folders'">Dossiers</button>
                <button onclick="window.location.href='/help'">Aide</button>
                <button onclick="window.location.href='/web_plan'">Plan du site</button>
            </nav>
        </header>

        <!-- Bandeau bleu pleine largeur -->
        <section class="hero-section">
            <img src="img/amu.png" alt="Logo AMU"
                 style="height:80px; position:absolute; top:20px; left:20px;">
        </section>

        <!-- Image pub avec texte par-dessus -->
        <section class="pub-section">
            <img src="img/pub.jpg" alt="Publicité AMU">
            <div class="pub-text">“Aix-Marseille Université, une université ouverte sur le monde”</div>
        </section>

        <!-- Contenu principal -->
        <main>
            <div class="dashboard-container">
                <div class="card">
                    <h2>Complétude des dossiers</h2>

                    <!-- Légende -->
                    <div class="legend">
                        <div class="legend-item">
                            <span class="legend-color complet"></span> Complet
                        </div>
                        <div class="legend-item">
                            <span class="legend-color incomplet"></span> Incomplet
                        </div>
                    </div>

                    <!-- Donut Chart -->
                    <div class="chart-container">
                        <div class="donut-chart">
                            <svg width="300" height="300">
                                <!-- cercle fond jaune (incomplet) -->
                                <circle r="130" cx="150" cy="150" fill="transparent" stroke="#EBC55E" stroke-width="40"></circle>
                                <!-- cercle bleu (complet) -->
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

        <!-- Footer -->
        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
        </footer>
        </body>
        </html>
        <?php
    }
}
<?php
namespace View;

class SettingsPage
{
    private string $titre;
    private array $data;

    public function __construct(string $titre, array $data)
    {
        $this->titre = $titre;
        $this->data = $data;
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($this->titre) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/settings.css"> <!-- fichier CSS dédié -->
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo" style="height:100px;">

            </div>
            <nav class="menu">
                <button onclick="window.location.href='/'">Accueil</button>
                <button onclick="window.location.href='/dashboard'">Tableau de bord</button>
                <button class="active" onclick="window.location.href='settings.php'">Paramètrage</button>
                <button onclick="window.location.href='/folders'">Dossiers</button>
                <button onclick="window.location.href='/web_plan'">Plan du site</button>
            </nav>
        </header>

        <main>
            <h1><?= htmlspecialchars($this->titre) ?></h1>

            <div class="sub-menu">
                <a href="index.php?page=settings&type=universites">Universités</a>
                <a href="index.php?page=settings&type=campagnes">Campagnes</a>
                <a href="index.php?page=settings&type=partenaires">Partenaires</a>
                <a href="index.php?page=settings&type=destinations">Destinations</a>
            </div>

            <table>
                <thead>
                <tr>
                    <?php if ($this->titre === "Paramètrage"): ?>
                        <th>Code</th><th>Université</th><th>Pays</th><th>Partenaire</th>
                    <?php elseif ($this->titre === "Campagnes"): ?>
                        <th>Code</th><th>Nom</th><th>Statut</th>
                    <?php elseif ($this->titre === "Partenaires"): ?>
                        <th>Nos partenaires</th><th>Cadre</th>
                    <?php elseif ($this->titre === "Destinations"): ?>
                        <th>Code</th><th>Université</th><th>Pays</th><th>Partenaire</th>
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

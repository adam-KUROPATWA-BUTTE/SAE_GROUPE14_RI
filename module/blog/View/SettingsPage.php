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
                <button onclick="window.location.href='/index.php?page=home'">Accueil</button>
                <button onclick="window.location.href='/index.php?page=dashboard'">Tableau de bord</button>
                <button class="active" onclick="window.location.href='/index.php?page=settings'">Paramètrage</button>
                <button onclick="window.location.href='/index.php?page=folders'">Dossiers</button>
                <button onclick="window.location.href='/index.php?page=help'">Aide</button>
                <button onclick="window.location.href='/index.php?page=web_plan'">Plan du site</button>
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

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
        </footer>
        </body>
        </html>
        <?php
    }
}

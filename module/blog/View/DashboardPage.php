<?php
namespace View;

class DashboardPage
{
    private array $dossiers;

    public function __construct(array $dossiers = [])
    {
        $this->dossiers = $dossiers;
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tableau de bord</title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo" style="height:100px;">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn">fr</button>
                        <div class="dropdown-content">
                            <a href="#">Français</a>
                            <a href="#">English</a>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="menu">
                <button onclick="window.location.href='/'">Accueil</button>
                <button class="active" onclick="window.location.href='dashboard.php'">Tableau de bord</button>
                <button onclick="window.location.href='/settings'">Paramétrage</button>
                <button onclick="window.location.href='/folders'">Dossiers</button>
                <button onclick="window.location.href='/help'">Aide</button>
                <button onclick="window.location.href='/web_plan'">Plan du site</button>
            </nav>
        </header>

        <main>
            <h1>Dossiers étudiants incomplets</h1>
            <table>
                <thead>
                <tr>
                    <th>NumETu</th>
                    <th>Nom étudiant</th>
                    <th>Prénom étudiant</th>
                    <th>Avancement</th>
                    <th>Pièces fournies</th>
                    <th>Dernière relance</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->dossiers as $dossier):
                    $total = (int)$dossier['total_pieces'];
                    $fournies = (int)$dossier['pieces_fournies'];
                    $pourcentage = $total > 0 ? round(($fournies / $total) * 100) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($dossier['numetu'] ?? '') ?></td>
                        <td><?= htmlspecialchars($dossier['nom']) ?></td>
                        <td><?= htmlspecialchars($dossier['prenom']) ?></td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress" style="width:<?= $pourcentage ?>%"><?= $pourcentage ?>%</div>
                            </div>
                        </td>
                        <td><?= $fournies ?> / <?= $total ?></td>
                        <td><?= htmlspecialchars($dossier['date_derniere_relance']) ?></td>
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
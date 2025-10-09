<?php
namespace View;

class FoldersPage
{
    private array $voeux;
    private string $message;

    public function __construct(array $voeux = [], string $message = '')
    {
        $this->voeux = $voeux;
        $this->message = $message;
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Gestion des dossiers</title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
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
                <button onclick="window.location.href='/settings'">Paramétrage</button>
                <button class="active" onclick="window.location.href='/folders'">Dossiers</button>
                <button onclick="window.location.href='/help'">Aide</button>
                <button onclick="window.location.href='/web_plan'">Plan du site</button>
            </nav>
        </header>

        <main>
            <h1>Fiche Étudiant</h1>

            <?php if (!empty($this->message)): ?>
                <div class="message">
                    <?= htmlspecialchars($this->message) ?>
                </div>
            <?php endif; ?>

            <div class="student-toolbar">
                <div>
                    <label for="search">Rechercher</label>
                    <input type="text" id="search" name="search">
                </div>
            </div>

            <!-- Tableau des dossiers -->
            <h2>Liste des dossiers étudiants</h2>
            <table>
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Total pièces</th>
                    <th>Pièces fournies</th>
                    <th>Dernière relance</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($this->voeux)): ?>
                    <?php foreach ($this->voeux as $voeu): ?>
                        <tr>
                            <td><?= htmlspecialchars($voeu['nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($voeu['prenom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($voeu['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($voeu['total_pieces'] ?? '') ?></td>
                            <td><?= htmlspecialchars($voeu['pieces_fournies'] ?? '') ?></td>
                            <td><?= htmlspecialchars($voeu['date_derniere_relance'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Aucun dossier enregistré</td></tr>
                <?php endif; ?>
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

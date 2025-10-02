<?php

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/folders.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
</head>
<body>
    <header>
        <div class="top-bar">
            <img src="img/logo.png" alt="Logo" style="height:100px;">
            <div class="right-buttons">
                <button>fr</button>
                <button onclick="window.location.href='login.php'">Se connecter</button>
            </div>
        </div>
        <nav class="menu">
            <button onclick="window.location.href='index.php'">Accueil</button>
            <button onclick="window.location.href='dashboard.php'">Tableau de bord</button>
            <button onclick="window.location.href='settings.php'">Paramètrage</button>
            <button onclick="window.location.href='folders.php'">Dossiers</button>
            <button onclick="window.location.href='help.php'">Aide</button>
            <button onclick="window.location.href='web_plan.php'">Plan du site</button>
        </nav>
    </header>
    <main>
        <h1>Gestion des dossiers</h1>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Avancement</th>
                    <th>Pièces fournies</th>
                    <th>Dernière relance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dossiers as $dossier):
                    $total = (int)$dossier['total_pieces'];
                    $fournies = (int)$dossier['pieces_fournies'];
                    $pourcentage = $total > 0 ? round(($fournies / $total) * 100) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($dossier['nom']) ?></td>
                    <td><?= htmlspecialchars($dossier['prenom']) ?></td>
                    <td><?= htmlspecialchars($dossier['email']) ?></td>
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
</body>
</html>
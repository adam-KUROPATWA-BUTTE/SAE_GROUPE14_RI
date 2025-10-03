<?php

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <base href="http://localhost:8080/">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/folders.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
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
            <button onclick="window.location.href='index.php?page=home'">Accueil</button>
            <button onclick="window.location.href='index.php?page=dashboard'">Tableau de bord</button>
            <button onclick="window.location.href='index.php?page=settings'">Paramètrage</button>
            <button onclick="window.location.href='index.php?page=folders'">Dossiers</button>
            <button onclick="window.location.href='index.php?page=help'">Aide</button>
            <button onclick="window.location.href='index.php?page=web_plan'">Plan du site</button>
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
                <?php
                // Données fictives : dossiers incomplets
                $dossiers = [
                    [
                        'numetu' => '22004567',
                        'nom' => 'Dupont',
                        'prenom' => 'Marie',
                        'total_pieces' => 5,
                        'pieces_fournies' => 3,
                        'date_derniere_relance' => '2025-09-25'
                    ],
                    [
                        'numetu' => '22007890',
                        'nom' => 'Nguyen',
                        'prenom' => 'Linh',
                        'total_pieces' => 4,
                        'pieces_fournies' => 2,
                        'date_derniere_relance' => '2025-09-29'
                    ],
                    [
                        'numetu' => '22001234',
                        'nom' => 'Bernard',
                        'prenom' => 'Sophie',
                        'total_pieces' => 6,
                        'pieces_fournies' => 4,
                        'date_derniere_relance' => '2025-09-20'
                    ],
                ];
                foreach ($dossiers as $dossier):
                    $total = (int)$dossier['total_pieces'];
                    $fournies = (int)$dossier['pieces_fournies'];
                    $pourcentage = $total > 0 ? round(($fournies / $total) * 100) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($dossier['numetu']) ?></td>
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
</body>
</html>
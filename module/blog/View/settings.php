<?php ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titre ?></title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2em;
            background: #f9f9f9;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 0.8em;
            text-align: left;
        }
        th {
            background: #007bff;
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f0f4ff;
        }
        .sub-menu {
            margin: 1em 0;
            text-align: center;
        }
        .sub-menu a {
            margin: 0 5px;
            padding: 8px 12px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .sub-menu a:hover {
            background: #0056b3;
        }
    </style>
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
<main style="max-width:700px;margin:2em auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);padding:2em;">

    <h1><?= $titre ?></h1>

    <div class="sub-menu">
        <a href="settings.php?type=universites">Universités</a>
        <a href="settings.php?type=campagnes">Campagnes</a>
        <a href="settings.php?type=partenaires">Partenaires</a>
        <a href="settings.php?type=destinations">Destinations</a>
    </div>

    <table>
        <thead>
        <tr>
            <?php if ($titre === "Paramètrage"): ?>
                <th>Code</th><th>Université</th><th>Pays</th><th>Partenaire</th>
            <?php elseif ($titre === "Campagnes"): ?>
                <th>Code</th><th>Nom</th><th>Statut</th>
            <?php elseif ($titre === "Partenaires"): ?>
                <th>Code</th><th>Nom</th><th>Pays</th>
            <?php elseif ($titre === "Destinations"): ?>
                <th>Code</th><th>Ville</th><th>Pays</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $row): ?>
            <tr>
                <?php foreach ($row as $value): ?>
                    <td><?= htmlspecialchars($value) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>

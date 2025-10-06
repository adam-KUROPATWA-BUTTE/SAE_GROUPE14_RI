<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètrage</title>
    <base href="http://localhost:8080/">
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
            <button onclick="window.location.href='/'">Accueil</button>
            <button onclick="window.location.href='/dashboard'">Tableau de bord</button>
            <button onclick="window.location.href='/settings'">Paramétrage</button>
            <button onclick="window.location.href='/folders'">Dossiers</button>
            <button onclick="window.location.href='/help'">Aide</button>
            <button onclick="window.location.href='/web_plan'">Plan du site</button>

        </nav>
    </header>
    <main style="max-width:700px;margin:2em auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);padding:2em;">
        <h1>Paramètrage</h1>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Université</th>
                    <th>Pays</th>
                    <th>Partenaire</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($universites as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['code']) ?></td>
                    <td><?= htmlspecialchars($u['universite']) ?></td>
                    <td><?= htmlspecialchars($u['pays']) ?></td>
                    <td><?= htmlspecialchars($u['partenaire']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
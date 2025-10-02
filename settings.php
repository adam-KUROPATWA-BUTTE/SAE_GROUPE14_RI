<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètrage</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
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
            <button onclick="window.location.href='index.php'">Accueil</button>
            <button onclick="window.location.href='dashboard.php'">Tableau de bord</button>
            <button onclick="window.location.href='settings.php'">Paramètrage</button>
            <button onclick="window.location.href='folders.php'">Dossiers</button>
            <button onclick="window.location.href='help.php'">Aide</button>
            <button onclick="window.location.href='web_plan.php'">Plan du site</button>
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
                <tr>
                    <td>AMU001</td>
                    <td>Aix-Marseille Université</td>
                    <td>France</td>
                    <td>Oui</td>
                </tr>
                <tr>
                    <td>OXF002</td>
                    <td>University of Oxford</td>
                    <td>Royaume-Uni</td>
                    <td>Oui</td>
                </tr>
                <tr>
                    <td>MIT003</td>
                    <td>MIT</td>
                    <td>États-Unis</td>
                    <td>Non</td>
                </tr>
                <!-- Ajoute d'autres lignes ici si besoin -->
            </tbody>
        </table>
    </main>
</body>
</html>
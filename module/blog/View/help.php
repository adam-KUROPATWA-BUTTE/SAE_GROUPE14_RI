<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - Service des Relations Internationales AMU</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/help.css">
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
            <button onclick="window.location.href='index.php'">Accueil</button>
            <button onclick="window.location.href='dashboard.php'">Tableau de bord</button>
            <button onclick="window.location.href='settings.php'">Paramètrage</button>
            <button onclick="window.location.href='folders.php'">Dossiers</button>
            <button onclick="window.location.href='help.php'">Aide</button>
            <button onclick="window.location.href='web_plan.php'">Plan du site</button>
        </nav>
    </header>
    <main>
        <h1>Aide pour les administrateurs</h1>
        <p>Bienvenue dans l’espace d’aide destiné aux administrateurs du service des relations internationales d’AMU.</p>
        <h2>Fonctionnalités principales</h2>
        <ul>
            <li><strong>Gestion des utilisateurs :</strong> Ajouter, modifier ou supprimer des comptes utilisateurs.</li>
            <li><strong>Gestion des universités partenaires :</strong> Ajouter, modifier ou supprimer des universités dans la base.</li>
            <li><strong>Consultation des dossiers :</strong> Accéder à tous les dossiers étudiants et suivre leur avancement.</li>
            <li><strong>Paramétrage :</strong> Modifier les paramètres du service (contacts, préférences, etc.).</li>
            <li><strong>Réinitialisation de mot de passe :</strong> Aider les utilisateurs à réinitialiser leur mot de passe si besoin.</li>
        </ul>
        <h2>Questions fréquentes</h2>
        <ul>
            <?php foreach ($faq as $item): ?>
                <li>
                    <strong><?= htmlspecialchars($item['question']) ?></strong><br>
                    <?= $item['answer'] ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <h2>Contact</h2>
        <p>Pour toute question ou problème, contactez le responsable du service des relations internationales :<br>
        <strong>relations-internationales@amu.fr</strong></p>
    </main>
</body>
</html>
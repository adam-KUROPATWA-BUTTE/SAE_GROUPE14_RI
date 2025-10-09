<?php ?>
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
        <button onclick="window.location.href='DashboardPage.php'">Tableau de bord</button>
        <button onclick="window.location.href='/settings'">Paramétrage</button>
        <button class="active" onclick="window.location.href='/folders'">Dossiers</button>
        <button onclick="window.location.href='/help'">Aide</button>
        <button onclick="window.location.href='/web_plan'">Plan du site</button>
    </nav>
    <div class="sub-menu" style="display:flex; gap:10px; margin-top:20px;">
        <button onclick="window.location.href='folders.php'">Les étudiants</button>
    </div>
</header>

<main>
    <h1>Fiche Étudiant</h1>
    <div class="student-toolbar">
        <!-- Recherche -->
        <div>
            <label for="search">Rechercher</label>
            <input type="text" id="search" name="search">
        </div>
        <!-- Boutons navigation -->
        <div class="nav-buttons">
            <button>&laquo;</button> <!-- premier -->
            <button>&lt;</button>   <!-- précédent -->
            <button>&gt;</button>   <!-- suivant -->
            <button>&raquo;</button> <!-- dernier -->
            <span style="margin-left:10px; font-style: italic ; cursor: pointer;">Enregistrer la fiche</span>
        </div>
    </div>

    <!-- Formulaire étudiant -->
    <form method="post" action="save_student.php" enctype="multipart/form-data">
        <div class="form-section">
            <label for="numetu">NumÉtu</label>
            <input type="text" name="numetu" id="numetu" required>

            <label for="nom">Nom</label>
            <input type="text" name="nom" id="nom" required>

            <label for="prenom">Prénom</label>
            <input type="text" name="prenom" id="prenom" required>

            <label for="naissance">Né(e) le</label>
            <input type="date" name="naissance" id="naissance">

            <label for="sexe">Sexe</label>
            <select name="sexe" id="sexe">
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
                <option value="Autre">Autre</option>
            </select>

            <label for="adresse">Adresse</label>
            <input type="text" name="adresse" id="adresse" style="grid-column: span 1;">

            <label for="cp">Code postal</label>
            <input type="text" name="cp" id="cp">

            <label for="ville">Ville</label>
            <input type="text" name="ville" id="ville">

            <label for="email_perso">Email Personnel</label>
            <input type="email" name="email_perso" id="email_perso" style="grid-column: span 3;">

            <label for="email_amu">Email AMU</label>
            <input type="email" name="email_amu" id="email_amu" style="grid-column: span 3;">

            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone">

            <label for="departement">Code Département</label>
            <input type="text" name="departement" id="departement">

            <label for="photo">Photo</label>
            <input type="file" name="photo" id="photo" accept="image/*">
        </div>
    </form>

    <!-- Liste des vœux -->
    <h2>Liste des vœux de l'étudiant</h2>
    <table>
        <thead>
        <tr>
            <th>N° vœu</th>
            <th>Code campagne</th>
            <th>Départ</th>
            <th>Retour</th>
            <th>Destination</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($voeux)): ?>
            <?php foreach ($voeux as $voeu): ?>
                <tr>
                    <td><?= htmlspecialchars($voeu['id']) ?></td>
                    <td><?= htmlspecialchars($voeu['codecampagne']) ?></td>
                    <td><?= htmlspecialchars($voeu['depart']) ?></td>
                    <td><?= htmlspecialchars($voeu['retour']) ?></td>
                    <td><?= htmlspecialchars($voeu['destination']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">Aucun vœu enregistré</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="voeux-actions">
        <button type="button" onclick="window.location.href='new_voeu.php'">Nouveau vœu</button>
        <button type="button" onclick="location.reload()">Actualiser la liste</button>
    </div>
</main>
</body>
</html>

<?php
$message = $_SESSION['message'] ?? '';
if ($message) {
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des dossiers</title>
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
                <?php if ($isLoggedIn): ?>
                    <button onclick="window.location.href='index.php?page=logout'">Se déconnecter</button>
                <?php else: ?>
                    <button onclick="window.location.href='index.php?page=login'">Se connecter</button>
                <?php endif; ?>
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
        <h1>Gestion des dossiers</h1>

        <?php if ($message): ?>
            <div class="message success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>NumETu</th>
                    <th>Nom étudiant</th>
                    <th>Prénom étudiant</th>
                    <th>Avancement</th>
                    <th>Pièces fournies</th>
                    <th>Dernière relance</th>
                    <?php if ($isLoggedIn): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dossiers as $dossier):
                    $total = (int)$dossier['total_pieces'];
                    $fournies = (int)$dossier['pieces_fournies'];
                    $pourcentage = $total > 0 ? round(($fournies / $total) * 100) : 0;
                    $isComplet = ($fournies === $total && $total > 0);
                    $isValide = $dossier['valide'] ?? false;
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
                    <?php if ($isLoggedIn): ?>
                        <td>
                            <?php if ($isValide): ?>
                                <span class="badge-valide">✓ Validé</span>
                            <?php elseif ($isComplet): ?>
                                <form method="POST" action="index.php?page=folders" style="display:inline;">
                                    <input type="hidden" name="action" value="valider">
                                    <input type="hidden" name="numetu" value="<?= htmlspecialchars($dossier['numetu']) ?>">
                                    <button type="submit" class="btn-valider" onclick="return confirm('Voulez-vous valider ce dossier ?')">
                                        Valider
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn-valider" disabled>Incomplet</button>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
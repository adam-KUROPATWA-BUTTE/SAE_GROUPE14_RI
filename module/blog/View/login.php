<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion / Création de compte</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
</head>

<body>
    <div class="container">
        <?php if (!empty($message)): ?>
            <?php
            // Déterminer le type de message
            $messageType = 'info';
            if (strpos($message, 'succès') !== false || strpos($message, 'réussie') !== false) {
                $messageType = 'success';
            } elseif (strpos($message, 'Erreur') !== false || strpos($message, 'incorrect') !== false || strpos($message, 'invalide') !== false || strpos($message, 'expiré') !== false) {
                $messageType = 'error';
            }
            ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($isTokenReset)): ?>
            <h2>Réinitialisation du mot de passe</h2>
            <form method="POST" action="index.php?page=reset&token=<?= urlencode($token) ?>">
                <input type="hidden" name="action" value="token_reset">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
                <button type="submit">Changer le mot de passe</button>
            </form>

        <?php elseif ($isLogin && !$isReset): ?>
            <h2>Connexion</h2>
            <form method="POST" action="index.php?page=login">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
            </form>
            <div class="toggle">
                <p><a href="index.php?page=reset">Mot de passe oublié ?</a></p>
                <p>Pas encore de compte ? <a href="index.php?page=register">Créer un compte</a></p>
            </div>

        <?php elseif ($isReset): ?>
            <h2>Mot de passe oublié</h2>
            <form method="POST" action="index.php?page=reset">
                <input type="hidden" name="action" value="reset">
                <input type="email" name="email" placeholder="Votre email" required>
                <button type="submit">Envoyer le lien de réinitialisation</button>
            </form>
            <div class="toggle">
                <p><a href="index.php?page=login">Retour à la connexion</a></p>
            </div>

       <?php else: ?>
        <h2>Créer un compte</h2>
        <form method="POST" action="index.php?page=register">
            <input type="hidden" name="action" value="register">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>
        <div class="toggle">
            <p>Déjà un compte ? <a href="index.php?page=login">Se connecter</a></p>
        </div>
    <?php endif; ?>
    </div>
</body>
</html>
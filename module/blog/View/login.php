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
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($isLogin && !$isReset): ?>
            <h2>Connexion</h2>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
            </form>
            <div class="toggle">
                <p><a href="?reset">Mot de passe oublié ?</a></p>
                <p>Pas encore de compte ? <a href="?register">Créer un compte</a></p>
            </div>
        <?php elseif ($isReset): ?>
            <h2>Mot de passe oublié</h2>
            <form method="POST">
                <input type="hidden" name="action" value="reset">
                <input type="email" name="email" placeholder="Votre email" required>
                <button type="submit">Envoyer le lien de réinitialisation</button>
            </form>
            <div class="toggle">
                <p><a href="?login">Retour à la connexion</a></p>
            </div>
        <?php else: ?>
            <h2>Créer un compte</h2>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">S'inscrire</button>
            </form>
            <div class="toggle">
                <p>Déjà un compte ? <a href="?login">Se connecter</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
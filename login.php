<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion / Création de compte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php
        $isLogin = true;
        if (isset($_GET['register'])) {
            $isLogin = false;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['action'] === 'login') {
                $email = $_POST['email'];
                $password = $_POST['password'];
                echo "<p>Connexion avec l'email : $email</p>";
            } elseif ($_POST['action'] === 'register') {
                $email = $_POST['email'];
                $password = $_POST['password'];
                echo "<p>Création de compte avec l'email : $email</p>";
            }
        }
        ?>

        <?php if ($isLogin): ?>
            <h2>Connexion</h2>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
            </form>
            <div class="toggle">
                <p>Pas encore de compte ? <a href="?register">Créer un compte</a></p>
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
<?php

namespace View;

class RegisterAdminPage
{
    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Créer un administrateur</title>
            <link rel="stylesheet" href="styles/login.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
            <style>
                .admin-header {
                    background: #2c3e50;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    margin-bottom: 20px;
                }
                .back-link {
                    display: inline-block;
                    margin-top: 15px;
                    color: #3498db;
                    text-decoration: none;
                }
                .back-link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>

        <body>
            <div class="admin-header">
                <h1>⚠️ Espace Administrateur</h1>
                <p>Création d'un nouveau compte administrateur</p>
            </div>

            <div class="container">
                <?php if (!empty($message)) : ?>
                    <?php
                    $messageType = 'info';
                    if (strpos($message, 'succès') !== false) {
                        $messageType = 'success';
                    } elseif (strpos($message, 'Erreur') !== false || strpos($message, 'invalide') !== false) {
                        $messageType = 'error';
                    }
                    ?>
                    <div class="message <?= $messageType ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <h2>🔐 Créer un compte administrateur</h2>
                <p style="text-align: center; color: #e74c3c; margin-bottom: 20px;">
                    <strong>Attention :</strong> Ce compte aura accès à toutes les fonctionnalités d'administration.
                </p>

                <form method="POST" action="index.php?page=register_admin">
                    <input type="hidden" name="action" value="register_admin">
                    
                    <input type="text" name="nom" placeholder="Nom" required>
                    <input type="text" name="prenom" placeholder="Prénom" required>
                    <input type="email" name="email" placeholder="Email administrateur" required>
                    <input type="password" name="password" placeholder="Mot de passe (min. 8 caractères)" required minlength="8">
                    
                    <button type="submit">✅ Créer l'administrateur</button>
                </form>

                <div class="toggle">
                    <a href="index.php?page=dashboard" class="back-link">
                        ← Retour au tableau de bord
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}

// Instanciation et rendu
$page = new RegisterAdminPage();
$page->render();
<?php
namespace View;

class LoginPage
{
    private $message;
    private $isTokenReset;
    private $isLogin;
    private $isReset;
    private $token;

    public function __construct($message, $isTokenReset, $isLogin, $isReset, $token)
    {
        $this->message = $message;
        $this->isTokenReset = $isTokenReset;
        $this->isLogin = $isLogin;
        $this->isReset = $isReset;
        $this->token = $token;
    }

    public function render(): void
    {
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
                <?php if (!empty($this->message)): ?>
                    <?php
                    // Déterminer le type de message
                    $messageType = 'info';
                    if (strpos($this->message, 'succès') !== false || strpos($this->message, 'réussie') !== false) {
                        $messageType = 'success';
                    } elseif (strpos($this->message, 'Erreur') !== false || strpos($this->message, 'incorrect') !== false || strpos($this->message, 'invalide') !== false || strpos($this->message, 'expiré') !== false) {
                        $messageType = 'error';
                    }
                    ?>
                    <div class="message <?= $messageType ?>">
                        <?= htmlspecialchars($this->message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->isTokenReset): ?>
                    <h2>Réinitialisation du mot de passe</h2>
                    <form method="POST" action="index.php?page=reset&token=<?= urlencode($this->token) ?>">
                        <input type="hidden" name="action" value="token_reset">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($this->token) ?>">
                        <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
                        <button type="submit">Changer le mot de passe</button>
                    </form>
                
                <?php elseif ($this->isLogin && !$this->isReset): ?>
                    <h2>Connexion</h2>
                    <form method="POST" action="index.php?page=login">
                        <input type="hidden" name="action" value="login">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Mot de passe" required>
                        <button type="submit">Se connecter</button>
                    </form>
                    <div class="toggle">
                        <p><a href="index.php?page=reset">Mot de passe oublié ?</a></p>
                        <p>Pas encore de compte ? <a href="index.php?page=register_etudiant">Créer un compte</a></p>
                    </div>
                
                <?php elseif ($this->isReset): ?>
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
                <form method="POST" action="index.php?page=register_etudiant">
                    <input type="hidden" name="action" value="register_etudiant">
                    <input type="text" name="nom" placeholder="Nom" required>
                    <input type="text" name="prenom" placeholder="Prénom" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <select name="type_etudiant" required>
                        <option value="">Sélectionnez le type d'étudiant</option>
                        <option value="entrant">Entrant</option>
                        <option value="sortant">Sortant</option>
                    </select>
                    <button type="submit">S'inscrire</button>
                </form>
                <div class="toggle">
                    <p>Déjà un compte ? <a href="index.php?page=login">Se connecter</a></p>
                </div>
            <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }
}

// Instanciation et rendu
$page = new LoginPage();
$page->render();
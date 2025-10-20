<?php
namespace View;

class LoginPage
{
    private string $message;
    private bool $isTokenReset;
    private bool $isLogin;
    private bool $isReset;
    private string $token;

    public function __construct($message = '', $isTokenReset = false, $isLogin = true, $isReset = false, $token = '')
    {
        $this->message = $message;
        $this->isTokenReset = $isTokenReset;
        $this->isLogin = $isLogin;
        $this->isReset = $isReset;
        $this->token = $token;
    }

    public function render()
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Connexion - Aix-Marseille Université</title>
            <link rel="stylesheet" href="styles/login.css">
        </head>
        <body>
            <div class="container">

                <h2>Service d'authentification<br>Aix-Marseille Université</h2>

                <?php if (!empty($this->message)): ?>
                    <div class="message <?= strpos($this->message, 'succès') !== false ? 'success' : 'error' ?>">
                        <?= htmlspecialchars($this->message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <label for="identifier">Email (admin) ou Numéro étudiant :</label>
                    <input type="text" name="identifier" id="identifier" required>

                    <label for="password">Mot de passe :</label>
                    <input type="password" name="password" id="password" required>

                    <button type="submit" name="action" value="login">Se connecter</button>
                </form>


                <div class="info-section warning">
                    <p>
                        ⚠️ Pour des raisons de sécurité, déconnectez-vous et fermez votre navigateur après utilisation.
                    </p>
                </div>

                <div class="info-section help">
                    <p>
                        💡 En cas de problème, consultez la <a href="https://dirnum.univ-amu.fr/public_content/contacts">page d’aide</a> ou contactez le support informatique.
                    </p>
                </div>

                <img src="img/logo_amu_login.png" alt="Logo Aix-Marseille Université" class="logo-amu">

            </div>
        </body>
        </html>
        <?php
    }
}

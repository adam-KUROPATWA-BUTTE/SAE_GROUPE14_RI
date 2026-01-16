<?php

// phpcs:disable Generic.Files.LineLength

namespace View;

/**
 * Class LoginPage
 *
 * Responsible for rendering the login page for AMU's authentication system.
 */
class LoginPage
{
    /** @var string Message to display */
    private string $message;
    /** @var bool True if token reset mode */
    private bool $isTokenReset;
    /** @var bool True if login mode */
    private bool $isLogin;
    /** @var bool True if request reset mode */
    private bool $isReset;
    /** @var string Token string */
    private string $token;

    public function __construct(
        string $message = '',
        bool $isTokenReset = false,
        bool $isLogin = true,
        bool $isReset = false,
        string $token = ''
    ) {
        $this->message = $message;
        $this->isTokenReset = $isTokenReset;
        $this->isLogin = $isLogin;
        $this->isReset = $isReset;
        $this->token = $token;
    }

    public function render(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Aix-Marseille University</title>
            <link rel="stylesheet" href="styles/login.css">
        </head>
        <body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">
        <div class="container">

            <h2>Authentication Service<br>Aix-Marseille University</h2>

            <?php if (!empty($this->message)) : ?>
                <div class="message <?= strpos($this->message, 'succès') !== false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($this->message) ?>
                </div>
            <?php endif; ?>

            <?php if ($this->isTokenReset) : ?>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($this->token) ?>">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" name="password" id="password" required>
                    <button type="submit" name="action" value="reset_password">Valider</button>
                </form>

            <?php elseif ($this->isReset) : ?>
                <form method="POST" action="">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                    <button type="submit" name="action" value="request_reset">Envoyer le lien</button>
                    <a href="index.php?page=login" style="display:block;margin-top:10px;">Retour</a>
                </form>

            <?php elseif ($this->isLogin) : ?>
                <form method="POST" action="">
                    <label for="identifier">Identifiant / Email</label>
                    <input type="text" name="identifier" id="identifier" required>

                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" required>

                    <button type="submit" name="action" value="login">Se connecter</button>
                    <a href="index.php?page=forgot_password" style="display:block;margin-top:10px;">Mot de passe oublié ?</a>
                </form>
            <?php endif; ?>

            <div class="info-section warning">
                <p>Fermez votre navigateur après usage pour des raisons de sécurité.</p>
            </div>

            <img src="img/logo_amu_login.png" alt="Aix-Marseille University Logo" class="logo-amu">

        </div>
        </body>
        </html>
        <?php
    }
}
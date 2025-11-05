<?php
namespace View;

class ResetPasswordPage
{
    private ?string $token;
    private ?string $error;
    private ?string $success;

    public function __construct(?string $token = null, ?string $error = null, ?string $success = null)
    {
        $this->token = $token;
        $this->error = $error;
        $this->success = $success;
    }

    public function render(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="Réinitialisation du mot de passe - Service des relations internationales AMU">
            <title>Réinitialisation du mot de passe - AMU</title>
            <link rel="stylesheet" href="/styles/login.css">
            <link rel="icon" type="image/png" href="/img/favicon.webp"/>
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
            <div class="login-container">
                <div class="login-card">
                    <h1>Nouveau mot de passe</h1>
                    
                    <?php if ($this->error): ?>
                        <div class="error-message">
                            <?= htmlspecialchars($this->error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($this->success): ?>
                        <div class="success-message">
                            <?= htmlspecialchars($this->success) ?>
                            <br><br>
                            <a href="/index.php?page=login" class="btn-primary">Se connecter</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($this->token ?? '') ?>">
                            
                            <div class="form-group">
                                <label for="password">Nouveau mot de passe</label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       minlength="8"
                                       placeholder="Minimum 8 caractères">
                                <small>Minimum 8 caractères</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirm">Confirmer le mot de passe</label>
                                <input type="password" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       required
                                       placeholder="Confirmez votre mot de passe">
                            </div>
                            
                            <button type="submit" name="submit_reset" class="btn-primary">
                                Réinitialiser le mot de passe
                            </button>
                        </form>
                        
                        <div class="login-links">
                            <a href="/index.php?page=login">Retour à la connexion</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
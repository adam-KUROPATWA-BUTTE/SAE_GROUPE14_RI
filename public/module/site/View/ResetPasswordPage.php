<?php

namespace View;

/**
 * Class ResetPasswordPage
 *
 * Renders the password reset page where a user can set a new password using a token.
 */
class ResetPasswordPage
{
    /** @var string|null The reset token */
    private ?string $token;

    /** @var string|null Error message to display */
    private ?string $error;

    /** @var string|null Success message to display */
    private ?string $success;

    /**
     * Constructor.
     *
     * @param string|null $token   The password reset token
     * @param string|null $error   Error message if any
     * @param string|null $success Success message if any
     */
    public function __construct(?string $token = null, ?string $error = null, ?string $success = null)
    {
        $this->token = $token;
        $this->error = $error;
        $this->success = $success;
    }

    /**
     * Render the password reset page.
     */
    public function render(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="Password reset - AMU International Relations Service">
            <title>Password Reset - AMU</title>
            <link rel="stylesheet" href="/styles/login.css">
            <link rel="icon" type="image/png" href="/img/favicon.webp"/>
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
        <div class="login-container">
            <div class="login-card">
                <h1>New Password</h1>

                <?php if ($this->error) : ?>
                    <!-- Display error message if present -->
                    <div class="error-message">
                        <?= htmlspecialchars($this->error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->success) : ?>
                    <!-- Display success message and link to login -->
                    <div class="success-message">
                        <?= htmlspecialchars($this->success) ?>
                        <br><br>
                        <a href="/index.php?page=login" class="btn-primary">Log in</a>
                    </div>
                <?php else : ?>
                    <!-- Password reset form -->
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($this->token ?? '') ?>">

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   minlength="8"
                                   placeholder="Minimum 8 characters">
                            <small>Minimum 8 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirm Password</label>
                            <input type="password"
                                   id="password_confirm"
                                   name="password_confirm"
                                   required
                                   placeholder="Confirm your password">
                        </div>

                        <button type="submit" name="submit_reset" class="btn-primary">
                            Reset Password
                        </button>
                    </form>

                    <!-- Link back to login page -->
                    <div class="login-links">
                        <a href="/index.php?page=login">Back to Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </body>
        </html>
        <?php
    }
}

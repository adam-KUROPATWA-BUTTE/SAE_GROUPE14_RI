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
    private string $message;
    private bool $isTokenReset;
    private bool $isLogin;
    private bool $isReset;
    private string $token;

    /**
     * LoginPage constructor.
     *
     * @param string $message Message to display (error, info, success)
     * @param bool $isTokenReset True if displaying a token-based reset form
     * @param bool $isLogin True if this is the login form, false if registration
     * @param bool $isReset True if the form is in reset mode
     * @param string $token Reset token if applicable
     */
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

    /**
     * Render the login page.
     */
    public function render(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Aix-Marseille University</title>
            <link rel="stylesheet" href="styles/login.css">
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'tritanopie' : '' ?>">
        <div class="container">

            <!-- Page Header -->
            <h2>Authentication Service<br>Aix-Marseille University</h2>

            <!-- Display messages if available -->
            <?php if (!empty($this->message)) : ?>
                <div class="message <?= strpos($this->message, 'succès') !== false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($this->message) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
                <label for="identifier">Email (admin) or Student Number:</label>
                <input type="text" name="identifier" id="identifier" required>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <button type="submit" name="action" value="login">Log in</button>
            </form>

            <!-- Security warning -->
            <div class="info-section warning">
                <p>
                    For security reasons, please log out and close your browser after use.
                </p>
            </div>

            <!-- Help section -->
            <div class="info-section help">
                <p>
                    💡 For assistance, visit the <a href="https://dirnum.univ-amu.fr/public_content/contacts">help page</a> or contact IT support.
                </p>
            </div>

            <!-- Footer Logo -->
            <img src="img/logo_amu_login.png" alt="Aix-Marseille University Logo" class="logo-amu">

        </div>
        </body>
        </html>
        <?php
    }
}

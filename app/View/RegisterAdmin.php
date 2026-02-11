<?php

// phpcs:disable Generic.Files.LineLength

namespace View;

/**
 * Class RegisterAdmin
 *
 * Renders the registration page for creating a new administrator account.
 */
class RegisterAdmin
{
    /** @var string Feedback message (success, error, info) */
    private string $message;

    /**
     * Constructor.
     *
     * @param string $message Feedback message to display.
     */
    public function __construct(string $message = '')
    {
        $this->message = $message;
    }

    /**
     * Render the registration page.
     *
     * @return void
     */
    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Create an Administrator</title>
            <link rel="stylesheet" href="styles/login.css">
            <link rel="stylesheet" href="styles/register.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>

        <body>
        <div class="admin-header">
            <h1>Administrator Area</h1>
            <p>Create a new administrator account</p>
        </div>

        <div class="container">
            <?php
            // Display message if set
            if ($this->message !== '') :
                $messageType = 'info';
                // Strict check with strpos returning int|false
                if (strpos($this->message, 'success') !== false) {
                    $messageType = 'success';
                } elseif (strpos($this->message, 'Error') !== false || strpos($this->message, 'invalid') !== false) {
                    $messageType = 'error';
                }
                ?>
                <div class="message <?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($this->message) ?>
                </div>
            <?php endif; ?>

            <h2 class="register-title">Create Administrator Account</h2>
            <p class="warning-text">
                <strong>Warning:</strong> This account will have access to all administration functionalities.
            </p>

            <form class="register-form" method="POST" action="index.php?page=register_admin">
                <input type="hidden" name="action" value="register_admin">

                <input type="text" name="nom" placeholder="Last Name" required>
                <input type="text" name="prenom" placeholder="First Name" required>
                <input type="email" name="email" placeholder="Administrator Email" required>
                <input type="password" name="password" placeholder="Password (min. 8 characters)" required minlength="8">

                <button type="submit" class="btn-submit">Create Administrator</button>
            </form>

            <div class="toggle">
                <a href="index.php?page=dashboard" class="back-link">← Back to Dashboard</a>
            </div>
        </div>
        </body>
        </html>
        <?php
    }
}
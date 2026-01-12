<?php

namespace View;

/**
 * Class RegisterAdmin
 *
 * Renders the registration page for creating a new administrator account.
 */
class RegisterAdmin
{
    /**
     * Render the registration page.
     */
    public function render(): void
    {
        // Start of HTML output
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
        <!-- Admin header section -->
        <div class="admin-header">
            <h1>Administrator Area</h1>
            <p>Create a new administrator account</p>
        </div>

        <div class="container">
            <?php
            // Display message if set (success, error, info)
            if (!empty($message)) :
                $messageType = 'info';
                if (strpos($message, 'success') !== false) {
                    $messageType = 'success';
                } elseif (strpos($message, 'Error') !== false || strpos($message, 'invalid') !== false) {
                    $messageType = 'error';
                }
                ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Registration form -->
            <h2 class="register-title">Create Administrator Account</h2>
            <p class="warning-text">
                <strong>Warning:</strong> This account will have access to all administration functionalities.
            </p>

            <form class="register-form" method="POST" action="index.php?page=register_admin">
                <input type="hidden" name="action" value="register_admin">

                <!-- Admin details -->
                <input type="text" name="nom" placeholder="Last Name" required>
                <input type="text" name="prenom" placeholder="First Name" required>
                <input type="email" name="email" placeholder="Administrator Email" required>
                <input type="password" name="password" placeholder="Password (min. 8 characters)" required minlength="8">

                <button type="submit" class="btn-submit">Create Administrator</button>
            </form>

            <!-- Link back to dashboard -->
            <div class="toggle">
                <a href="index.php?page=dashboard" class="back-link">← Back to Dashboard</a>
            </div>
        </div>
        </body>
        </html>

        <?php
    }
}

// Instantiate and render the page
$page = new RegisterAdmin();
$page->render();

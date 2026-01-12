<?php

namespace View;

/**
 * Class NotFoundPage
 *
 * Displays a 404 page not found message.
 */
class NotFoundPage
{
    private string $titre;

    /**
     * NotFoundPage constructor.
     *
     * @param string $titre Title of the page
     */
    public function __construct(string $titre = 'Page non trouvée')
    {
        $this->titre = $titre;
    }

    /**
     * Render the 404 page.
     */
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
            <link rel="stylesheet" href="styles/404.css">
            <title><?= htmlspecialchars($this->titre) ?> - 404</title>
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
        <div class="notfound-container">
            <h1>404</h1>
            <p>La page que vous recherchez n’existe pas.</p>
            <a href="/">Retour à l’accueil</a>
        </div>
        </body>
        </html>
        <?php
    }
}

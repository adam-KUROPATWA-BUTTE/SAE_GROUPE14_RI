<?php

// phpcs:disable Generic.Files.LineLength

namespace View;

/**
 * Class NotFoundPage
 *
 * Displays a 404 page not found message.
 */
class NotFoundPage
{
    /** @var string Title of the page */
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
     *
     * @return void
     */
    public function render(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Correction Level 9: Strict boolean check for mixed session value
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);

        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="styles/404.css">
            <title><?= htmlspecialchars($this->titre) ?> - 404</title>
        </head>
        <body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">
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
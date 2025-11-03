<?php
namespace View;

class NotFoundPage
{
    private string $titre;

    public function __construct(string $titre = 'Page non trouvée')
    {
        $this->titre = $titre;
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="styles/404.css">
            <title><?= htmlspecialchars($this->titre) ?> - 404</title>
        </head>
        <body>
            <h1>404</h1>
            <p>La page que vous cherchez n’existe pas.</p>
            <a href="/">Retour à l’accueil</a>
        </body>
        </html>
        <?php
    }
}

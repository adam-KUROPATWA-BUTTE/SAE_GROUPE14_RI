<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/404.css">
    <title><?= htmlspecialchars($titre) ?> - 404</title>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">
    <div class="notfound-container">
        <h1>404</h1>
        <p>La page que vous recherchez n’existe pas.</p>
        <a href="/">Retour à l’accueil</a>
    </div>
</body>
</html>
<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/web_plan.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
    <title>Plan du site</title>
    <base href="http://localhost:8080/">
</head>
<body>
    <header>
        <div class="top-bar">
            <img src="img/logo.png" alt="Logo" style="height:100px;">
            <div class="right-buttons"></div>


        </div>
    </header>
    <main style="padding:2em;">
        <h1>Plan du site</h1>
        <ul>
            <?php foreach ($links as $link): ?>
                <li><a href="<?= htmlspecialchars($link['url']) ?>"><?= htmlspecialchars($link['label']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
<!-- Footer -->
<footer>
    <p>&copy; 2025 - Aix-Marseille Université.</p>
</footer>
</html>
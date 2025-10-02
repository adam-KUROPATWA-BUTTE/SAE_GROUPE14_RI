<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <title>Page avec bouton</title>
</head>
<body>
    <header>
        <div class="top-bar">
            <img src="img/logo.png" alt="Logo" style="height:100px;">
            <div class="right-buttons">
                <button>fr</button>
                <button onclick="window.location.href='login.php'">Se connecter</button>
            </div>
        </div>
    
        <nav class="menu">
            <button>Accueil</button>
            <button>Tableau de bord</button>
            <button>Paramètrage</button>
            <button>Dossiers</button>
            <button>Aide</button>
        </nav>
    </header>
</body>
</html>

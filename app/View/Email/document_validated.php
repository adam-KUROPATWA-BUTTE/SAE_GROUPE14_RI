<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Document validé</title>
    <link rel="stylesheet" href="styles/emails.css">
</head>
<body>
<div class="email-wrapper">
    <div class="email-header email-header--green">
        <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="AMU">
        <h2>✓ Document validé</h2>
    </div>
    <div class="email-body">
        <p>Bonjour <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>,</p>
        <p>Bonne nouvelle ! Votre document a été <strong class="text-green-bold">validé</strong> :</p>
        <div class="email-highlight email-highlight--green">
            <strong>✓ <?= htmlspecialchars($documentLabel, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <p>Votre dossier progresse bien. N'oubliez pas de déposer les autres documents si nécessaire.</p>
        <div class="email-cta">
            <a href="<?= htmlspecialchars($folderLink, ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">Voir mon dossier</a>
        </div>
        <p class="email-note">Si vous avez des questions, contactez le service RI.</p>
        <p class="email-note--small">Notification automatique • Service RI - IUT Aix</p>
    </div>
</div>
</body>
</html>
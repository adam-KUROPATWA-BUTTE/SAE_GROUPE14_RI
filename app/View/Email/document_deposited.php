<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Document déposé</title></head>
<body>
  <div class="email-wrapper">
    <div class="email-header email-header--blue">
      <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="AMU">
      <h2>📄 Document déposé</h2>
    </div>
    <div class="email-body">
      <p>Bonjour <strong><?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></strong>,</p>
      <p>Nous confirmons la réception de votre document :</p>
      <div class="email-highlight email-highlight--blue">
        <strong>📎 <?= htmlspecialchars($documentLabel, ENT_QUOTES, 'UTF-8') ?></strong>
      </div>
      <p>Votre document sera examiné par notre équipe. Vous recevrez une notification une fois validé.</p>
      <div class="email-cta">
        <a href="<?= htmlspecialchars($folderLink, ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">Voir mon dossier</a>
      </div>
      <p class="email-note">Si vous avez des questions, contactez le service RI.</p>
      <p class="email-note--small">Notification automatique • Service RI - IUT Aix</p>
    </div>
  </div>
</body>
</html>
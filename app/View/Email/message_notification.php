<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Nouveau Message</title></head>
<body>
  <div class="email-wrapper">
    <div class="email-header email-header--blue">
      <h2>📬 Nouveau Message</h2>
    </div>
    <div class="email-body">
      <p>Bonjour <strong><?= htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') ?></strong>,</p>
      <p>Vous avez reçu un nouveau message de <strong><?= htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8') ?></strong>.</p>

      <div class="email-highlight email-highlight--grey">
        <div class="highlight-sender">De : <?= htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="highlight-preview"><?= htmlspecialchars($messagePreview, ENT_QUOTES, 'UTF-8') ?><?= strlen($messagePreview) >= 150 ? '…' : '' ?></div>
      </div>

      <div class="email-cta">
        <a href="<?= htmlspecialchars($platformLink, ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">Consulter sur la plateforme</a>
      </div>
      <p class="email-note">Veuillez vous connecter à votre espace pour lire le message complet et y répondre.</p>
      <p class="email-note--small">Email automatique • Service RI - AMU</p>
    </div>
  </div>
</body>
</html>
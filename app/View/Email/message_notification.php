<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Nouveau Message</title></head>
<body style="font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <div style="background:#1d7ac6;padding:20px;text-align:center;">
      <h2 style="color:#fff;margin:10px 0 0;">📬 Nouveau Message</h2>
    </div>
    <div style="padding:30px;">
      <p>Bonjour <?= htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') ?>,</p>
      <p>Vous avez reçu un nouveau message de <strong><?= htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
      
      <div style="background:#f8f9fa;border-left:4px solid #1d7ac6;padding:15px;margin:20px 0;border-radius:4px;">
        <div style="font-weight:600;color:#1d7ac6;margin-bottom:8px;font-size:14px;">De : <?= htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8') ?></div>
        <div style="color:#555;font-size:14px;font-style:italic;"><?= htmlspecialchars($messagePreview, ENT_QUOTES, 'UTF-8') ?><?= strlen($messagePreview) > 150 ? '...' : '' ?></div>
      </div>
      
      <div style="text-align:center;margin:18px 0;">
        <a href="<?= htmlspecialchars($platformLink, ENT_QUOTES, 'UTF-8') ?>" style="background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;">Consulter sur la plateforme</a>
      </div>
      <p style="font-size:14px;color:#666;">Veuillez vous connecter à votre espace pour lire le message complet et y répondre.</p>
      <p style="color:#666;font-size:13px;margin:12px 0 0;">Email automatique • Service RI - AMU</p>
    </div>
  </div>
</body>
</html>

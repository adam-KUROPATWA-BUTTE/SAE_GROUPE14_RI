<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Document déposé</title></head>
<body style="font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <div style="background:#1d7ac6;padding:20px;text-align:center;">
      <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="AMU" style="height:50px;">
      <h2 style="color:#fff;margin:10px 0 0;">📄 Document déposé</h2>
    </div>
    <div style="padding:30px;">
      <p>Bonjour <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>,</p>
      <p>Nous confirmons la réception de votre document :</p>
      <div style="background:#e3f2fd;padding:15px;border-left:4px solid #1d7ac6;margin:16px 0;">
        <strong style="color:#1d7ac6;font-size:16px;">📎 <?= htmlspecialchars($documentLabel, ENT_QUOTES, 'UTF-8') ?></strong>
      </div>
      <p>Votre document sera examiné par notre équipe. Vous recevrez une notification une fois validé.</p>
      <div style="text-align:center;margin:18px 0;">
        <a href="<?= htmlspecialchars($folderLink, ENT_QUOTES, 'UTF-8') ?>" style="background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;">Voir mon dossier</a>
      </div>
      <p style="font-size:14px;color:#666;">Si vous avez des questions, contactez le service RI.</p>
      <p style="color:#666;font-size:13px;margin:12px 0 0;">Notification automatique • Service RI - IUT Aix</p>
    </div>
  </div>
</body>
</html>

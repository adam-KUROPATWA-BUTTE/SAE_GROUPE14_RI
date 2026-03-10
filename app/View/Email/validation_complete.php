<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Documents Validés</title></head>
<body style="font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <div style="background:#28a745;padding:20px;text-align:center;">
      <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="AMU" style="height:50px;">
      <h2 style="color:#fff;margin:10px 0 0;">✓ Documents Validés</h2>
    </div>
    <div style="padding:30px;">
      <p>Bonjour <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>,</p>
      <p>Bonne nouvelle ! Votre dossier <strong><?= htmlspecialchars($numEtu, ENT_QUOTES, 'UTF-8') ?></strong> a été examiné et les documents suivants ont été <strong style="color:#28a745;">validés</strong> :</p>
      
      <ul style="margin:0 0 16px 20px;">
        <?php foreach ($validatedDocuments as $doc): ?>
          <li style="color:#28a745;"><strong>✓ <?= htmlspecialchars($doc, ENT_QUOTES, 'UTF-8') ?></strong></li>
        <?php endforeach; ?>
      </ul>
      
      <p style="color:#28a745;font-weight:bold;">Votre dossier est maintenant complet et approuvé !</p>
      <div style="text-align:center;margin:18px 0;">
        <a href="<?= htmlspecialchars($folderLink, ENT_QUOTES, 'UTF-8') ?>" style="background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;">Voir mon dossier</a>
      </div>
      <p style="font-size:14px;color:#666;">Si vous avez des questions, contactez le service RI.</p>
      <p style="color:#666;font-size:13px;margin:12px 0 0;">Notification automatique • Service RI - IUT Aix</p>
    </div>
  </div>
</body>
</html>

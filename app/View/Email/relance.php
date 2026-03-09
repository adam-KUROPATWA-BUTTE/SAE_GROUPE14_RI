<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Rappel dossier</title></head>
<body style="font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <div style="background:#1d7ac6;padding:20px;text-align:center;">
      <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="AMU" style="height:50px;">
      <h2 style="color:#fff;margin:10px 0 0;">⚠️ Rappel — Dossier incomplet</h2>
    </div>
    <div style="padding:30px;">
      <p>Bonjour <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>,</p>
      <p>Votre dossier n°<strong><?= htmlspecialchars($dossierId, ENT_QUOTES, 'UTF-8') ?></strong> est actuellement <strong>incomplet</strong>.</p>
      
      <?php if (!empty($itemsToComplete)): ?>
        <ul style="margin:0 0 16px 20px;">
          <?php foreach ($itemsToComplete as $item): ?>
            <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Veuillez compléter les documents manquants dans votre dossier.</p>
      <?php endif; ?>
      
      <div style="text-align:center;margin:18px 0;">
        <a href="<?= htmlspecialchars($folderLink, ENT_QUOTES, 'UTF-8') ?>" style="background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;">Accéder à mon dossier</a>
      </div>
      <p style="font-size:14px;color:#666;">Pour toute question, contactez le service RI.</p>
      <p style="color:#666;font-size:13px;margin:12px 0 0;">Email automatique • Service RI - IUT Aix</p>
    </div>
  </div>
</body>
</html>

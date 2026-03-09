<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Mise à jour dossier</title></head>
<body style="font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <div style="background:#1d7ac6;padding:20px;text-align:center;">
      <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="AMU" style="height:50px;">
      <h2 style="color:#fff;margin:10px 0 0;">Mise à jour de votre dossier</h2>
    </div>
    <div style="padding:30px;">
      <p>Bonjour <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>,</p>
      <p>Votre dossier a été examiné par l'administration. Voici le récapitulatif :</p>
      
      <div style="background-color:#f9f9f9;border-radius:6px;padding:18px 20px;margin:16px 0;">
        <?php if (!empty($sectionAcceptees)): ?>
          <p style="margin:0 0 6px;font-weight:bold;color:#2e7d32;">✅ Documents validés :</p>
          <ul style="margin:0 0 16px 20px;line-height:1.8;color:#333;">
            <?= $sectionAcceptees ?>
          </ul>
        <?php endif; ?>
        
        <?php if (!empty($sectionRefusees)): ?>
          <p style="margin:0 0 6px;font-weight:bold;color:#c62828;">❌ Documents non validés :</p>
          <ul style="margin:0 0 16px 20px;line-height:1.8;color:#333;">
            <?= $sectionRefusees ?>
          </ul>
        <?php endif; ?>
        
        <?php foreach ($autresLignes as $ligne): ?>
          <p style="margin:4px 0;"><?= $ligne ?></p>
        <?php endforeach; ?>
      </div>
      
      <?php if (!empty($statutGlobal)): ?>
        <?php 
        $statutColors = [
            'Dépôt'          => '#607d8b',
            'En instruction' => '#f57c00',
            'Accepté'        => '#2e7d32',
            'Refusé'         => '#c62828',
        ];
        $statutColor = $statutColors[$statutGlobal] ?? '#1d7ac6';
        ?>
        <div style="margin-top:20px;padding:12px 16px;border-radius:6px;background:#f5f5f5;border-left:4px solid <?= $statutColor ?>;">
          <span style="font-weight:bold;color:#333;">Statut de votre dossier :</span>
          <span style="margin-left:8px;font-weight:bold;color:<?= $statutColor ?>;"><?= htmlspecialchars($statutGlobal, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($dateLimite)): ?>
        <p style="margin-top:12px;color:#555;">📅 Date limite de remise des pièces : <b><?= htmlspecialchars($dateLimite, ENT_QUOTES, 'UTF-8') ?></b></p>
      <?php endif; ?>
      
      <div style="text-align:center;margin:28px 0 16px;">
        <a href="https://ri-amu.app/" style="background-color:#1d7ac6;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:bold;">Consulter mon espace</a>
      </div>
      <p style="font-size:13px;color:#888;">Pour toute question, contactez le service Relations Internationales.</p>
      <p style="color:#bbb;font-size:12px;margin:8px 0 0;">Email automatique • Service RI AMU</p>
    </div>
  </div>
</body>
</html>

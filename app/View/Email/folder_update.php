<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Mise à jour dossier</title></head>
<body>
  <div class="email-wrapper">
    <div class="email-header email-header--blue">
      <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="AMU">
      <h2>Mise à jour de votre dossier</h2>
    </div>
    <div class="email-body">
      <p>Bonjour <strong><?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></strong>,</p>
      <p>Votre dossier a été examiné par l'administration. Voici le récapitulatif :</p>

      <div class="email-summary">
        <?php if (!empty($sectionAcceptees)): ?>
          <p class="summary-title--green">✅ Documents validés :</p>
          <ul><?= $sectionAcceptees ?></ul>
        <?php endif; ?>

        <?php if (!empty($sectionRefusees)): ?>
          <p class="summary-title--red">❌ Documents non validés :</p>
          <ul><?= $sectionRefusees ?></ul>
        <?php endif; ?>

        <?php foreach ($autresLignes as $ligne): ?>
          <p class="summary-line"><?= $ligne ?></p>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($statutGlobal)):
        $statutClasses = [
          'Dépôt'          => 'email-status--depot',
          'En instruction' => 'email-status--instruction',
          'Accepté'        => 'email-status--accepte',
          'Refusé'         => 'email-status--refuse',
        ];
        $statutClass = $statutClasses[$statutGlobal] ?? '';
      ?>
        <div class="email-status <?= $statutClass ?>">
          <span class="status-label">Statut de votre dossier :</span>
          <span class="status-value"><?= htmlspecialchars($statutGlobal, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($dateLimite)): ?>
        <p class="email-deadline">📅 Date limite de remise des pièces : <strong><?= htmlspecialchars($dateLimite, ENT_QUOTES, 'UTF-8') ?></strong></p>
      <?php endif; ?>

      <div class="email-cta email-cta--wide">
        <a href="https://ri-amu.app/" class="btn-primary btn-primary--lg">Consulter mon espace</a>
      </div>
      <p class="email-note">Pour toute question, contactez le service Relations Internationales.</p>
      <p class="email-footer">Email automatique • Service RI AMU</p>
    </div>
  </div>
</body>
</html>
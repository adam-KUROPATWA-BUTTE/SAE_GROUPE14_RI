<?php
/**
 * Partial : Section statut global du dossier (select + bouton)
 *
 * @var string  $numEtu
 * @var string  $currentStatus
 * @var Closure $t
 * @var string  $userRole
 */

$isAdmin = (($_SESSION['role'] ?? $userRole) === 'admin');

$statusLabels = [
    'depot'       => $t(['fr' => 'Dépôt',          'en' => 'Submitted']),
    'instruction' => $t(['fr' => 'En instruction', 'en' => 'Under Review']),
    'accepte'     => $t(['fr' => 'Accepté',        'en' => 'Accepted']),
    'refuse'      => $t(['fr' => 'Refusé',         'en' => 'Refused']),
];
?>
<div class="form-section global-status-section full-width">
    <div class="global-status-block">
        <strong class="global-status-title"><?= $t(['fr' => 'Statut GLOBAL du dossier :', 'en' => 'GLOBAL Folder status:']) ?></strong>
        <div class="global-status-controls">
            <select id="global_status_select" class="global-status-select" <?= !$isAdmin ? 'disabled' : '' ?>>
                <option value="depot"       <?= $currentStatus === 'depot'       ? 'selected' : '' ?>><?= $statusLabels['depot']       ?></option>
                <option value="instruction" <?= $currentStatus === 'instruction' ? 'selected' : '' ?>><?= $statusLabels['instruction'] ?></option>
                <option value="accepte"     <?= $currentStatus === 'accepte'     ? 'selected' : '' ?>><?= $statusLabels['accepte']     ?></option>
                <option value="refuse"      <?= $currentStatus === 'refuse'      ? 'selected' : '' ?>><?= $statusLabels['refuse']      ?></option>
            </select>
            <?php if ($isAdmin) : ?>
                <button type="button" class="btn-secondary"
                        onclick="window.folderManager.updateGlobalStatus('<?= $numEtu ?>')">
                    <?= $t(['fr' => 'Mettre à jour le statut', 'en' => 'Update Status']) ?>
                </button>
                <span id="global_status_indicator" class="status-indicator"></span>
            <?php endif; ?>
        </div>
    </div>
</div>
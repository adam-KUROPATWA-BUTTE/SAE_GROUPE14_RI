<?php
/**
 * Partial : Section statut global du dossier (select + bouton)
 *
 * @var string  $numEtu        NumEtu déjà htmlspecialchars-é
 * @var string  $currentStatus statut courant du dossier
 * @var Closure $t
 */
?>
<div class="form-section global-status-section full-width">
    <div class="global-status-block">
        <strong class="global-status-title"><?= $t(['fr' => 'Statut GLOBAL du dossier :', 'en' => 'GLOBAL Folder status:']) ?></strong>
        <div class="global-status-controls">
            <select id="global_status_select" class="global-status-select">
                <option value="depot"       <?= $currentStatus === 'depot'       ? 'selected' : '' ?>><?= $t(['fr' => 'Dépôt',         'en' => 'Submitted'])    ?></option>
                <option value="instruction" <?= $currentStatus === 'instruction' ? 'selected' : '' ?>><?= $t(['fr' => 'En instruction', 'en' => 'Under Review']) ?></option>
                <option value="accepte"     <?= $currentStatus === 'accepte'     ? 'selected' : '' ?>><?= $t(['fr' => 'Accepté',        'en' => 'Accepted'])     ?></option>
                <option value="refuse"      <?= $currentStatus === 'refuse'      ? 'selected' : '' ?>><?= $t(['fr' => 'Refusé',         'en' => 'Refused'])      ?></option>
            </select>
            <button type="button" class="btn-secondary"
                    onclick="window.folderManager.updateGlobalStatus('<?= $numEtu ?>')">
                <?= $t(['fr' => 'Mettre à jour le statut', 'en' => 'Update Status']) ?>
            </button>
            <span id="global_status_indicator" class="status-indicator"></span>
        </div>
    </div>
</div>
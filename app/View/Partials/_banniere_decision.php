<?php
/**
 * Partial : Bannière décision rapide (chef de département uniquement)
 *
 * @var string $numEtu
 * @var string $currentStatus
 * @var string $lang
 * @var Closure $t
 */
?>
<div class="banniere-decision">
    <span class="banniere-decision-label"><?= $t(['fr' => 'Décision sur le dossier :', 'en' => 'Decision on folder:']) ?></span>
    <div class="banniere-decision-buttons">
        <button type="button"
                class="btn-decision btn-accepter <?= $currentStatus === 'accepte' ? 'btn-decision-active' : '' ?>"
                onclick="window.folderManager.setDecision('<?= $numEtu ?>', 'accepte', this)">
            ✅ <?= $t(['fr' => 'Accepter le dossier', 'en' => 'Accept Folder']) ?>
        </button>
        <button type="button"
                class="btn-decision btn-refuser <?= $currentStatus === 'refuse' ? 'btn-decision-active' : '' ?>"
                onclick="window.folderManager.setDecision('<?= $numEtu ?>', 'refuse', this)">
            ❌ <?= $t(['fr' => 'Refuser le dossier', 'en' => 'Refuse Folder']) ?>
        </button>
        <span id="decision_indicator" class="status-indicator"></span>
    </div>
</div>
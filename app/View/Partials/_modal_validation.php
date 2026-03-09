<?php
/**
 * Partial : Modal de validation du dossier
 *
 * @var string  $numEtu       NumEtu déjà htmlspecialchars-é
 * @var string  $redirectPage page de redirection après validation
 * @var string  $lang
 * @var Closure $t
 */
?>
<div id="modal-validation" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2><?= $t(['fr' => '📋 Validation du dossier', 'en' => '📋 Folder Validation']) ?></h2>
        </div>
        <form id="form-validation" method="POST" action="index.php?page=valider_documents&lang=<?= htmlspecialchars($lang) ?>">
            <input type="hidden" name="numetu"      value="<?= $numEtu ?>">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirectPage) ?>">

            <div class="modal-section" id="section-modifications" style="display:none;">
                <h3>📝 <?= $t(['fr' => 'Modifications effectuées', 'en' => 'Changes Made']) ?></h3>
                <div class="modifications-list"><ul id="liste-modifications"></ul></div>
            </div>

            <div class="modal-section" id="section-manquants">
                <h3>⚠️ <?= $t(['fr' => 'Documents manquants', 'en' => 'Missing Documents']) ?></h3>
                <div id="liste-manquants"></div>
            </div>

            <div class="modal-section" id="section-presents">
                <h3>✅ <?= $t(['fr' => 'Documents à valider', 'en' => 'Documents to Validate']) ?></h3>
                <div id="liste-presents"></div>
            </div>


            <div class="modal-actions">
                <button type="button" class="btn-modal btn-modal-cancel" id="btn-modal-cancel">
                    <?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?>
                </button>
                <button type="submit" class="btn-modal btn-modal-submit">
                    <?= $t(['fr' => '✉️ Enregistrer et notifier', 'en' => '✉️ Save and Notify']) ?>
                </button>
            </div>
        </form>
    </div>
</div>
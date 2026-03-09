<?php
/**
 * Partial : Bannière date limite de dépôt
 *
 * @var string $numEtu
 * @var string|null $dateLimite
 * @var string $redirectPage   ex: 'coordinateur-stage'
 * @var string $lang
 * @var Closure $t
 */
?>
<div class="banniere-date-limite <?= $dateLimite ? 'avec-date' : 'sans-date' ?>">
    <span class="banniere-icone">📅</span>
    <?php if ($dateLimite) : ?>
        <span class="banniere-texte">
            <?= $t(['fr' => 'Date limite de dépôt :', 'en' => 'Submission deadline:']) ?>
            <strong><?= htmlspecialchars(date('d/m/Y', (int) strtotime($dateLimite))) ?></strong>
        </span>
    <?php else : ?>
        <span class="banniere-texte"><?= $t(['fr' => 'Aucune date limite définie', 'en' => 'No deadline set']) ?></span>
    <?php endif; ?>
    <button type="button" class="banniere-btn-edit" id="btn-edit-date-limite">
        <?= $dateLimite ? $t(['fr' => '✏️ Modifier', 'en' => '✏️ Edit']) : $t(['fr' => '+ Définir une date', 'en' => '+ Set a date']) ?>
    </button>
    <div class="banniere-date-form" id="form-date-limite" style="display:none;">
        <form method="POST" action="index.php?page=valider_documents&lang=<?= htmlspecialchars($lang) ?>"
              style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <input type="hidden" name="numetu"      value="<?= $numEtu ?>">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirectPage) ?>">
            <input type="date" name="date_limite"
                   value="<?= htmlspecialchars($dateLimite ?? '') ?>"
                   min="<?= date('Y-m-d') ?>"
                   class="date-limite-input" style="width:auto;">
            <button type="submit" class="btn-secondary"><?= $t(['fr' => 'Enregistrer', 'en' => 'Save']) ?></button>
            <button type="button" id="btn-cancel-date" class="btn-secondary"><?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?></button>
        </form>
    </div>
</div>
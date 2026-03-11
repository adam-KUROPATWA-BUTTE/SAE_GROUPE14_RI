<?php
/**
 * Partial : Bannière décision rapide (chef de département uniquement)
 *
 * @var string $numEtu
 * @var string $currentStatus   — valeur de avis_chef_departement ('accepte', 'refuse', ou '')
 * @var string $lang
 * @var Closure $t
 */

$numEtuRaw = strval($studentData['NumEtu'] ?? '');
?>
<div class="banniere-decision">
    <span class="banniere-decision-label"><?= $t(['fr' => 'Décision sur le dossier :', 'en' => 'Decision on profile:']) ?></span>
    <div class="banniere-decision-buttons">

        <!-- Bouton Accepter -->
        <form method="POST" action="index.php?page=chef-departement&action=view&numetu=<?= urlencode($numEtuRaw) ?>&lang=<?= htmlspecialchars($lang) ?>" style="display:inline;">
            <input type="hidden" name="set_avis_chef" value="1">
            <input type="hidden" name="numetu" value="<?= htmlspecialchars($numEtuRaw) ?>">
            <input type="hidden" name="avis" value="accepte">
            <button type="submit"
                    class="btn-decision btn-accepter <?= $currentStatus === 'accepte' ? 'btn-decision-active' : '' ?>">
                ✅ <?= $t(['fr' => 'Accepter le dossier', 'en' => 'Accept Profile']) ?>
            </button>
        </form>

        <!-- Bouton Refuser -->
        <form method="POST" action="index.php?page=chef-departement&action=view&numetu=<?= urlencode($numEtuRaw) ?>&lang=<?= htmlspecialchars($lang) ?>" style="display:inline;">
            <input type="hidden" name="set_avis_chef" value="1">
            <input type="hidden" name="numetu" value="<?= htmlspecialchars($numEtuRaw) ?>">
            <input type="hidden" name="avis" value="refuse">
            <button type="submit"
                    class="btn-decision btn-refuser <?= $currentStatus === 'refuse' ? 'btn-decision-active' : '' ?>">
                ❌ <?= $t(['fr' => 'Refuser le dossier', 'en' => 'Refuse Profile']) ?>
            </button>
        </form>

        <span id="decision_indicator" class="status-indicator"></span>
    </div>
</div>
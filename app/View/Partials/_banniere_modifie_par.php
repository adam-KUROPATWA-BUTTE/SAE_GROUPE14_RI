<?php
/**
 * Partial : Bannière "Modifié par … le …"
 *
 * @var string|null $modifiePar   Nom de l'admin ayant fait la dernière modif
 * @var string|null $modifieLe    Date/heure de la dernière modif (format Y-m-d H:i:s)
 * @var Closure     $t
 */
?>
<div class="banniere-modifie-par">
    <span class="banniere-modifie-par__icone">✏️</span>
    <span class="banniere-modifie-par__texte">
        <?php if (!empty($modifiePar) || !empty($modifieLe)) : ?>
            <?= $t(['fr' => 'Dernière modification par', 'en' => 'Last modified by']) ?>
            <strong><?= htmlspecialchars($modifiePar ?? '') ?></strong>            <?php if (!empty($modifieLe)) : ?>
                <?= $t(['fr' => 'le', 'en' => 'on']) ?>
                <strong><?= htmlspecialchars(date('d/m/Y', (int) strtotime($modifieLe))) ?></strong>
                <?= $t(['fr' => 'à', 'en' => 'at']) ?>
                <strong><?= htmlspecialchars(date('H:i', (int) strtotime($modifieLe))) ?></strong>
            <?php endif; ?>
        <?php else : ?>
            <?= $t(['fr' => 'Folder jamais modifié', 'en' => 'Folder never modified']) ?>
        <?php endif; ?>
    </span>
</div>


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
            <strong><?= htmlspecialchars($modifiePar ?? 'Administrateur') ?></strong>
            <?php if (!empty($modifieLe)) : ?>
                <?= $t(['fr' => 'le', 'en' => 'on']) ?>
                <strong><?= htmlspecialchars(date('d/m/Y', strtotime($modifieLe))) ?></strong>
                <?= $t(['fr' => 'à', 'en' => 'at']) ?>
                <strong><?= htmlspecialchars(date('H:i', strtotime($modifieLe))) ?></strong>
            <?php endif; ?>
        <?php else : ?>
            <?= $t(['fr' => 'Dossier jamais modifié', 'en' => 'Folder never modified']) ?>
        <?php endif; ?>
    </span>
</div>

<style>
.banniere-modifie-par {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f0f4ff;
    border-left: 4px solid #4a6cf7;
    border-radius: 6px;
    padding: 10px 16px;
    margin-bottom: 16px;
    font-size: 0.9rem;
    color: #333;
}
.banniere-modifie-par__icone {
    font-size: 1rem;
    flex-shrink: 0;
}
.banniere-modifie-par__texte strong {
    color: #1a3ecf;
}
</style>
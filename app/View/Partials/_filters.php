<?php
/**
 * Partial : Filtres de la liste des étudiants
 *
 * @var array<string, mixed> $filters
 * @var bool                 $hasActiveFilters
 * @var string               $resetUrl          URL de réinitialisation des filtres
 * @var bool                 $showAccordFilter  true = afficher le filtre Accord (admin only)
 * @var Closure              $t
 */

$showAccordFilter = $showAccordFilter ?? false;
?>
<div class="filters">
    <div class="filter-group">
        <label>
            <input type="checkbox" name="entrant_sortant" value="entrant"
                <?= (strval($filters['type'] ?? '')) === 'entrant' ? 'checked' : '' ?>>
            <?= $t(['fr' => 'Entrant', 'en' => 'Incoming']) ?>
        </label>
        <label>
            <input type="checkbox" name="entrant_sortant" value="sortant"
                <?= (strval($filters['type'] ?? '')) === 'sortant' ? 'checked' : '' ?>>
            <?= $t(['fr' => 'Sortant', 'en' => 'Outgoing']) ?>
        </label>
    </div>

    <div class="filter-group">
        <label>
            <input type="checkbox" name="zone" value="europe"
                <?= (strval($filters['zone'] ?? '')) === 'europe' ? 'checked' : '' ?>>
            <?= $t(['fr' => 'Europe', 'en' => 'Europe']) ?>
        </label>
        <label>
            <input type="checkbox" name="zone" value="hors_europe"
                <?= (strval($filters['zone'] ?? '')) === 'hors_europe' ? 'checked' : '' ?>>
            <?= $t(['fr' => 'Hors-Europe', 'en' => 'Non-Europe']) ?>
        </label>
    </div>

    <div class="filter-group">
        <label for="filter-complet"><?= $t(['fr' => 'Statut :', 'en' => 'Status:']) ?></label>
        <select id="filter-complet">
            <option value="all" <?= (strval($filters['complet'] ?? 'all')) === 'all' ? 'selected' : '' ?>><?= $t(['fr' => 'Tous',      'en' => 'All'])        ?></option>
            <option value="1"   <?= (strval($filters['complet'] ?? ''))    === '1'   ? 'selected' : '' ?>><?= $t(['fr' => 'Complet',   'en' => 'Complete'])   ?></option>
            <option value="0"   <?= (strval($filters['complet'] ?? ''))    === '0'   ? 'selected' : '' ?>><?= $t(['fr' => 'Incomplet', 'en' => 'Incomplete']) ?></option>
        </select>
    </div>

    <div class="filter-group">
        <label for="filter-composante"><?= $t(['fr' => 'Composante :', 'en' => 'Component:']) ?></label>
        <select id="filter-composante" name="composante">
            <option value="all"       <?= (strval($filters['composante'] ?? 'all')) === 'all'       ? 'selected' : '' ?>><?= $t(['fr' => 'Toutes', 'en' => 'All']) ?></option>
            <option value="AMU CIVIS" <?= (strval($filters['composante'] ?? ''))    === 'AMU CIVIS' ? 'selected' : '' ?>>AMU CIVIS</option>
            <option value="IUT"       <?= (strval($filters['composante'] ?? ''))    === 'IUT'       ? 'selected' : '' ?>>IUT</option>
        </select>
    </div>

    <?php if ($showAccordFilter) : ?>
        <div class="filter-group">
            <label for="filter-accord"><?= $t(['fr' => 'Accord :', 'en' => 'Agreement:']) ?></label>
            <select id="filter-accord" name="accord">
                <option value="all"       <?= (strval($filters['accord'] ?? 'all')) === 'all'        ? 'selected' : '' ?>><?= $t(['fr' => 'Tous', 'en' => 'All']) ?></option>
                <option value="Erasmus"   <?= (strval($filters['accord'] ?? ''))    === 'Erasmus'     ? 'selected' : '' ?>>Erasmus</option>
                <option value="Bilatéral" <?= (strval($filters['accord'] ?? ''))    === 'Bilatéral'   ? 'selected' : '' ?>>Bilatéral</option>
            </select>
        </div>
    <?php endif; ?>

    <div class="filter-group">
        <?php if ($hasActiveFilters) : ?>
            <a href="<?= $resetUrl ?>" class="btn-reset">
                <?= $t(['fr' => 'Réinitialiser les filtres', 'en' => 'Reset filters']) ?>
            </a>
        <?php endif; ?>
    </div>
</div>
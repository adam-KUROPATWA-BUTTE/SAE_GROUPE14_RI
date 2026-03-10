<?php
/**
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array{student: string, dept: string, year: string, type: string, camp: string, dest: string, cadre: string} $filters
 * @var array<int, array<string, mixed>> $outgoing
 * @var array<int, array<string, mixed>> $incoming
 * @var array<int, string> $departments
 */

ob_start();
?>

    <h1 class="suivi-global"><?= $t(['fr' => 'Suivi Global des Mobilités', 'en' => 'Global Mobility Tracking']) ?></h1>

    <form class="filters-container" method="GET" action="index.php">
        <input type="hidden" name="page" value="dashboard-admin">
        <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">

        <input type="text" name="student" placeholder="<?= $t(['fr' => 'Rechercher...', 'en' => 'Search...']) ?>" value="<?= htmlspecialchars($filters['student']) ?>" onchange="this.form.submit()">

        <select name="dept" onchange="this.form.submit()">
            <option value=""><?= $t(['fr' => 'Départements', 'en' => 'Departments']) ?></option>
            <?php foreach ($departments as $deptCode) : ?>
                <option value="<?= htmlspecialchars($deptCode) ?>" <?= $filters['dept'] === $deptCode ? 'selected' : '' ?>>
                    <?= htmlspecialchars($deptCode) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="year" onchange="this.form.submit()">
            <option value=""><?= $t(['fr' => 'Année', 'en' => 'Year']) ?></option>
            <option value="2024-2025" <?= $filters['year'] === '2024-2025' ? 'selected' : '' ?>>24-25</option>
        </select>

        <select name="camp" onchange="this.form.submit()">
            <option value=""><?= $t(['fr' => 'Campagne', 'en' => 'Campaign']) ?></option>
            <option value="Automne 2024" <?= $filters['camp'] === 'Automne 2024' ? 'selected' : '' ?>>Automne 24</option>
        </select>

        <input type="text" name="dest" placeholder="<?= $t(['fr' => 'Destination', 'en' => 'Destination']) ?>" value="<?= htmlspecialchars($filters['dest']) ?>" onchange="this.form.submit()">

        <div class="filter-group framework-group">
            <strong class="framework-label"><?= $t(['fr' => 'Cadre :', 'en' => 'Framework:']) ?></strong>

            <label>
                <input type="radio" name="cadre" value="" onchange="this.form.submit()" <?= $filters['cadre'] === '' ? 'checked' : '' ?>>
                <?= $t(['fr' => 'Tous', 'en' => 'All']) ?>
            </label>
            <label>
                <input type="radio" name="cadre" value="AMU CIVIS" onchange="this.form.submit()" <?= $filters['cadre'] === 'AMU CIVIS' ? 'checked' : '' ?>>
                AMU CIVIS
            </label>
            <label>
                <input type="radio" name="cadre" value="IUT" onchange="this.form.submit()" <?= $filters['cadre'] === 'IUT' ? 'checked' : '' ?>>
                IUT
            </label>
            <label>
                <input type="radio" name="cadre" value="Erasmus" onchange="this.form.submit()" <?= $filters['cadre'] === 'Erasmus' ? 'checked' : '' ?>>
                ERASMUS
            </label>
            <label>
                <input type="radio" name="cadre" value="Bilatéral" onchange="this.form.submit()" <?= $filters['cadre'] === 'Bilatéral' ? 'checked' : '' ?>>
                BILATÉRAL
            </label>
        </div>
    </form>

    <div class="section-composante">
        <div class="barre-titre" onclick="window.dashboardManager.toggleAccordion('sortants')">
            <span><?= $t(['fr' => 'Dossiers Sortants', 'en' => 'Outgoing Files']) ?> (<?= count($outgoing) ?>)</span>
            <span class="fleche" id="fleche-sortants">▼</span>
        </div>

        <div id="contenu-sortants" class="contenu-dossiers">
            <div class="table-responsive">
                <?php if (empty($outgoing)) : ?>
                    <p class="no-files"><?= $t(['fr' => 'Aucun dossier.', 'en' => 'No files.']) ?></p>
                <?php else : ?>
                    <table>
                        <thead>
                        <tr>
                            <th><?= $t(['fr' => 'Étudiant',    'en' => 'Student']) ?></th>
                            <th><?= $t(['fr' => 'Département', 'en' => 'Department']) ?></th>
                            <th><?= $t(['fr' => 'Destination', 'en' => 'Destination']) ?></th>
                            <th><?= $t(['fr' => 'Campagne',    'en' => 'Campaign']) ?></th>
                            <th><?= $t(['fr' => 'Année',       'en' => 'Year']) ?></th>
                            <th><?= $t(['fr' => 'État',        'en' => 'Status']) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($outgoing as $d) :
                            $pct        = intval($d['calc_percentage']);
                            $badgeClass = ($pct >= 100) ? 'bg-success' : (($pct > 50) ? 'bg-warning' : 'bg-danger');
                            $label      = ($pct >= 100) ? $t(['fr' => 'Validé', 'en' => 'Validated']) : $pct . '%';
                            $numEtu     = strval($d['NumEtu'] ?? '');
                            $detailUrl  = "index.php?page=folders-admin&action=view&numetu=" . urlencode($numEtu) . "&lang=" . urlencode($lang);
                            ?>
                            <tr onclick="window.location.href='<?= $detailUrl ?>'" class="clickable-row">
                                <td>
                                    <strong><?= htmlspecialchars(strval($d['Nom'] ?? '') . ' ' . strval($d['Prenom'] ?? '')) ?></strong>
                                    <br><small><?= htmlspecialchars($numEtu) ?></small>
                                </td>
                                <td><?= htmlspecialchars(strval($d['CodeDepartement'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(strval($d['Destination'] ?? $d['Pays'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(strval($d['calc_camp'])) ?></td>
                                <td><?= htmlspecialchars(strval($d['calc_annee'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>"><?= $label ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr class="separator">

    <div class="section-composante">
        <div class="barre-titre" onclick="window.dashboardManager.toggleAccordion('entrants')">
            <span><?= $t(['fr' => 'Dossiers Entrants', 'en' => 'Incoming Files']) ?> (<?= count($incoming) ?>)</span>
            <span class="fleche" id="fleche-entrants">▼</span>
        </div>

        <div id="contenu-entrants" class="contenu-dossiers">
            <div class="table-responsive">
                <?php if (empty($incoming)) : ?>
                    <p class="no-files"><?= $t(['fr' => 'Aucun dossier.', 'en' => 'No files.']) ?></p>
                <?php else : ?>
                    <table>
                        <thead>
                        <tr>
                            <th><?= $t(['fr' => 'Étudiant',    'en' => 'Student']) ?></th>
                            <th><?= $t(['fr' => 'Département', 'en' => 'Department']) ?></th>
                            <th><?= $t(['fr' => 'Destination', 'en' => 'Destination']) ?></th>
                            <th><?= $t(['fr' => 'Campagne',    'en' => 'Campaign']) ?></th>
                            <th><?= $t(['fr' => 'Année',       'en' => 'Year']) ?></th>
                            <th><?= $t(['fr' => 'État',        'en' => 'Status']) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($incoming as $d) :
                            $pct        = intval($d['calc_percentage']);
                            $badgeClass = ($pct >= 100) ? 'bg-success' : (($pct > 50) ? 'bg-warning' : 'bg-danger');
                            $label      = ($pct >= 100) ? $t(['fr' => 'Validé', 'en' => 'Validated']) : $pct . '%';
                            $numEtu     = strval($d['NumEtu'] ?? '');
                            $detailUrl  = "index.php?page=folders-admin&action=view&numetu=" . urlencode($numEtu) . "&lang=" . urlencode($lang);
                            ?>
                            <tr onclick="window.location.href='<?= $detailUrl ?>'" class="clickable-row">
                                <td>
                                    <strong><?= htmlspecialchars(strval($d['Nom'] ?? '') . ' ' . strval($d['Prenom'] ?? '')) ?></strong>
                                    <br><small><?= htmlspecialchars($numEtu) ?></small>
                                </td>
                                <td><?= htmlspecialchars(strval($d['CodeDepartement'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(strval($d['Destination'] ?? $d['Pays'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(strval($d['calc_camp'])) ?></td>
                                <td><?= htmlspecialchars(strval($d['calc_annee'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>"><?= $label ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
<div id="app-config"
     data-lang="<?= htmlspecialchars($lang) ?>"
     data-role="admin"
     style="display:none;">
</div>
<?php
$content = ob_get_clean();

$title = $t(['fr' => 'Tableau de bord Admin', 'en' => 'Admin Dashboard']);
$styles = ['styles/folders.css', 'styles/dashboard.css', 'styles/index.css', 'styles/chatbot.css'];
$scripts = ['js/dashboard.js'];
$activeMenu = 'dashboard';
$userRole = 'admin';

include __DIR__ . '/../Layout/base.php';
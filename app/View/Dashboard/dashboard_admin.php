<?php
/**
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array{student: string, dept: string, year: string, type: string, camp: string, dest: string, cadre: string} $filters
 * @var array<int, array<string, mixed>> $outgoing
 * @var array<int, array<string, mixed>> $incoming
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
            <option value="Informatique" <?= $filters['dept'] === 'Informatique' ? 'selected' : '' ?>>Info</option>
            <option value="GEA" <?= $filters['dept'] === 'GEA' ? 'selected' : '' ?>>GEA</option>
            <option value="Biologie" <?= $filters['dept'] === 'Biologie' ? 'selected' : '' ?>>Bio</option>
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

        <div class="filter-group" style="grid-column: 1 / -1; display: flex; gap: 15px; flex-wrap: wrap; margin-top: 5px; align-items: center; padding: 10px; background: #fff; border: 1px solid #ccc; border-radius: 4px;">
            <strong style="margin-right: 10px;"><?= $t(['fr' => 'Cadre :', 'en' => 'Framework:']) ?></strong>

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
            <div class="table-responsive" style="padding: 15px;">
                <?php if (empty($outgoing)) : ?>
                    <p class="no-files"><?= $t(['fr' => 'Aucun dossier.', 'en' => 'No files.']) ?></p>
                <?php else : ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Dept</th>
                            <th>Dest</th>
                            <th>Campagne</th>
                            <th>Année</th>
                            <th>État</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($outgoing as $d) :
                            $pct = intval($d['calc_percentage']);
                            $badgeClass = ($pct >= 100) ? 'bg-success' : (($pct > 50) ? 'bg-warning' : 'bg-danger');
                            $label = ($pct >= 100) ? 'Validé' : $pct . '%';
                            $numEtu = strval($d['NumEtu'] ?? '');
                            $detailUrl = "index.php?page=folders-admin&action=view&numetu=" . urlencode($numEtu) . "&lang=" . urlencode($lang);
                            ?>
                            <tr onclick="window.location.href='<?= $detailUrl ?>'" class="clickable-row">
                                <td>
                                    <strong><?= htmlspecialchars(strval($d['Nom'] ?? '') . ' ' . strval($d['Prenom'] ?? '')) ?></strong>
                                    <br><small><?= htmlspecialchars($numEtu) ?></small>
                                </td>
                                <td><?= htmlspecialchars(strval($d['CodeDepartement'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(strval($d['Destination'] ?? '')) ?></td>
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

    <hr class="separator" style="border: 0; height: 1px; background: #eee; margin: 20px 0;">

    <div class="section-composante">
        <div class="barre-titre" onclick="window.dashboardManager.toggleAccordion('entrants')">
            <span><?= $t(['fr' => 'Dossiers Entrants', 'en' => 'Incoming Files']) ?> (<?= count($incoming) ?>)</span>
            <span class="fleche" id="fleche-entrants">▼</span>
        </div>

        <div id="contenu-entrants" class="contenu-dossiers">
            <div class="table-responsive" style="padding: 15px;">
                <?php if (empty($incoming)) : ?>
                    <p class="no-files"><?= $t(['fr' => 'Aucun dossier.', 'en' => 'No files.']) ?></p>
                <?php else : ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Dept</th>
                            <th>Type</th>
                            <th>Année</th>
                            <th>État</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($incoming as $d) :
                            $pct = intval($d['calc_percentage']);
                            $badgeClass = ($pct >= 100) ? 'bg-success' : (($pct > 50) ? 'bg-warning' : 'bg-danger');
                            $label = ($pct >= 100) ? 'Validé' : $pct . '%';
                            $numEtu = strval($d['NumEtu'] ?? '');
                            $detailUrl = "index.php?page=folders-admin&action=view&numetu=" . urlencode($numEtu) . "&lang=" . urlencode($lang);
                            ?>
                            <tr onclick="window.location.href='<?= $detailUrl ?>'" class="clickable-row">
                                <td>
                                    <strong><?= htmlspecialchars(strval($d['Nom'] ?? '') . ' ' . strval($d['Prenom'] ?? '')) ?></strong>
                                    <br><small><?= htmlspecialchars($numEtu) ?></small>
                                </td>
                                <td><?= htmlspecialchars(strval($d['CodeDepartement'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(strval($d['Type'] ?? '')) ?></td>
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

<?php
$content = ob_get_clean();

$title = $t(['fr' => 'Tableau de bord Admin', 'en' => 'Admin Dashboard']);
$styles = ['styles/folders.css', 'styles/dashboard.css', 'styles/index.css', 'styles/chatbot.css'];
$scripts = ['js/dashboard.js'];
$activeMenu = 'dashboard';
$userRole = 'admin';

include __DIR__ . '/../Layout/base.php';
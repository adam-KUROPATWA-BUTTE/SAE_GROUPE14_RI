<?php
/**
 * Page Coordinateur de stage
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var bool $isLoggedIn
 * @var array<string, mixed> $filters
 * @var int $page
 * @var string $message
 * @var array<int, array<string, mixed>> $paginatedData
 * @var int $totalCount
 * @var int $totalPages
 * @var array<string, mixed>|null $studentData
 * @var string $action
 */

$buildPaginationUrl = function(int $p) use ($filters, $lang): string {
    $params = array_merge($filters, [
        'p'    => $p,
        'page' => 'coordinateur-stage',
        'lang' => $lang,
    ]);
    return 'index.php?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
};

$hasActiveFilters = (strval($filters['type']    ?? 'all')) !== 'all'
    || (strval($filters['zone']    ?? 'all')) !== 'all'
    || (strval($filters['complet'] ?? 'all')) !== 'all'
    || (strval($filters['composante'] ?? 'all')) !== 'all'
    || !empty($filters['search']);

ob_start();
?>


<?php if ($action === 'view') : ?>

    <?php if (!$studentData) : ?>
        <p><?= $t(['fr' => 'Étudiant non trouvé', 'en' => 'Student not found']) ?></p>
    <?php else : ?>
        <?php
        $pieces = (isset($studentData['pieces']) && is_array($studentData['pieces'])) ? $studentData['pieces'] : [];
        $detectedType = '';
        if (!empty($pieces['convention']['file'])) {
            $detectedType = 'stage';
        } elseif (!empty($pieces['lettre_motivation']['file'])) {
            $detectedType = 'etudes';
        }
        $numEtu = htmlspecialchars(strval($studentData['NumEtu'] ?? ''));
        ?>

        <h1><?= $t(['fr' => 'Dossier étudiant', 'en' => 'Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'coordinateur-stage']) ?>'" class="btn-secondary">
                <?= $t(['fr' => 'Retour à la liste', 'en' => 'Back to List']) ?>
            </button>
        </div>

        <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu_display"><?= $t(['fr' => 'NumÉtu', 'en' => 'Student ID']) ?></label>
                <input type="text" id="numetu_display" value="<?= $numEtu ?>" disabled class="input-disabled">
                <input type="hidden" name="numetu" value="<?= $numEtu ?>">

                <label><?= $t(['fr' => 'Nom', 'en' => 'Last Name']) ?></label>
                <input type="text" name="nom" value="<?= htmlspecialchars(strval($studentData['Nom'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Prénom', 'en' => 'First Name']) ?></label>
                <input type="text" name="prenom" value="<?= htmlspecialchars(strval($studentData['Prenom'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Né(e) le', 'en' => 'Date of Birth']) ?></label>
                <input type="date" name="naissance" value="<?= htmlspecialchars(strval($studentData['DateNaissance'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Sexe', 'en' => 'Gender']) ?></label>
                <select name="sexe" disabled class="input-disabled">
                    <option value="M"     <?= ($studentData['Sexe'] ?? '') === 'M'     ? 'selected' : '' ?>><?= $t(['fr' => 'Masculin', 'en' => 'Male']) ?></option>
                    <option value="F"     <?= ($studentData['Sexe'] ?? '') === 'F'     ? 'selected' : '' ?>><?= $t(['fr' => 'Féminin',  'en' => 'Female']) ?></option>
                    <option value="Autre" <?= ($studentData['Sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>><?= $t(['fr' => 'Autre',    'en' => 'Other']) ?></option>
                </select>

                <label><?= $t(['fr' => 'Adresse', 'en' => 'Address']) ?></label>
                <input type="text" name="adresse" value="<?= htmlspecialchars(strval($studentData['Adresse'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Code postal', 'en' => 'Postal Code']) ?></label>
                <input type="text" name="cp" value="<?= htmlspecialchars(strval($studentData['CodePostal'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Ville', 'en' => 'City']) ?></label>
                <input type="text" name="ville" value="<?= htmlspecialchars(strval($studentData['Ville'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Email Personnel', 'en' => 'Personal Email']) ?></label>
                <input type="email" name="email_perso" value="<?= htmlspecialchars(strval($studentData['EmailPersonnel'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Email AMU', 'en' => 'AMU Email']) ?></label>
                <input type="email" name="email_amu" value="<?= htmlspecialchars(strval($studentData['EmailAMU'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Téléphone', 'en' => 'Phone']) ?></label>
                <input type="text" name="telephone" value="<?= htmlspecialchars(strval($studentData['Telephone'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Composante', 'en' => 'Component']) ?></label>
                <input type="text" name="composante" value="<?= htmlspecialchars(strval($studentData['Composante'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Département', 'en' => 'Department']) ?></label>
                <input type="text" name="departement" value="<?= htmlspecialchars(strval($studentData['CodeDepartement'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Campus', 'en' => 'Campus']) ?></label>
                <input type="text" name="campus" value="<?= htmlspecialchars(strval($studentData['Campus'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Discipline', 'en' => 'Discipline']) ?></label>
                <input type="text" name="discipline" value="<?= htmlspecialchars(strval($studentData['Discipline'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Niveau d\'étude', 'en' => 'Study Level']) ?></label>
                <input type="text" name="niveau_etude" value="<?= htmlspecialchars(strval($studentData['NiveauEtude'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Formation', 'en' => 'Degree Program']) ?></label>
                <input type="text" name="formation" value="<?= htmlspecialchars(strval($studentData['Formation'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Moyenne Bac', 'en' => 'High School Average']) ?></label>
                <input type="text" name="moyenne_bac" value="<?= htmlspecialchars(strval($studentData['MoyenneBac'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Moyenne sans Bac', 'en' => 'Average w/o High School']) ?></label>
                <input type="text" name="moyenne_sans_bac" value="<?= htmlspecialchars(strval($studentData['MoyenneSansBac'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Avis DRI', 'en' => 'DRI Advice']) ?></label>
                <input type="text" name="avis_dri" value="<?= htmlspecialchars(strval($studentData['AvisDRI'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Date de début', 'en' => 'Start Date']) ?></label>
                <input type="text" name="date_debut" value="<?= htmlspecialchars(strval($studentData['DateDebut'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'A déjà effectué une mobilité', 'en' => 'Previous Mobility']) ?></label>
                <input type="text" name="mobilite_anterieure" value="<?= htmlspecialchars(strval($studentData['MobiliteAnterieure'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Pays', 'en' => 'Country']) ?></label>
                <input type="text" name="pays" value="<?= htmlspecialchars(strval($studentData['Pays'] ?? '')) ?>" disabled class="input-disabled">

                <label><?= $t(['fr' => 'Type', 'en' => 'Type']) ?></label>
                <select name="type" disabled class="input-disabled">
                    <option value="entrant" <?= ($studentData['Type'] ?? '') === 'entrant' ? 'selected' : '' ?>><?= $t(['fr' => 'Entrant', 'en' => 'Incoming']) ?></option>
                    <option value="sortant" <?= ($studentData['Type'] ?? '') === 'sortant' ? 'selected' : '' ?>><?= $t(['fr' => 'Sortant', 'en' => 'Outgoing']) ?></option>
                </select>

                <label><?= $t(['fr' => 'Zone', 'en' => 'Zone']) ?></label>
                <select name="zone" disabled class="input-disabled">
                    <option value="europe"      <?= ($studentData['Zone'] ?? '') === 'europe'      ? 'selected' : '' ?>><?= $t(['fr' => 'Europe',      'en' => 'Europe']) ?></option>
                    <option value="hors_europe" <?= ($studentData['Zone'] ?? '') === 'hors_europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Hors Europe', 'en' => 'Non-Europe']) ?></option>
                </select>

                <label><?= $t(['fr' => 'Type de mobilité', 'en' => 'Mobility Type']) ?></label>
                <select name="mobilite_type" disabled class="input-disabled">
                    <option value=""      ><?= $t(['fr' => '-- Choisir --', 'en' => '-- Choose --']) ?></option>
                    <option value="stage"  <?= $detectedType === 'stage'  ? 'selected' : '' ?>><?= $t(['fr' => 'Stage',  'en' => 'Internship']) ?></option>
                    <option value="etudes" <?= $detectedType === 'etudes' ? 'selected' : '' ?>><?= $t(['fr' => 'Études', 'en' => 'Studies']) ?></option>
                </select>
            </div>

            <!-- Pièces justificatives (lecture seule) -->
            <div class="form-section documents-section" style="grid-column: 1 / -1;">
                <h2><?= $t(['fr' => 'Pièces Justificatives', 'en' => 'Supporting Documents']) ?></h2>

                <div class="doc-review-list">
                    <?php
                    $docTypes = [
                        'photo'             => $t(['fr' => 'Photo',                  'en' => 'Photo']),
                        'cv'                => $t(['fr' => 'CV',                     'en' => 'CV']),
                        'convention'        => $t(['fr' => 'Convention de stage',    'en' => 'Internship Agreement']),
                        'lettre_motivation' => $t(['fr' => 'Lettre de motivation',   'en' => 'Motivation Letter']),
                        'langues'           => $t(['fr' => 'Attestation de langues', 'en' => 'Language Certificate']),
                    ];

                    foreach ($docTypes as $key => $label) :
                        $doc    = $pieces[$key] ?? null;
                        $hasDoc = !empty($doc['file']);
                        $status  = $doc['status']  ?? 'pending';
                        $comment = $doc['comment'] ?? '';
                        ?>
                        <div class="doc-review-item" data-doctype="<?= $key ?>">
                            <div class="doc-info">
                                <h4><?= $label ?></h4>
                                <?php if ($hasDoc) : ?>
                                    <a href="data:application/octet-stream;base64,<?= strval($doc['file']) ?>"
                                       download="<?= $key ?>_<?= $numEtu ?>" class="btn-download">
                                        <?= $t(['fr' => 'Télécharger le fichier', 'en' => 'Download file']) ?>
                                    </a>
                                <?php else : ?>
                                    <span class="no-document"><?= $t(['fr' => 'Non fourni', 'en' => 'Not provided']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="doc-actions">
                                <?php if ($hasDoc) : ?>
                                    <div class="status-radios">
                                        <label class="radio-accept">
                                            <input type="radio" name="status_<?= $key ?>" value="accepted"
                                                <?= ($status === 'accepted' || $status === 'pending') ? 'checked' : '' ?>>
                                            <?= $t(['fr' => 'Accepter', 'en' => 'Accept']) ?>
                                        </label>
                                        <label class="radio-refuse">
                                            <input type="radio" name="status_<?= $key ?>" value="refused"
                                                <?= $status === 'refused' ? 'checked' : '' ?>>
                                            <?= $t(['fr' => 'Refuser', 'en' => 'Refuse']) ?>
                                        </label>
                                    </div>
                                    <textarea name="comment_<?= $key ?>"
                                              placeholder="<?= $t(['fr' => 'Ajouter un commentaire...', 'en' => 'Add a comment...']) ?>"><?= htmlspecialchars($comment) ?></textarea>

                                    <div style="display:flex;align-items:center;margin-top:5px;">
                                        <button type="button" class="btn-confirm-doc"
                                                onclick="window.folderManager.confirmDocument('<?= $numEtu ?>', '<?= $key ?>')">
                                            <?= $t(['fr' => 'Confirmer la pièce', 'en' => 'Confirm Document']) ?>
                                        </button>
                                        <span class="doc-save-indicator" id="indicator_<?= $key ?>"></span>
                                    </div>
                                <?php else : ?>
                                    <span class="no-document"><?= $t(['fr' => 'Aucune action disponible', 'en' => 'No action available']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary"
                        onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'coordinateur-stage']) ?>'">
                    <?= $t(['fr' => 'Retour', 'en' => 'Back']) ?>
                </button>
            </div>
        </form>

    <?php endif; ?>

<?php else : ?>

    <h1><?= $t(['fr' => 'Étudiants en mobilité de stage', 'en' => 'Internship Mobility Students']) ?></h1>

    <?php if (!empty($message)) : ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="student-toolbar">
        <div class="search-container-toolbar">
            <label for="search" class="search-label"><?= $t(['fr' => 'Rechercher', 'en' => 'Search']) ?></label>
            <input type="text" id="search" name="search"
                   placeholder="Nom, prénom, email..."
                   value="<?= htmlspecialchars(strval($filters['search'] ?? '')) ?>">
            <button type="button" id="btn-search-loupe" class="btn-search">
                <img src="img/loupe.png" alt="Rechercher">
            </button>
        </div>
    </div>

    <div class="filters-container">
        <p class="filters-title"><?= $t(['fr' => 'Filtres', 'en' => 'Filters']) ?></p>

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

            <div class="filter-group">
                <?php if ($hasActiveFilters) : ?>
                    <a href="<?= $buildUrl('index.php', ['page' => 'coordinateur-stage']) ?>" class="btn-reset">
                        <?= $t(['fr' => 'Réinitialiser les filtres', 'en' => 'Reset filters']) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <p class="results-count"><?= $totalCount ?> <?= $t(['fr' => 'étudiant(s) trouvé(s)', 'en' => 'student(s) found']) ?></p>

        <?php
        $groupedData = [];
        foreach ($paginatedData as $etudiant) {
            $comp = strval($etudiant['Composante'] ?? '');
            if (empty($comp) || $comp === '-') {
                $comp = $t(['fr' => 'Autre / Non assignée', 'en' => 'Other / Unassigned']);
            }
            $groupedData[$comp][] = $etudiant;
        }
        ?>

        <div class="conteneur-composantes">
            <?php foreach ($groupedData as $compName => $students) : ?>
                <?php $safeCompId = md5(strval($compName)); ?>
                <div class="section-composante">
                    <div class="barre-titre" data-target="dossiers-<?= $safeCompId ?>">
                        <span><?= htmlspecialchars(strval($compName)) ?> (<?= count($students) ?>)</span>
                        <span class="fleche">▼</span>
                    </div>

                    <div id="dossiers-<?= $safeCompId ?>" class="contenu-dossiers">
                        <table class="table-etudiants">
                            <thead>
                            <tr>
                                <th><?= $t(['fr' => 'Nom',        'en' => 'Last Name'])  ?></th>
                                <th><?= $t(['fr' => 'Prénom',     'en' => 'First Name']) ?></th>
                                <th><?= $t(['fr' => 'Type',       'en' => 'Type'])       ?></th>
                                <th><?= $t(['fr' => 'Composante', 'en' => 'Component'])  ?></th>
                                <th><?= $t(['fr' => 'Département','en' => 'Department']) ?></th>
                                <th><?= $t(['fr' => 'Mobilité',   'en' => 'Mobility'])   ?></th>
                                <th><?= $t(['fr' => 'Statut',     'en' => 'Status'])     ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $etudiant) : ?>
                                <?php
                                $rawPieces    = strval($etudiant['PiecesJustificatives'] ?? '{}');
                                $decoded      = json_decode($rawPieces, true);
                                $ePieces      = is_array($decoded) ? $decoded : [];

                                $mobilityType = '-';
                                if (!empty($ePieces['convention']['file'])) {
                                    $mobilityType = $t(['fr' => 'Stage',  'en' => 'Internship']);
                                } elseif (!empty($ePieces['lettre_motivation']['file'])) {
                                    $mobilityType = $t(['fr' => 'Études', 'en' => 'Studies']);
                                }

                                $eNumEtu      = strval($etudiant['NumEtu']          ?? '');
                                $eNom         = strval($etudiant['Nom']             ?? '');
                                $ePrenom      = strval($etudiant['Prenom']          ?? '');
                                $eType        = strval($etudiant['Type']            ?? '');
                                $eComposante  = strval($etudiant['Composante']      ?? '-');
                                $eDepartement = strval($etudiant['CodeDepartement'] ?? '-');
                                $eStatus      = strval($etudiant['status']          ?? 'depot');
                                ?>
                                <tr class="clickable-row" data-numetu="<?= htmlspecialchars($eNumEtu) ?>"
                                    onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'coordinateur-stage', 'action' => 'view', 'numetu' => $eNumEtu]) ?>'">
                                    <td><?= htmlspecialchars($eNom) ?></td>
                                    <td><?= htmlspecialchars($ePrenom) ?></td>
                                    <td><?= $t(['fr' => ($eType === 'entrant' ? 'Entrant' : 'Sortant'), 'en' => ($eType === 'entrant' ? 'Incoming' : 'Outgoing')]) ?></td>
                                    <td><?= htmlspecialchars($eComposante  ?: '-') ?></td>
                                    <td><?= htmlspecialchars($eDepartement ?: '-') ?></td>
                                    <td><?= htmlspecialchars($mobilityType) ?></td>
                                    <td>
                                        <?php if ($eStatus === 'accepte') : ?>
                                            <span class="status-complete"   style="color:green;">Accepté</span>
                                        <?php elseif ($eStatus === 'refuse') : ?>
                                            <span class="status-incomplete" style="color:red;">Refusé</span>
                                        <?php elseif ($eStatus === 'instruction') : ?>
                                            <span class="status-incomplete" style="color:orange;">En instruction</span>
                                        <?php else : ?>
                                            <span class="status-incomplete" style="color:grey;">Dépôt</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="pagination accordion-pagination"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div><!-- /.filters-container -->

<?php endif; ?>

<?php
$content = ob_get_clean();

$title = $t([
    'fr' => 'Coordinateur de stage - Relations Internationales AMU',
    'en' => 'Internship Coordinator - International Relations AMU',
]);

$styles          = ['styles/index.css', 'styles/folders.css', 'styles/chatbot.css'];
$scripts         = ['js/folders.js'];
$activeMenu      = 'coordinateur-stage';
$userRole        = 'coordinateur_stage';
$metaDescription = $t([
    'fr' => 'Espace coordinateur de stage — gestion des mobilités étudiantes.',
    'en' => 'Internship coordinator space — student mobility management.',
]);

include __DIR__ . '/../Layout/base.php';
<?php
/**
 * Vue : Dossiers Admin
 *
 * @var string $lang
 * @var string $action
 * @var array<string, mixed>|null $studentData
 * @var array<string, mixed> $filters
 * @var int $totalCount
 * @var array<int, array<string, mixed>> $paginatedData
 * @var string $message
 */

if (!isset($t)) {
    $t = function(array $translations) use ($lang) {
        return $translations[$lang] ?? $translations['fr'] ?? '';
    };
}

$buildUrl = function(string $path, array $params = []) use ($lang): string {
    $params['lang'] = $lang;
    $separator = (strpos($path, '?') === false) ? '?' : '&';
    return $path . $separator . http_build_query($params);
};

$PAGE = 'folders-admin';

$hasActiveFilters = (strval($filters['type']       ?? 'all')) !== 'all'
    || (strval($filters['zone']       ?? 'all')) !== 'all'
    || (strval($filters['complet']    ?? 'all')) !== 'all'
    || (strval($filters['composante'] ?? 'all')) !== 'all'
    || (strval($filters['accord']     ?? 'all')) !== 'all'
    || !empty($filters['date_debut'])
    || !empty($filters['date_fin'])
    || !empty($filters['search']);

ob_start();
?>

<?php if ($action === 'view') : ?>

    <?php if (!$studentData) : ?>
        <p><?= $t(['fr' => 'Étudiant non trouvé', 'en' => 'Student not found']) ?></p>
    <?php else : ?>
        <?php
        $pieces        = (isset($studentData['pieces'])  && is_array($studentData['pieces']))  ? $studentData['pieces']  : [];
        $statuts       = (isset($studentData['statuts']) && is_array($studentData['statuts'])) ? $studentData['statuts'] : [];
        $mobiliteCol  = strtolower(trim(strval($studentData['Mobilite'] ?? '')));
        if ($mobiliteCol === 'stage') {
            $detectedType = 'stage';
        } elseif ($mobiliteCol === 'etude' || $mobiliteCol === 'etudes') {
            $detectedType = 'etude';
        } else {
            $detectedType = !empty($pieces['convention']['file'])         ? 'stage'
                : (!empty($pieces['lettre_motivation']['file']) ? 'etudes' : '');
        }
        $numEtu        = htmlspecialchars(strval($studentData['NumEtu'] ?? ''));
        $dateLimite    = $studentData['DateLimite'] ?? null;
        $currentStatus = $studentData['status'] ?? 'depot';
        ?>

        <?php
        $modifiePar = $studentData['ModifiePar'] ?? null;
        $modifieLe  = $studentData['ModifieLe']  ?? null;
        include __DIR__ . '/../Partials/_banniere_modifie_par.php';
        ?>

        <h1><?= $t(['fr' => 'Dossier étudiant', 'en' => 'Student Profile']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => $PAGE]) ?>'" class="btn-secondary">
                <?= $t(['fr' => 'Retour à la liste', 'en' => 'Back to List']) ?>
            </button>
        </div>

        <?php if (!empty($message)) : ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php
        $redirectPage = $PAGE;
        include __DIR__ . '/../Partials/_banniere_date_limite.php';
        ?>

        <!-- ── AVIS CHEF DE DÉPARTEMENT (lecture seule) ── -->
        <?php
        $avisChef  = strval($studentData['avis_chef_departement'] ?? '');
        $avisLabel = match($avisChef) {
            'accepte' => ['fr' => 'Folder accepté par le chef de département',  'en' => 'Folder accepted by department head'],
            'refuse'  => ['fr' => 'Folder refusé par le chef de département',   'en' => 'Folder refused by department head'],
            default   => ['fr' => 'Aucune décision du chef de département',       'en' => 'No decision from department head'],
        };
        $avisIcon = match($avisChef) {
            'accepte' => '✅',
            'refuse'  => '❌',
            default   => '⏳',
        };
        $avisClass = match($avisChef) {
            'accepte' => 'avis-chef avis-chef--accepte',
            'refuse'  => 'avis-chef avis-chef--refuse',
            default   => 'avis-chef avis-chef--pending',
        };
        ?>
        <div class="<?= $avisClass ?>">
            <span class="avis-chef-icon"><?= $avisIcon ?></span>
            <span class="avis-chef-label"><?= $t(['fr' => 'Avis chef de département :', 'en' => 'Department head decision:']) ?></span>
            <span class="avis-chef-value"><?= $t($avisLabel) ?></span>
        </div>

        <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <?php
                $editableFields = [];
                $allEditable    = true;
                include __DIR__ . '/../Partials/_form_fields.php';
                ?>
            </div>

            <h2><?= $t(['fr' => 'Revue des Pièces Justificatives', 'en' => 'Documents Review']) ?></h2>
            <div class="form-section documents-section full-width">
                <?php
                $languesEditable  = false;
                $allFilesEditable = true;
                include __DIR__ . '/../Partials/_doc_review.php';
                ?>
            </div>

            <?php include __DIR__ . '/../Partials/_global_status.php'; ?>

            <div class="form-actions">
                <button type="button" id="btn-enregistrer" class="btn-secondary">
                    <?= $t(['fr' => '✉️ Enregistrer et notifier', 'en' => '✉️ Save and Notify']) ?>
                </button>
                <button type="button" class="btn-secondary"
                        onclick="window.location.href='<?= $buildUrl('index.php', ['page' => $PAGE]) ?>'">
                    <?= $t(['fr' => 'Retour', 'en' => 'Back']) ?>
                </button>
            </div>
        </form>

        <?php include __DIR__ . '/../Partials/_modal_validation.php'; ?>

        <script>
            <?php
            $repoTemp    = new \Model\Persistence\FolderRepositoryPDO();
            $analyseData = $repoTemp->analyserDocuments($numEtu);
            ?>
            window.analyseDocumentsData = <?= json_encode($analyseData) ?>;
        </script>

    <?php endif; ?>

<?php else : ?>

    <h1><?= $t(['fr' => 'Liste des étudiants', 'en' => 'Students List']) ?></h1>

    <?php if (!empty($message)) : ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="student-toolbar">
        <div class="search-container-toolbar">
            <label for="search" class="search-label"><?= $t(['fr' => 'Rechercher', 'en' => 'Search']) ?></label>
            <input type="text" id="search" name="search" placeholder="Nom, prénom, email..."
                   value="<?= htmlspecialchars(strval($filters['search'] ?? '')) ?>">
            <button type="button" id="btn-search-loupe" class="btn-search">
                <img src="img/loupe.png" alt="Rechercher">
            </button>
        </div>
        <div class="toolbar-actions">
            <button id="btn-import-excel" class="btn-search"
                    onclick="document.getElementById('file-import').click()">
                <?= $t(['fr' => 'Importer Excel/CSV', 'en' => 'Import Excel/CSV']) ?>
            </button>
            <form id="form-import" method="post"
                  action="index.php?page=import_folders&lang=<?= htmlspecialchars($lang) ?>"
                  enctype="multipart/form-data" class="hidden-element">
                <input type="file" id="file-import" name="excel_file" accept=".csv,.xlsx,.xls"
                       onchange="document.getElementById('form-import').submit()">
            </form>
        </div>
    </div>

    <div class="filters-container">
        <p class="filters-title"><?= $t(['fr' => 'Filtres', 'en' => 'Filters']) ?></p>
        <?php
        $resetUrl         = $buildUrl('index.php', ['page' => $PAGE]);
        $showAccordFilter = true;
        $viewPage         = $PAGE;
        include __DIR__ . '/../Partials/_filters.php';
        include __DIR__ . '/../Partials/_table_etudiants.php';
        ?>
    </div>

<?php endif; ?>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="admin"
         style="display:none;">
    </div>
<?php
$content = ob_get_clean();

$title      = $t(['fr' => 'Gestion des dossiers - Admin', 'en' => 'Folders Management - Admin']);
$styles     = ['styles/index.css', 'styles/folders.css', 'styles/chatbot.css'];
$scripts    = ['js/folders.js'];
$activeMenu = $PAGE;
$userRole   = 'admin';

include __DIR__ . '/../Layout/base.php';
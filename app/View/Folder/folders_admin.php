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

<?php if ($action === 'create') : ?>

    <h1><?= $t(['fr' => 'Créer un nouveau dossier étudiant', 'en' => 'Create New Student Profile']) ?></h1>
    <div class="form-back-button">
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => $PAGE]) ?>'" class="btn-secondary">
            <?= $t(['fr' => 'Retour à la liste', 'en' => 'Back to List']) ?>
        </button>
    </div>

    <form method="post" action="index.php?page=save_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
        <div class="form-section">
            <label for="numetu"><?= $t(['fr' => 'NumÉtu *', 'en' => 'Student ID *']) ?></label>
            <input type="text" name="numetu" id="numetu" required>

            <label for="nom"><?= $t(['fr' => 'Nom *', 'en' => 'Last Name *']) ?></label>
            <input type="text" name="nom" id="nom" required>

            <label for="prenom"><?= $t(['fr' => 'Prénom *', 'en' => 'First Name *']) ?></label>
            <input type="text" name="prenom" id="prenom" required>

            <label for="naissance"><?= $t(['fr' => 'Né(e) le', 'en' => 'Date of Birth']) ?></label>
            <input type="date" name="naissance" id="naissance">

            <label for="sexe"><?= $t(['fr' => 'Sexe', 'en' => 'Gender']) ?></label>
            <select name="sexe" id="sexe">
                <option value="M"><?= $t(['fr' => 'Masculin', 'en' => 'Male']) ?></option>
                <option value="F"><?= $t(['fr' => 'Féminin', 'en' => 'Female']) ?></option>
                <option value="Autre"><?= $t(['fr' => 'Autre', 'en' => 'Other']) ?></option>
            </select>

            <label for="adresse"><?= $t(['fr' => 'Adresse', 'en' => 'Address']) ?></label>
            <input type="text" name="adresse" id="adresse">

            <label for="cp"><?= $t(['fr' => 'Code postal', 'en' => 'Postal Code']) ?></label>
            <input type="text" name="cp" id="cp">

            <label for="ville"><?= $t(['fr' => 'Ville', 'en' => 'City']) ?></label>
            <input type="text" name="ville" id="ville">

            <label for="email_perso"><?= $t(['fr' => 'Email Personnel *', 'en' => 'Personal Email *']) ?></label>
            <input type="email" name="email_perso" id="email_perso" required>

            <label for="email_amu"><?= $t(['fr' => 'Email AMU', 'en' => 'AMU Email']) ?></label>
            <input type="email" name="email_amu" id="email_amu">

            <label for="telephone"><?= $t(['fr' => 'Téléphone *', 'en' => 'Phone *']) ?></label>
            <input type="text" name="telephone" id="telephone" required>

            <label for="composante"><?= $t(['fr' => 'Composante', 'en' => 'Component']) ?></label>
            <input type="text" name="composante" id="composante">

            <label for="departement"><?= $t(['fr' => 'Département', 'en' => 'Department']) ?></label>
            <input type="text" name="departement" id="departement">

            <label for="campus"><?= $t(['fr' => 'Campus', 'en' => 'Campus']) ?></label>
            <input type="text" name="campus" id="campus">

            <label for="discipline"><?= $t(['fr' => 'Discipline', 'en' => 'Discipline']) ?></label>
            <input type="text" name="discipline" id="discipline">

            <label for="niveau_etude"><?= $t(['fr' => 'Niveau d\'étude', 'en' => 'Study Level']) ?></label>
            <input type="text" name="niveau_etude" id="niveau_etude">

            <label for="formation"><?= $t(['fr' => 'Formation', 'en' => 'Degree Program']) ?></label>
            <input type="text" name="formation" id="formation">

            <label for="moyenne_bac"><?= $t(['fr' => 'Moyenne Bac', 'en' => 'High School Average']) ?></label>
            <input type="text" name="moyenne_bac" id="moyenne_bac">

            <label for="moyenne_sans_bac"><?= $t(['fr' => 'Moyenne sans Bac', 'en' => 'Average w/o High School']) ?></label>
            <input type="text" name="moyenne_sans_bac" id="moyenne_sans_bac">

            <label for="avis_dri"><?= $t(['fr' => 'Avis DRI', 'en' => 'DRI Advice']) ?></label>
            <input type="text" name="avis_dri" id="avis_dri">

            <label for="date_debut"><?= $t(['fr' => 'Date de début', 'en' => 'Start Date']) ?></label>
            <input type="text" name="date_debut" id="date_debut">

            <label for="mobilite_anterieure"><?= $t(['fr' => 'A déjà effectué une mobilité', 'en' => 'Previous Mobility']) ?></label>
            <input type="text" name="mobilite_anterieure" id="mobilite_anterieure">

            <label for="pays"><?= $t(['fr' => 'Pays', 'en' => 'Country']) ?></label>
            <input type="text" name="pays" id="pays">

            <label for="type"><?= $t(['fr' => 'Type *', 'en' => 'Type *']) ?></label>
            <select name="type" id="type" required>
                <option value=""><?= $t(['fr' => '-- Choisir --', 'en' => '-- Choose --']) ?></option>
                <option value="entrant"><?= $t(['fr' => 'Entrant', 'en' => 'Incoming']) ?></option>
                <option value="sortant"><?= $t(['fr' => 'Sortant', 'en' => 'Outgoing']) ?></option>
            </select>

            <label for="zone"><?= $t(['fr' => 'Zone *', 'en' => 'Zone *']) ?></label>
            <select name="zone" id="zone" required>
                <option value=""><?= $t(['fr' => '-- Choisir --', 'en' => '-- Choose --']) ?></option>
                <option value="europe"><?= $t(['fr' => 'Europe', 'en' => 'Europe']) ?></option>
                <option value="hors_europe"><?= $t(['fr' => 'Hors Europe', 'en' => 'Non-Europe']) ?></option>
            </select>

            <label for="photo"><?= $t(['fr' => 'Photo', 'en' => 'Photo']) ?></label>
            <input type="file" name="photo" id="photo" accept="image/*">

            <label for="cv"><?= $t(['fr' => 'CV', 'en' => 'CV']) ?></label>
            <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx">

            <label for="mobilite_type"><?= $t(['fr' => 'Type de mobilité', 'en' => 'Mobility Type']) ?></label>
            <select name="mobilite_type" id="mobilite_type">
                <option value=""><?= $t(['fr' => '-- Choisir --', 'en' => '-- Choose --']) ?></option>
                <option value="stage"><?= $t(['fr' => 'Stage', 'en' => 'Internship']) ?></option>
                <option value="etudes"><?= $t(['fr' => 'Études', 'en' => 'Studies']) ?></option>
            </select>
        </div>

        <div class="fichier-obligatoire" id="justificatif_convention">
            <label><?= $t(['fr' => 'Convention de stage', 'en' => 'Internship Agreement']) ?></label>
            <input type="file" name="convention" accept=".pdf,.doc,.docx">
        </div>
        <div class="fichier-obligatoire" id="lettre_motivation">
            <label><?= $t(['fr' => 'Lettre de motivation', 'en' => 'Motivation Letter']) ?></label>
            <input type="file" name="lettre_motivation" accept=".pdf,.doc,.docx">
        </div>
        <div class="fichier-obligatoire" id="justificatif_langues">
            <label><?= $t(['fr' => 'Attestation de langues', 'en' => 'Language Certificate']) ?></label>
            <input type="file" name="langues_file" accept=".pdf,.doc,.docx,.jpg,.png">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-secondary"><?= $t(['fr' => 'Enregistrer', 'en' => 'Save']) ?></button>
            <button type="button" class="btn-secondary"
                    onclick="window.location.href='<?= $buildUrl('index.php', ['page' => $PAGE]) ?>'">
                <?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?>
            </button>
        </div>
    </form>

<?php elseif ($action === 'view') : ?>

    <?php if (!$studentData) : ?>
        <p><?= $t(['fr' => 'Étudiant non trouvé', 'en' => 'Student not found']) ?></p>
    <?php else : ?>
        <?php
        $pieces        = (isset($studentData['pieces'])  && is_array($studentData['pieces']))  ? $studentData['pieces']  : [];
        $statuts       = (isset($studentData['statuts']) && is_array($studentData['statuts'])) ? $studentData['statuts'] : [];
        $detectedType  = !empty($pieces['convention']['file']) ? 'stage' : (!empty($pieces['lettre_motivation']['file']) ? 'etudes' : '');
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
        // Colonne dédiée, indépendante du status global du dossier
        $avisChef  = strval($studentData['avis_chef_departement'] ?? '');
        $avisLabel = match($avisChef) {
            'accepte' => ['fr' => 'Dossier accepté par le chef de département',  'en' => 'Folder accepted by department head'],
            'refuse'  => ['fr' => 'Dossier refusé par le chef de département',   'en' => 'Folder refused by department head'],
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
            $repoTemp    = new \Model\Persistence\DossierRepositoryPDO();
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
            <button id="btn-creer-dossier"
                    onclick="window.location.href='<?= $buildUrl('index.php', ['page' => $PAGE, 'action' => 'create']) ?>'">
                <?= $t(['fr' => '+ Créer un dossier', 'en' => '+ Create Folder']) ?>
            </button>
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

<?php
$content = ob_get_clean();

$title      = $t(['fr' => 'Gestion des dossiers - Admin', 'en' => 'Folders Management - Admin']);
$styles     = ['styles/index.css', 'styles/folders.css', 'styles/chatbot.css'];
$scripts    = ['js/folders.js'];
$activeMenu = $PAGE;
$userRole   = 'admin';

include __DIR__ . '/../Layout/base.php';
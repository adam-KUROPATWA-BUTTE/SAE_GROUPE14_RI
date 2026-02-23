<?php
/**
 * Vue : Dossiers Admin
 *
 * Variables attendues (extraites par View::render) :
 * @var string $action
 * @var array<string, mixed> $filters
 * @var int $page
 * @var string $message
 * @var string $lang
 * @var array<string, mixed>|null $studentData
 * @var array<int, array<string, mixed>> $paginatedData
 * @var int $totalCount
 * @var int $totalPages
 * @var Closure(array<string, string>): string $t (fournie globalement ou définie ici)
 */

// Création de la fonction de traduction locale si non injectée
if (!isset($t)) {
    $t = function(array $translations) use ($lang) {
        return $translations[$lang] ?? $translations['fr'] ?? '';
    };
}

// Utilitaires de construction d'URL
$buildUrl = function(string $path, array $params = []) use ($lang): string {
    $params['lang'] = $lang;
    $separator = (strpos($path, '?') === false) ? '?' : '&';
    return $path . $separator . http_build_query($params);
};

$buildPaginationUrl = function(int $p) use ($filters, $lang, $page): string {
    $params = array_merge($filters, [
        'p' => $p,
        'page' => 'folders-admin'
    ]);
    $params = array_filter($params, fn($v) => !empty($v) || $v === 0 || $v === '0');
    $params['lang'] = $lang;
    return 'index.php?' . http_build_query($params);
};

// Vérification de filtres actifs
$hasActiveFilters = (strval($filters['type'] ?? 'all')) !== 'all'
    || (strval($filters['zone'] ?? 'all')) !== 'all'
    || (strval($filters['complet'] ?? 'all')) !== 'all'
    || !empty($filters['date_debut'])
    || !empty($filters['date_fin'])
    || !empty($filters['search']);

ob_start();
?>

    <?php if ($action === 'create') : ?>
        <h1><?= $t(['fr' => 'Créer un nouveau dossier étudiant','en' => 'Create New Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'" class="btn-secondary">
                <?= $t(['fr' => 'Retour à la liste','en' => 'Back to List']) ?>
            </button>
        </div>
        
        <form method="post" action="index.php?page=save_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu"><?= $t(['fr' => 'NumÉtu *','en' => 'Student ID *']) ?></label>
                <input type="text" name="numetu" id="numetu" required>
                
                <label for="nom"><?= $t(['fr' => 'Nom *','en' => 'Last Name *']) ?></label>
                <input type="text" name="nom" id="nom" required>
                
                <label for="prenom"><?= $t(['fr' => 'Prénom *','en' => 'First Name *']) ?></label>
                <input type="text" name="prenom" id="prenom" required>
                
                <label for="naissance"><?= $t(['fr' => 'Né(e) le','en' => 'Date of Birth']) ?></label>
                <input type="date" name="naissance" id="naissance">
                
                <label for="sexe"><?= $t(['fr' => 'Sexe','en' => 'Gender']) ?></label>
                <select name="sexe" id="sexe">
                    <option value="M"><?= $t(['fr' => 'Masculin','en' => 'Male']) ?></option>
                    <option value="F"><?= $t(['fr' => 'Féminin','en' => 'Female']) ?></option>
                    <option value="Autre"><?= $t(['fr' => 'Autre','en' => 'Other']) ?></option>
                </select>
                
                <label for="adresse"><?= $t(['fr' => 'Adresse','en' => 'Address']) ?></label>
                <input type="text" name="adresse" id="adresse">
                
                <label for="cp"><?= $t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
                <input type="text" name="cp" id="cp">
                
                <label for="ville"><?= $t(['fr' => 'Ville','en' => 'City']) ?></label>
                <input type="text" name="ville" id="ville">
                
                <label for="email_perso"><?= $t(['fr' => 'Email Personnel *','en' => 'Personal Email *']) ?></label>
                <input type="email" name="email_perso" id="email_perso" required>
                
                <label for="email_amu"><?= $t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
                <input type="email" name="email_amu" id="email_amu">
                
                <label for="telephone"><?= $t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
                <input type="text" name="telephone" id="telephone" required>
                
                <label for="departement"><?= $t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
                <input type="text" name="departement" id="departement">
                
                <label for="type"><?= $t(['fr' => 'Type *','en' => 'Type *']) ?></label>
                <select name="type" id="type" required>
                    <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="entrant"><?= $t(['fr' => 'Entrant','en' => 'Incoming']) ?></option>
                    <option value="sortant"><?= $t(['fr' => 'Sortant','en' => 'Outgoing']) ?></option>
                </select>
                
                <label for="zone"><?= $t(['fr' => 'Zone *','en' => 'Zone *']) ?></label>
                <select name="zone" id="zone" required>
                    <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="europe"><?= $t(['fr' => 'Europe','en' => 'Europe']) ?></option>
                    <option value="hors_europe"><?= $t(['fr' => 'Hors Europe','en' => 'Non-Europe']) ?></option>
                </select>

                <label for="photo"><?= $t(['fr' => 'Photo','en' => 'Photo']) ?></label>
                <input type="file" name="photo" id="photo" accept="image/*">

                <label for="cv"><?= $t(['fr' => 'CV','en' => 'CV']) ?></label>
                <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx">

                <label for="mobilite_type"><?= $t(['fr' => 'Type de mobilité','en' => 'Mobility Type']) ?></label>
                <select name="mobilite_type" id="mobilite_type">
                    <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="stage"><?= $t(['fr' => 'Stage','en' => 'Internship']) ?></option>
                    <option value="etudes"><?= $t(['fr' => 'Études','en' => 'Studies']) ?></option>
                </select>
            </div>

            <div class="fichier-obligatoire" id="justificatif_convention">
                <label><?= $t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>
                <input type="file" name="convention" accept=".pdf,.doc,.docx">
            </div>
            <div class="fichier-obligatoire" id="lettre_motivation">
                <label><?= $t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>
                <input type="file" name="lettre_motivation" accept=".pdf,.doc,.docx">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-secondary"><?= $t(['fr' => 'Enregistrer','en' => 'Save']) ?></button>
                <button type="button" class="btn-secondary" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                    <?= $t(['fr' => 'Annuler','en' => 'Cancel']) ?>
                </button>
            </div>
        </form>

    <?php elseif ($action === 'view') : ?>
        <?php if (!$studentData): ?>
            <p><?= $t(['fr' => 'Étudiant non trouvé','en' => 'Student not found']) ?></p>
        <?php else: ?>
            <?php
            $pieces = (isset($studentData['pieces']) && is_array($studentData['pieces'])) ? $studentData['pieces'] : [];
            $detectedType = '';
            if (!empty($pieces['convention'])) {
                $detectedType = 'stage';
            } elseif (!empty($pieces['lettre_motivation'])) {
                $detectedType = 'etudes';
            }
            $numEtu = htmlspecialchars(strval($studentData['NumEtu'] ?? ''));
            ?>
            <h1><?= $t(['fr' => 'Dossier étudiant','en' => 'Student Folder']) ?></h1>
            <div class="form-back-button">
                <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'" class="btn-secondary">
                    <?= $t(['fr' => 'Retour à la liste','en' => 'Back to List']) ?>
                </button>
            </div>

            <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
                <div class="form-section">
                    <label for="numetu_display"><?= $t(['fr' => 'NumÉtu *','en' => 'Student ID *']) ?></label>
                    <input type="text" id="numetu_display" value="<?= $numEtu ?>" disabled class="input-disabled">
                    <input type="hidden" name="numetu" id="numetu" value="<?= $numEtu ?>">

                    <label for="nom"><?= $t(['fr' => 'Nom *','en' => 'Last Name *']) ?></label>
                    <input type="text" name="nom" id="nom" value="<?= htmlspecialchars(strval($studentData['Nom'] ?? '')) ?>" disabled class="input-disabled" required>

                    <label for="prenom"><?= $t(['fr' => 'Prénom *','en' => 'First Name *']) ?></label>
                    <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars(strval($studentData['Prenom'] ?? '')) ?>" disabled class="input-disabled" required>

                    <label for="naissance"><?= $t(['fr' => 'Né(e) le','en' => 'Date of Birth']) ?></label>
                    <input type="date" name="naissance" id="naissance" value="<?= htmlspecialchars(strval($studentData['DateNaissance'] ?? '')) ?>" disabled class="input-disabled">

                    <label for="sexe"><?= $t(['fr' => 'Sexe','en' => 'Gender']) ?></label>
                    <select name="sexe" id="sexe" disabled class="input-disabled">
                        <option value="M" <?= ($studentData['Sexe'] ?? '') === 'M' ? 'selected' : '' ?>><?= $t(['fr' => 'Masculin','en' => 'Male']) ?></option>
                        <option value="F" <?= ($studentData['Sexe'] ?? '') === 'F' ? 'selected' : '' ?>><?= $t(['fr' => 'Féminin','en' => 'Female']) ?></option>
                        <option value="Autre" <?= ($studentData['Sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>><?= $t(['fr' => 'Autre','en' => 'Other']) ?></option>
                    </select>

                    <label for="adresse"><?= $t(['fr' => 'Adresse','en' => 'Address']) ?></label>
                    <input type="text" name="adresse" id="adresse" value="<?= htmlspecialchars(strval($studentData['Adresse'] ?? '')) ?>" disabled class="input-disabled">

                    <label for="cp"><?= $t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
                    <input type="text" name="cp" id="cp" value="<?= htmlspecialchars(strval($studentData['CodePostal'] ?? '')) ?>" disabled class="input-disabled">

                    <label for="ville"><?= $t(['fr' => 'Ville','en' => 'City']) ?></label>
                    <input type="text" name="ville" id="ville" value="<?= htmlspecialchars(strval($studentData['Ville'] ?? '')) ?>" disabled class="input-disabled">

                    <label for="email_perso"><?= $t(['fr' => 'Email Personnel *','en' => 'Personal Email *']) ?></label>
                    <input type="email" name="email_perso" id="email_perso" value="<?= htmlspecialchars(strval($studentData['EmailPersonnel'] ?? '')) ?>" disabled class="input-disabled" required>

                    <label for="email_amu"><?= $t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
                    <input type="email" name="email_amu" id="email_amu" value="<?= htmlspecialchars(strval($studentData['EmailAMU'] ?? '')) ?>" disabled class="input-disabled">

                    <label for="telephone"><?= $t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
                    <input type="text" name="telephone" id="telephone" value="<?= htmlspecialchars(strval($studentData['Telephone'] ?? '')) ?>" disabled class="input-disabled" required>

                    <label for="departement"><?= $t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
                    <input type="text" name="departement" id="departement" value="<?= htmlspecialchars(strval($studentData['CodeDepartement'] ?? '')) ?>" disabled class="input-disabled">

                    <label for="type"><?= $t(['fr' => 'Type *','en' => 'Type *']) ?></label>
                    <select name="type" id="type" disabled class="input-disabled" required>
                        <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                        <option value="entrant" <?= ($studentData['Type'] ?? '') === 'entrant' ? 'selected' : '' ?>><?= $t(['fr' => 'Entrant','en' => 'Incoming']) ?></option>
                        <option value="sortant" <?= ($studentData['Type'] ?? '') === 'sortant' ? 'selected' : '' ?>><?= $t(['fr' => 'Sortant','en' => 'Outgoing']) ?></option>
                    </select>

                    <label for="zone"><?= $t(['fr' => 'Zone *','en' => 'Zone *']) ?></label>
                    <select name="zone" id="zone" disabled class="input-disabled" required>
                        <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                        <option value="europe" <?= ($studentData['Zone'] ?? '') === 'europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Europe','en' => 'Europe']) ?></option>
                        <option value="hors_europe" <?= ($studentData['Zone'] ?? '') === 'hors_europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Hors Europe','en' => 'Non-Europe']) ?></option>
                    </select>

                    <label for="mobilite_type"><?= $t(['fr' => 'Type de mobilité','en' => 'Mobility Type']) ?></label>
                    <select name="mobilite_type" id="mobilite_type" disabled class="input-disabled">
                        <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                        <option value="stage" <?= $detectedType === 'stage' ? 'selected' : '' ?>><?= $t(['fr' => 'Stage','en' => 'Internship']) ?></option>
                        <option value="etudes" <?= $detectedType === 'etudes' ? 'selected' : '' ?>><?= $t(['fr' => 'Études','en' => 'Studies']) ?></option>
                    </select>
                </div>

                <div class="form-section documents-section">
                    <h2><?= $t(['fr' => 'Pièces Justificatives','en' => 'Supporting Documents']) ?></h2>
                    
                    <div class="document-block">
                        <label><?= $t(['fr' => 'Photo','en' => 'Photo']) ?></label>
                        <?php if (!empty($pieces['photo'])) : ?>
                            <div class="document-preview">
                                <img src="data:image/jpeg;base64,<?= strval($pieces['photo']) ?>" alt="Photo" class="photo-preview"><br>
                                <a href="data:image/jpeg;base64,<?= strval($pieces['photo']) ?>" download="photo_<?= $numEtu ?>.jpg" class="btn-download">
                                    <?= $t(['fr' => 'Télécharger','en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <p class="no-document"><?= $t(['fr' => 'Aucune photo disponible','en' => 'No photo available']) ?></p>
                        <?php endif; ?>
                        <input type="file" name="photo" id="photo" accept="image/*" disabled class="input-disabled file-input-margin">
                    </div>

                    <div class="document-block">
                        <label><?= $t(['fr' => 'CV','en' => 'CV']) ?></label>
                        <?php if (!empty($pieces['cv'])) : ?>
                            <div class="document-preview">
                                <p class="document-available"><?= $t(['fr' => 'CV disponible','en' => 'CV available']) ?></p>
                                <a href="data:application/pdf;base64,<?= strval($pieces['cv']) ?>" download="cv_<?= $numEtu ?>.pdf" class="btn-download">
                                    <?= $t(['fr' => 'Télécharger le CV','en' => 'Download CV']) ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <p class="no-document"><?= $t(['fr' => 'Aucun CV disponible','en' => 'No CV available']) ?></p>
                        <?php endif; ?>
                        <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx" disabled class="input-disabled file-input-margin">
                    </div>

                    <div id="justificatif_convention" class="document-block">
                        <label><?= $t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>
                        <?php if (!empty($pieces['convention'])) : ?>
                            <div class="document-preview">
                                <p class="document-available"><?= $t(['fr' => 'Convention disponible','en' => 'Agreement available']) ?></p>
                                <a href="data:application/pdf;base64,<?= strval($pieces['convention']) ?>" download="convention_<?= $numEtu ?>.pdf" class="btn-download">
                                    <?= $t(['fr' => 'Télécharger','en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <p class="no-document"><?= $t(['fr' => 'Aucune convention disponible','en' => 'No agreement available']) ?></p>
                        <?php endif; ?>
                        <input type="file" name="convention" id="convention" accept=".pdf,.doc,.docx" disabled class="input-disabled file-input-margin">
                    </div>

                    <div id="lettre_motivation" class="document-block">
                        <label><?= $t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>
                        <?php if (!empty($pieces['lettre_motivation'])) : ?>
                            <div class="document-preview">
                                <p class="document-available"><?= $t(['fr' => 'Lettre disponible','en' => 'Letter available']) ?></p>
                                <a href="data:application/pdf;base64,<?= strval($pieces['lettre_motivation']) ?>" download="lettre_<?= $numEtu ?>.pdf" class="btn-download">
                                    <?= $t(['fr' => 'Télécharger','en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <p class="no-document"><?= $t(['fr' => 'Aucune lettre disponible','en' => 'No letter available']) ?></p>
                        <?php endif; ?>
                        <input type="file" name="lettre_motivation" id="lettre_motivation_file" accept=".pdf,.doc,.docx" disabled class="input-disabled file-input-margin">
                    </div>

                    <?php $isComplete = intval($studentData['IsComplete'] ?? 0); ?>
                    <div class="status-block status-<?= $isComplete ? 'complete' : 'incomplete' ?>">
                        <strong><?= $t(['fr' => 'Statut du dossier :','en' => 'Folder status:']) ?></strong>
                        <span class="status-text-<?= $isComplete ? 'complete' : 'incomplete' ?>">
                            <?= $isComplete ? $t(['fr' => 'Complet','en' => 'Complete']) : $t(['fr' => 'Incomplet','en' => 'Incomplete']) ?>
                        </span>
                        <br><br>
                        <button type="button" onclick="window.location.href='index.php?page=toggle_complete&numetu=<?= urlencode($numEtu) ?>&lang=<?= htmlspecialchars($lang) ?>'" class="btn-secondary btn-toggle-status">
                            <?= $isComplete ? $t(['fr' => 'Marquer comme incomplet','en' => 'Mark as incomplete']) : $t(['fr' => 'Marquer comme complet','en' => 'Mark as complete']) ?>
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="btn-modifier" class="btn-danger"><?= $t(['fr' => 'Modifier','en' => 'Edit']) ?></button>
                    <button type="submit" id="btn-enregistrer" class="btn-secondary btn-hidden"><?= $t(['fr' => 'Enregistrer','en' => 'Save']) ?></button>
                    <button type="button" id="btn-annuler" class="btn-secondary btn-hidden" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                        <?= $t(['fr' => 'Annuler','en' => 'Cancel']) ?>
                    </button>
                    <button type="button" class="btn-secondary" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                        <?= $t(['fr' => 'Retour','en' => 'Back']) ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>

    <?php else : ?>
        <h1><?= $t(['fr' => 'Liste des étudiants','en' => 'Students List']) ?></h1>

        <?php if (!empty($message)) : ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="student-toolbar">
            <div class="search-container-toolbar">
                <label for="search" class="search-label"><?= $t(['fr' => 'Rechercher','en' => 'Search']) ?></label>
                <input type="text" id="search" name="search" placeholder="Nom, prénom, email..." value="<?= htmlspecialchars(strval($filters['search'] ?? '')) ?>">
                <button type="button" id="btn-search-loupe" class="btn-search">
                    <img src="img/loupe.png" alt="Rechercher">
                </button>
            </div>
            <div style="display: flex; gap: 10px;">
                <button id="btn-import-excel" class="btn-search" onclick="document.getElementById('file-import').click()">
                    <?= $t(['fr' => 'Importer Excel/CSV','en' => 'Import Excel/CSV']) ?>
                </button>
                <form id="form-import" method="post" action="index.php?page=import_folders&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" style="display:none;">
                    <input type="file" id="file-import" name="excel_file" accept=".csv, .xlsx, .xls" onchange="document.getElementById('form-import').submit()">
                </form>

                <button id="btn-creer-dossier" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin', 'action' => 'create']) ?>'">
                    <?= $t(['fr' => '+ Créer un dossier','en' => '+ Create Folder']) ?>
                </button>
            </div>
        </div>

        <div class="filters-container">
            <p class="filters-title"><?= $t(['fr' => 'Filtres','en' => 'Filters']) ?></p>

            <div class="filters">
                <div class="filter-group">
                    <label>
                        <input type="checkbox" name="entrant_sortant" value="entrant" <?= (strval($filters['type'] ?? '')) === 'entrant' ? 'checked' : '' ?>>
                        <?= $t(['fr' => 'Entrant','en' => 'Incoming']) ?>
                    </label>
                    <label>
                        <input type="checkbox" name="entrant_sortant" value="sortant" <?= (strval($filters['type'] ?? '')) === 'sortant' ? 'checked' : '' ?>>
                        <?= $t(['fr' => 'Sortant','en' => 'Outgoing']) ?>
                    </label>
                </div>

                <div class="filter-group">
                    <label>
                        <input type="checkbox" name="zone" value="europe" <?= (strval($filters['zone'] ?? '')) === 'europe' ? 'checked' : '' ?>>
                        <?= $t(['fr' => 'Europe','en' => 'Europe']) ?>
                    </label>
                    <label>
                        <input type="checkbox" name="zone" value="hors_europe" <?= (strval($filters['zone'] ?? '')) === 'hors_europe' ? 'checked' : '' ?>>
                        <?= $t(['fr' => 'Hors-Europe','en' => 'Non-Europe']) ?>
                    </label>
                </div>

                <div class="filter-group">
                    <label for="filter-complet"><?= $t(['fr' => 'Statut :','en' => 'Status:']) ?></label>
                    <select id="filter-complet">
                        <option value="all" <?= (strval($filters['complet'] ?? 'all')) === 'all' ? 'selected' : '' ?>><?= $t(['fr' => 'Tous','en' => 'All']) ?></option>
                        <option value="1" <?= (strval($filters['complet'] ?? '')) === '1' ? 'selected' : '' ?>><?= $t(['fr' => 'Complet','en' => 'Complete']) ?></option>
                        <option value="0" <?= (strval($filters['complet'] ?? '')) === '0' ? 'selected' : '' ?>><?= $t(['fr' => 'Incomplet','en' => 'Incomplete']) ?></option>
                    </select>
                </div>

                <div class="filter-group">
                    <?php if ($hasActiveFilters) : ?>
                        <a href="<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>" class="btn-reset">
                            <?= $t(['fr' => 'Réinitialiser les filtres','en' => 'Reset filters']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <p class="results-count"><?= $totalCount ?> <?= $t(['fr' => 'étudiant(s) trouvé(s)','en' => 'student(s) found']) ?></p>

        <table id="table-etudiants">
            <thead>
                <tr>
                    <th><?= $t(['fr' => 'Nom','en' => 'Last Name']) ?></th>
                    <th><?= $t(['fr' => 'Prénom','en' => 'First Name']) ?></th>
                    <th><?= $t(['fr' => 'Né(e) le','en' => 'Birth Date']) ?></th>
                    <th><?= $t(['fr' => 'Type','en' => 'Type']) ?></th>
                    <th><?= $t(['fr' => 'Zone','en' => 'Zone']) ?></th>
                    <th><?= $t(['fr' => 'Mobilité', 'en' => 'Mobility']) ?></th>
                    <th><?= $t(['fr' => 'Statut','en' => 'Status']) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paginatedData as $etudiant) : ?>
                    <?php
                    $rawPieces = strval($etudiant['PiecesJustificatives'] ?? '{}');
                    $decoded = json_decode($rawPieces, true);
                    $pieces = is_array($decoded) ? $decoded : [];

                    $mobilityType = '-';
                    if (!empty($pieces['convention'])) {
                        $mobilityType = $t(['fr' => 'Stage', 'en' => 'Internship']);
                    } elseif (!empty($pieces['lettre_motivation'])) {
                        $mobilityType = $t(['fr' => 'Études', 'en' => 'Studies']);
                    }

                    $numEtu = strval($etudiant['NumEtu'] ?? '');
                    $nom = strval($etudiant['Nom'] ?? '');
                    $prenom = strval($etudiant['Prenom'] ?? '');
                    $type = strval($etudiant['Type'] ?? '');
                    $zone = strval($etudiant['Zone'] ?? '');
                    $isComplete = intval($etudiant['IsComplete'] ?? 0);
                    ?>
                    <tr class="clickable-row" data-numetu="<?= htmlspecialchars($numEtu) ?>">
                        <td><?= htmlspecialchars($nom) ?></td>
                        <td><?= htmlspecialchars($prenom) ?></td>
                        <td><?= htmlspecialchars(strval($etudiant['DateNaissance'] ?? '')) ?></td>
                        <td><?= $t(['fr' => ($type === 'entrant' ? 'Entrant' : 'Sortant'), 'en' => ($type === 'entrant' ? 'Incoming' : 'Outgoing')]) ?></td>
                        <td><?= $t(['fr' => ($zone === 'europe' ? 'Europe' : 'Hors Europe'), 'en' => ($zone === 'europe' ? 'Europe' : 'Non-Europe')]) ?></td>
                        <td><?= htmlspecialchars($mobilityType) ?></td>
                        <td>
                            <?= $isComplete === 1
                                ? '<span class="status-complete">Complet</span>'
                                : '<span class="status-incomplete">Incomplet</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 0) : ?>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <button onclick="window.location.href='<?= $buildPaginationUrl(1) ?>'">«</button>
                    <button onclick="window.location.href='<?= $buildPaginationUrl($page - 1) ?>'">‹</button>
                <?php else : ?>
                    <button disabled>«</button>
                    <button disabled>‹</button>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) : ?>
                    <button class="<?= $i === $page ? 'active' : '' ?>" onclick="window.location.href='<?= $buildPaginationUrl($i) ?>'"><?= $i ?></button>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages) : ?>
                    <button onclick="window.location.href='<?= $buildPaginationUrl($page + 1) ?>'">›</button>
                    <button onclick="window.location.href='<?= $buildPaginationUrl($totalPages) ?>'">»</button>
                <?php else : ?>
                    <button disabled>›</button>
                    <button disabled>»</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>


<?php
$content = ob_get_clean();

// Configuration pour le layout principal (base.php)
$title = $t([
    'fr' => 'Gestion des dossiers - Admin',
    'en' => 'Folders Management - Admin'
]); 

$styles = ['styles/index.css','styles/folders.css', 'styles/chatbot.css'];
$scripts = ['js/chatbot.js', 'js/folders.js'];
$activeMenu = 'folders';
$userRole = 'admin';

// Inclusion du layout commun qui contient <header>, <footer>, chatbot etc.
include __DIR__ . '/../Layout/base.php';
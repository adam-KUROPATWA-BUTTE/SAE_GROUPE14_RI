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

$hasActiveFilters = (strval($filters['type'] ?? 'all')) !== 'all'
    || (strval($filters['zone'] ?? 'all')) !== 'all'
    || (strval($filters['complet'] ?? 'all')) !== 'all'
    || (strval($filters['composante'] ?? 'all')) !== 'all'
    || (strval($filters['accord'] ?? 'all')) !== 'all'
    || !empty($filters['date_debut'])
    || !empty($filters['date_fin'])
    || !empty($filters['search']);

ob_start();
?>

<?php if ($action === 'create') : ?>

    <h1><?= $t(['fr' => 'Créer un nouveau dossier étudiant', 'en' => 'Create New Student Folder']) ?></h1>
    <div class="form-back-button">
        <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'" class="btn-secondary">
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

            <label for="composante"><?= $t(['fr' => 'Composante (AMU CIVIS, IUT, Erasmus...)', 'en' => 'Component']) ?></label>
            <input type="text" name="composante" id="composante">

            <label for="departement"><?= $t(['fr' => 'Département (Info, GEA...)', 'en' => 'Department (CS, Biz...)']) ?></label>
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
            <button type="button" class="btn-secondary" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                <?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?>
            </button>
        </div>
    </form>

<?php elseif ($action === 'view') : ?>

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
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'" class="btn-secondary">
                <?= $t(['fr' => 'Retour à la liste', 'en' => 'Back to List']) ?>
            </button>
        </div>

        <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu_display"><?= $t(['fr' => 'NumÉtu *', 'en' => 'Student ID *']) ?></label>
                <input type="text" id="numetu_display" value="<?= $numEtu ?>" disabled class="input-disabled">
                <input type="hidden" name="numetu" value="<?= $numEtu ?>">

                <label for="nom"><?= $t(['fr' => 'Nom *', 'en' => 'Last Name *']) ?></label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars(strval($studentData['Nom'] ?? '')) ?>" disabled class="input-disabled" required>

                <label for="prenom"><?= $t(['fr' => 'Prénom *', 'en' => 'First Name *']) ?></label>
                <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars(strval($studentData['Prenom'] ?? '')) ?>" disabled class="input-disabled" required>

                <label for="naissance"><?= $t(['fr' => 'Né(e) le', 'en' => 'Date of Birth']) ?></label>
                <input type="date" name="naissance" id="naissance" value="<?= htmlspecialchars(strval($studentData['DateNaissance'] ?? '')) ?>" disabled class="input-disabled">

                <label for="sexe"><?= $t(['fr' => 'Sexe', 'en' => 'Gender']) ?></label>
                <select name="sexe" id="sexe" disabled class="input-disabled">
                    <option value="M" <?= ($studentData['Sexe'] ?? '') === 'M' ? 'selected' : '' ?>><?= $t(['fr' => 'Masculin', 'en' => 'Male']) ?></option>
                    <option value="F" <?= ($studentData['Sexe'] ?? '') === 'F' ? 'selected' : '' ?>><?= $t(['fr' => 'Féminin', 'en' => 'Female']) ?></option>
                    <option value="Autre" <?= ($studentData['Sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>><?= $t(['fr' => 'Autre', 'en' => 'Other']) ?></option>
                </select>

                <label for="adresse"><?= $t(['fr' => 'Adresse', 'en' => 'Address']) ?></label>
                <input type="text" name="adresse" id="adresse" value="<?= htmlspecialchars(strval($studentData['Adresse'] ?? '')) ?>" disabled class="input-disabled">

                <label for="cp"><?= $t(['fr' => 'Code postal', 'en' => 'Postal Code']) ?></label>
                <input type="text" name="cp" id="cp" value="<?= htmlspecialchars(strval($studentData['CodePostal'] ?? '')) ?>" disabled class="input-disabled">

                <label for="ville"><?= $t(['fr' => 'Ville', 'en' => 'City']) ?></label>
                <input type="text" name="ville" id="ville" value="<?= htmlspecialchars(strval($studentData['Ville'] ?? '')) ?>" disabled class="input-disabled">

                <label for="email_perso"><?= $t(['fr' => 'Email Personnel *', 'en' => 'Personal Email *']) ?></label>
                <input type="email" name="email_perso" id="email_perso" value="<?= htmlspecialchars(strval($studentData['EmailPersonnel'] ?? '')) ?>" disabled class="input-disabled" required>

                <label for="email_amu"><?= $t(['fr' => 'Email AMU', 'en' => 'AMU Email']) ?></label>
                <input type="email" name="email_amu" id="email_amu" value="<?= htmlspecialchars(strval($studentData['EmailAMU'] ?? '')) ?>" disabled class="input-disabled">

                <label for="telephone"><?= $t(['fr' => 'Téléphone *', 'en' => 'Phone *']) ?></label>
                <input type="text" name="telephone" id="telephone" value="<?= htmlspecialchars(strval($studentData['Telephone'] ?? '')) ?>" disabled class="input-disabled" required>

                <label for="composante"><?= $t(['fr' => 'Composante (AMU CIVIS, IUT, Erasmus...)', 'en' => 'Component']) ?></label>
                <input type="text" name="composante" id="composante" value="<?= htmlspecialchars(strval($studentData['Composante'] ?? '')) ?>" disabled class="input-disabled">

                <label for="departement"><?= $t(['fr' => 'Département (Info, GEA...)', 'en' => 'Department (CS, Biz...)']) ?></label>
                <input type="text" name="departement" id="departement" value="<?= htmlspecialchars(strval($studentData['CodeDepartement'] ?? '')) ?>" disabled class="input-disabled">

                <label for="campus"><?= $t(['fr' => 'Campus', 'en' => 'Campus']) ?></label>
                <input type="text" name="campus" id="campus" value="<?= htmlspecialchars(strval($studentData['Campus'] ?? '')) ?>" disabled class="input-disabled">

                <label for="discipline"><?= $t(['fr' => 'Discipline', 'en' => 'Discipline']) ?></label>
                <input type="text" name="discipline" id="discipline" value="<?= htmlspecialchars(strval($studentData['Discipline'] ?? '')) ?>" disabled class="input-disabled">

                <label for="niveau_etude"><?= $t(['fr' => 'Niveau d\'étude', 'en' => 'Study Level']) ?></label>
                <input type="text" name="niveau_etude" id="niveau_etude" value="<?= htmlspecialchars(strval($studentData['NiveauEtude'] ?? '')) ?>" disabled class="input-disabled">

                <label for="formation"><?= $t(['fr' => 'Formation', 'en' => 'Degree Program']) ?></label>
                <input type="text" name="formation" id="formation" value="<?= htmlspecialchars(strval($studentData['Formation'] ?? '')) ?>" disabled class="input-disabled">

                <label for="moyenne_bac"><?= $t(['fr' => 'Moyenne Bac', 'en' => 'High School Average']) ?></label>
                <input type="text" name="moyenne_bac" id="moyenne_bac" value="<?= htmlspecialchars(strval($studentData['MoyenneBac'] ?? '')) ?>" disabled class="input-disabled">

                <label for="moyenne_sans_bac"><?= $t(['fr' => 'Moyenne sans Bac', 'en' => 'Average w/o High School']) ?></label>
                <input type="text" name="moyenne_sans_bac" id="moyenne_sans_bac" value="<?= htmlspecialchars(strval($studentData['MoyenneSansBac'] ?? '')) ?>" disabled class="input-disabled">

                <label for="avis_dri"><?= $t(['fr' => 'Avis DRI', 'en' => 'DRI Advice']) ?></label>
                <input type="text" name="avis_dri" id="avis_dri" value="<?= htmlspecialchars(strval($studentData['AvisDRI'] ?? '')) ?>" disabled class="input-disabled">

                <label for="date_debut"><?= $t(['fr' => 'Date de début', 'en' => 'Start Date']) ?></label>
                <input type="text" name="date_debut" id="date_debut" value="<?= htmlspecialchars(strval($studentData['DateDebut'] ?? '')) ?>" disabled class="input-disabled">

                <label for="mobilite_anterieure"><?= $t(['fr' => 'A déjà effectué une mobilité', 'en' => 'Previous Mobility']) ?></label>
                <input type="text" name="mobilite_anterieure" id="mobilite_anterieure" value="<?= htmlspecialchars(strval($studentData['MobiliteAnterieure'] ?? '')) ?>" disabled class="input-disabled">

                <label for="pays"><?= $t(['fr' => 'Pays', 'en' => 'Country']) ?></label>
                <input type="text" name="pays" id="pays" value="<?= htmlspecialchars(strval($studentData['Pays'] ?? '')) ?>" disabled class="input-disabled">

                <label for="type"><?= $t(['fr' => 'Type *', 'en' => 'Type *']) ?></label>
                <select name="type" id="type" disabled class="input-disabled" required>
                    <option value=""><?= $t(['fr' => '-- Choisir --', 'en' => '-- Choose --']) ?></option>
                    <option value="entrant" <?= ($studentData['Type'] ?? '') === 'entrant' ? 'selected' : '' ?>><?= $t(['fr' => 'Entrant', 'en' => 'Incoming']) ?></option>
                    <option value="sortant" <?= ($studentData['Type'] ?? '') === 'sortant' ? 'selected' : '' ?>><?= $t(['fr' => 'Sortant', 'en' => 'Outgoing']) ?></option>
                </select>

                <label for="zone"><?= $t(['fr' => 'Zone *', 'en' => 'Zone *']) ?></label>
                <select name="zone" id="zone" disabled class="input-disabled" required>
                    <option value=""><?= $t(['fr' => '-- Choisir --', 'en' => '-- Choose --']) ?></option>
                    <option value="europe" <?= ($studentData['Zone'] ?? '') === 'europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Europe', 'en' => 'Europe']) ?></option>
                    <option value="hors_europe" <?= ($studentData['Zone'] ?? '') === 'hors_europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Hors Europe', 'en' => 'Non-Europe']) ?></option>
                </select>

                <label for="mobilite_type"><?= $t(['fr' => 'Type de mobilité', 'en' => 'Mobility Type']) ?></label>
                <select name="mobilite_type" id="mobilite_type" disabled class="input-disabled">
                    <option value=""><?= $t(['fr' => '-- Choisir --', 'en' => '-- Choose --']) ?></option>
                    <option value="stage" <?= $detectedType === 'stage' ? 'selected' : '' ?>><?= $t(['fr' => 'Stage', 'en' => 'Internship']) ?></option>
                    <option value="etudes" <?= $detectedType === 'etudes' ? 'selected' : '' ?>><?= $t(['fr' => 'Études', 'en' => 'Studies']) ?></option>
                </select>
            </div>

            <div class="form-section documents-section" style="grid-column: 1 / -1;">
                <h2><?= $t(['fr' => 'Revue des Pièces Justificatives', 'en' => 'Documents Review']) ?></h2>

                <div class="doc-review-list">
                    <?php
                    $docTypes = [
                        'photo'            => $t(['fr' => 'Photo', 'en' => 'Photo']),
                        'cv'               => $t(['fr' => 'CV', 'en' => 'CV']),
                        'convention'       => $t(['fr' => 'Convention de stage', 'en' => 'Internship Agreement']),
                        'lettre_motivation' => $t(['fr' => 'Lettre de motivation', 'en' => 'Motivation Letter']),
                        'langues'          => $t(['fr' => 'Attestation de langues', 'en' => 'Language Certificate']),
                    ];

                    foreach ($docTypes as $key => $label) :
                        $doc    = $pieces[$key] ?? null;
                        $hasDoc = !empty($doc['file']);
                        $status  = $doc['status'] ?? 'pending';
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

                                <br><br>
                                <label style="font-size:0.85em;text-align:left;color:#666;">
                                    <?= $t(['fr' => 'Mettre à jour le fichier (Optionnel) :', 'en' => 'Update file (Optional):']) ?>
                                </label>
                                <input type="file"
                                       name="<?= $key === 'langues' ? 'langues_file' : $key ?>"
                                       accept="<?= $key === 'photo' ? 'image/*' : '.pdf,.doc,.docx' ?>"
                                       disabled class="input-disabled file-input-margin" style="margin-top:5px;">
                            </div>

                            <div class="doc-actions <?= !$hasDoc ? 'disabled-area' : '' ?>">
                                <div class="status-radios">
                                    <label class="radio-accept">
                                        <input type="radio" name="status_<?= $key ?>" value="accepted"
                                            <?= ($status === 'accepted' || $status === 'pending') ? 'checked' : '' ?>
                                            <?= !$hasDoc ? 'disabled' : '' ?>>
                                        <?= $t(['fr' => 'Accepter', 'en' => 'Accept']) ?>
                                    </label>
                                    <label class="radio-refuse">
                                        <input type="radio" name="status_<?= $key ?>" value="refused"
                                            <?= $status === 'refused' ? 'checked' : '' ?>
                                            <?= !$hasDoc ? 'disabled' : '' ?>>
                                        <?= $t(['fr' => 'Refuser', 'en' => 'Refuse']) ?>
                                    </label>
                                </div>
                                <textarea name="comment_<?= $key ?>"
                                          placeholder="<?= $t(['fr' => 'Ajouter un commentaire pour l\'étudiant...', 'en' => 'Add a comment for the student...']) ?>"
                                          <?= !$hasDoc ? 'disabled' : '' ?>><?= htmlspecialchars($comment) ?></textarea>

                                <div style="display:flex;align-items:center;margin-top:5px;">
                                    <button type="button" class="btn-confirm-doc"
                                            onclick="window.folderManager.confirmDocument('<?= $numEtu ?>', '<?= $key ?>')"
                                        <?= !$hasDoc ? 'disabled' : '' ?>>
                                        <?= $t(['fr' => 'Confirmer la pièce', 'en' => 'Confirm Document']) ?>
                                    </button>
                                    <span class="doc-save-indicator" id="indicator_<?= $key ?>"></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php $currentStatus = $studentData['status'] ?? 'depot'; ?>
                <div class="global-status-block" style="margin-top:30px;margin-bottom:20px;padding:20px;background:#f8f9fa;border-radius:8px;border-left:5px solid var(--primary-color);">
                    <strong style="font-size:1.1em;"><?= $t(['fr' => 'Statut GLOBAL du dossier :', 'en' => 'GLOBAL Folder status:']) ?></strong>
                    <div style="display:flex;gap:15px;align-items:center;margin-top:15px;">
                        <select id="global_status_select" class="form-control" style="width:auto;padding:8px;border-radius:5px;border:1px solid #ccc;">
                            <option value="depot"       <?= $currentStatus === 'depot'       ? 'selected' : '' ?>><?= $t(['fr' => 'Dépôt',         'en' => 'Submitted'])    ?></option>
                            <option value="instruction" <?= $currentStatus === 'instruction' ? 'selected' : '' ?>><?= $t(['fr' => 'En instruction', 'en' => 'Under Review']) ?></option>
                            <option value="accepte"     <?= $currentStatus === 'accepte'     ? 'selected' : '' ?>><?= $t(['fr' => 'Accepté',        'en' => 'Accepted'])     ?></option>
                            <option value="refuse"      <?= $currentStatus === 'refuse'      ? 'selected' : '' ?>><?= $t(['fr' => 'Refusé',         'en' => 'Refused'])      ?></option>
                        </select>
                        <button type="button" class="btn-primary" onclick="window.folderManager.updateGlobalStatus('<?= $numEtu ?>')">
                            <?= $t(['fr' => 'Mettre à jour le statut', 'en' => 'Update Status']) ?>
                        </button>
                        <span id="global_status_indicator" style="font-weight:bold;margin-left:10px;"></span>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="btn-modifier" class="btn-danger"><?= $t(['fr' => 'Modifier', 'en' => 'Edit']) ?></button>
                <button type="submit" id="btn-enregistrer" class="btn-secondary btn-hidden"><?= $t(['fr' => 'Enregistrer', 'en' => 'Save']) ?></button>
                <button type="button" id="btn-annuler" class="btn-secondary btn-hidden"
                        onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                    <?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?>
                </button>
                <button type="button" class="btn-secondary"
                        onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                    <?= $t(['fr' => 'Retour', 'en' => 'Back']) ?>
                </button>
            </div>
        </form>

    <?php endif; ?>

<?php else : ?>

    <h1><?= $t(['fr' => 'Liste des étudiants', 'en' => 'Students List']) ?></h1>

    <?php if (!empty($message)) : ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="student-toolbar">
        <div class="search-container-toolbar">
            <label for="search" class="search-label"><?= $t(['fr' => 'Rechercher', 'en' => 'Search']) ?></label>
            <input type="text" id="search" name="search" placeholder="Nom, prénom, email..." value="<?= htmlspecialchars(strval($filters['search'] ?? '')) ?>">
            <button type="button" id="btn-search-loupe" class="btn-search">
                <img src="img/loupe.png" alt="Rechercher">
            </button>
        </div>
        <div style="display:flex;gap:10px;">
            <button id="btn-import-excel" class="btn-search" onclick="document.getElementById('file-import').click()">
                <?= $t(['fr' => 'Importer Excel/CSV', 'en' => 'Import Excel/CSV']) ?>
            </button>
            <form id="form-import" method="post" action="index.php?page=import_folders&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" style="display:none;">
                <input type="file" id="file-import" name="excel_file" accept=".csv,.xlsx,.xls" onchange="document.getElementById('form-import').submit()">
            </form>
            <button id="btn-creer-dossier" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'folders-admin', 'action' => 'create']) ?>'">
                <?= $t(['fr' => '+ Créer un dossier', 'en' => '+ Create Folder']) ?>
            </button>
        </div>
    </div>

    <div class="filters-container">
        <p class="filters-title"><?= $t(['fr' => 'Filtres', 'en' => 'Filters']) ?></p>

        <div class="filters">
            <div class="filter-group">
                <label>
                    <input type="checkbox" name="entrant_sortant" value="entrant" <?= (strval($filters['type'] ?? '')) === 'entrant' ? 'checked' : '' ?>>
                    <?= $t(['fr' => 'Entrant', 'en' => 'Incoming']) ?>
                </label>
                <label>
                    <input type="checkbox" name="entrant_sortant" value="sortant" <?= (strval($filters['type'] ?? '')) === 'sortant' ? 'checked' : '' ?>>
                    <?= $t(['fr' => 'Sortant', 'en' => 'Outgoing']) ?>
                </label>
            </div>

            <div class="filter-group">
                <label>
                    <input type="checkbox" name="zone" value="europe" <?= (strval($filters['zone'] ?? '')) === 'europe' ? 'checked' : '' ?>>
                    <?= $t(['fr' => 'Europe', 'en' => 'Europe']) ?>
                </label>
                <label>
                    <input type="checkbox" name="zone" value="hors_europe" <?= (strval($filters['zone'] ?? '')) === 'hors_europe' ? 'checked' : '' ?>>
                    <?= $t(['fr' => 'Hors-Europe', 'en' => 'Non-Europe']) ?>
                </label>
            </div>

            <div class="filter-group">
                <label for="filter-complet"><?= $t(['fr' => 'Statut :', 'en' => 'Status:']) ?></label>
                <select id="filter-complet">
                    <option value="all" <?= (strval($filters['complet'] ?? 'all')) === 'all' ? 'selected' : '' ?>><?= $t(['fr' => 'Tous',      'en' => 'All'])      ?></option>
                    <option value="1"   <?= (strval($filters['complet'] ?? ''))    === '1'   ? 'selected' : '' ?>><?= $t(['fr' => 'Complet',   'en' => 'Complete']) ?></option>
                    <option value="0"   <?= (strval($filters['complet'] ?? ''))    === '0'   ? 'selected' : '' ?>><?= $t(['fr' => 'Incomplet', 'en' => 'Incomplete']) ?></option>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-composante"><?= $t(['fr' => 'Composante :', 'en' => 'Component:']) ?></label>
                <select id="filter-composante" name="composante">
                    <option value="all"      <?= (strval($filters['composante'] ?? 'all'))  === 'all'       ? 'selected' : '' ?>><?= $t(['fr' => 'Toutes', 'en' => 'All']) ?></option>
                    <option value="AMU CIVIS" <?= (strval($filters['composante'] ?? ''))    === 'AMU CIVIS'  ? 'selected' : '' ?>>AMU CIVIS</option>
                    <option value="IUT"       <?= (strval($filters['composante'] ?? ''))    === 'IUT'        ? 'selected' : '' ?>>IUT</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-accord"><?= $t(['fr' => 'Accord :', 'en' => 'Agreement:']) ?></label>
                <select id="filter-accord" name="accord">
                    <option value="all"       <?= (strval($filters['accord'] ?? 'all')) === 'all'        ? 'selected' : '' ?>><?= $t(['fr' => 'Tous', 'en' => 'All']) ?></option>
                    <option value="Erasmus"   <?= (strval($filters['accord'] ?? ''))   === 'Erasmus'     ? 'selected' : '' ?>>Erasmus</option>
                    <option value="Bilatéral" <?= (strval($filters['accord'] ?? ''))   === 'Bilatéral'   ? 'selected' : '' ?>>Bilatéral</option>
                </select>
            </div>

            <div class="filter-group">
                <?php if ($hasActiveFilters) : ?>
                    <a href="<?= $buildUrl('index.php', ['page' => 'folders-admin']) ?>" class="btn-reset">
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
                                <th><?= $t(['fr' => 'Nom',                    'en' => 'Last Name'])           ?></th>
                                <th><?= $t(['fr' => 'Prénom',                 'en' => 'First Name'])          ?></th>
                                <th><?= $t(['fr' => 'Type',                   'en' => 'Type'])                ?></th>
                                <th><?= $t(['fr' => 'Composante / Accord',    'en' => 'Component / Agreement']) ?></th>
                                <th><?= $t(['fr' => 'Département',            'en' => 'Department'])          ?></th>
                                <th><?= $t(['fr' => 'Mobilité',               'en' => 'Mobility'])            ?></th>
                                <th><?= $t(['fr' => 'Statut',                 'en' => 'Status'])              ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $etudiant) : ?>
                                <?php
                                $rawPieces   = strval($etudiant['PiecesJustificatives'] ?? '{}');
                                $decoded     = json_decode($rawPieces, true);
                                $ePieces     = is_array($decoded) ? $decoded : [];

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
                                <tr class="clickable-row" data-numetu="<?= htmlspecialchars($eNumEtu) ?>">
                                    <td><?= htmlspecialchars($eNom) ?></td>
                                    <td><?= htmlspecialchars($ePrenom) ?></td>
                                    <td><?= $t(['fr' => ($eType === 'entrant' ? 'Entrant' : 'Sortant'), 'en' => ($eType === 'entrant' ? 'Incoming' : 'Outgoing')]) ?></td>
                                    <td><?= htmlspecialchars($eComposante  ?: '-') ?></td>
                                    <td><?= htmlspecialchars($eDepartement ?: '-') ?></td>
                                    <td><?= htmlspecialchars($mobilityType) ?></td>
                                    <td>
                                        <?php if ($eStatus === 'accepte') : ?>
                                            <span class="status-complete"    style="color:green;">Accepté</span>
                                        <?php elseif ($eStatus === 'refuse') : ?>
                                            <span class="status-incomplete"  style="color:red;">Refusé</span>
                                        <?php elseif ($eStatus === 'instruction') : ?>
                                            <span class="status-incomplete"  style="color:orange;">En instruction</span>
                                        <?php else : ?>
                                            <span class="status-incomplete"  style="color:grey;">Dépôt</span>
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
    'fr' => 'Gestion des dossiers - Admin',
    'en' => 'Folders Management - Admin',
]);

$styles     = ['styles/index.css', 'styles/folders.css', 'styles/chatbot.css'];
$scripts    = ['js/folders.js'];
$activeMenu = 'folders';
$userRole   = 'admin';

include __DIR__ . '/../Layout/base.php';
<?php
/**
 * Vue : Dossiers Étudiant
 *
 * Variables attendues (extraites par View::render) :
 * @var array<string, mixed> $dossier
 * @var string $studentId
 * @var string $message
 * @var string $lang
 * @var Closure(array<string, string>): string $t
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

// Initialisation des données
$isCreateMode = empty($dossier);
$formAction = $isCreateMode ? 'create_folder' : 'update_my_folder';
$rawPieces = $dossier['pieces'] ?? [];
$pieces = is_array($rawPieces) ? $rawPieces : [];

$detectedType = '';
if (!$isCreateMode) {
    if (!empty($pieces['convention']['file'])) {
        $detectedType = 'stage';
    } elseif (!empty($pieces['lettre_motivation']['file'])) {
        $detectedType = 'etudes';
    }
}

$valNom = htmlspecialchars(strval($dossier['Nom'] ?? ''));
$valPrenom = htmlspecialchars(strval($dossier['Prenom'] ?? ''));
$valDate = htmlspecialchars(strval($dossier['DateNaissance'] ?? ''));
$valSexe = strval($dossier['Sexe'] ?? '');
$valEmailP = htmlspecialchars(strval($dossier['EmailPersonnel'] ?? ''));
$valEmailA = htmlspecialchars(strval($dossier['EmailAMU'] ?? ''));
$valTel = htmlspecialchars(strval($dossier['Telephone'] ?? ''));
$valAdresse = htmlspecialchars(strval($dossier['Adresse'] ?? ''));
$valCP = htmlspecialchars(strval($dossier['CodePostal'] ?? ''));
$valVille = htmlspecialchars(strval($dossier['Ville'] ?? ''));
$valDept = htmlspecialchars(strval($dossier['CodeDepartement'] ?? ''));
$valComposante = htmlspecialchars(strval($dossier['Composante'] ?? ''));
$valCampus = htmlspecialchars(strval($dossier['Campus'] ?? ''));
$valDiscipline = htmlspecialchars(strval($dossier['Discipline'] ?? ''));
$valNiveauEtude = htmlspecialchars(strval($dossier['NiveauEtude'] ?? ''));
$valFormation = htmlspecialchars(strval($dossier['Formation'] ?? ''));
$valMoyenneBac = htmlspecialchars(strval($dossier['MoyenneBac'] ?? ''));
$valMoyenneSansBac = htmlspecialchars(strval($dossier['MoyenneSansBac'] ?? ''));
$valAvisDRI = htmlspecialchars(strval($dossier['AvisDRI'] ?? ''));
$valDateDebut = htmlspecialchars(strval($dossier['DateDebut'] ?? ''));
$valMobiliteAnterieure = htmlspecialchars(strval($dossier['MobiliteAnterieure'] ?? ''));
$valPays = htmlspecialchars(strval($dossier['Pays'] ?? ''));
$valType = strval($dossier['Type'] ?? '');
$valZone = strval($dossier['Zone'] ?? '');

ob_start();
?>

    <h1><?= $t(['fr' => 'Mon dossier étudiant','en' => 'My Student Folder']) ?></h1>

<?php if (!empty($message)) : ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

    <form method="post"
          action="<?= $buildUrl('index.php', ['page' => $formAction]) ?>"
          enctype="multipart/form-data"
          class="creation-form">

        <div class="form-section">
            <label><?= $t(['fr' => 'Numéro étudiant','en' => 'Student ID']) ?></label>
            <input type="text" value="<?= htmlspecialchars($studentId) ?>" readonly style="background:#eee;">
            <input type="hidden" name="numetu" value="<?= htmlspecialchars($studentId) ?>">

            <label><?= $t(['fr' => 'Nom *','en' => 'Last Name *']) ?></label>
            <input type="text" name="nom" value="<?= $valNom ?>"
                <?= $isCreateMode ? 'required' : 'readonly style="background:#f7f7f7;"' ?>>

            <label><?= $t(['fr' => 'Prénom *','en' => 'First Name *']) ?></label>
            <input type="text" name="prenom" value="<?= $valPrenom ?>"
                <?= $isCreateMode ? 'required' : 'readonly style="background:#f7f7f7;"' ?>>

            <label><?= $t(['fr' => 'Date de naissance','en' => 'Date of Birth']) ?></label>
            <input type="date" name="naissance" value="<?= $valDate ?>"
                <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

            <label><?= $t(['fr' => 'Sexe','en' => 'Gender']) ?></label>
            <select name="sexe" id="sexe" <?= $isCreateMode ? '' : 'disabled style="background:#f7f7f7;"' ?>>
                <option value="M" <?= $valSexe === 'M' ? 'selected' : '' ?>><?= $t(['fr' => 'Masculin','en' => 'Male']) ?></option>
                <option value="F" <?= $valSexe === 'F' ? 'selected' : '' ?>><?= $t(['fr' => 'Féminin','en' => 'Female']) ?></option>
                <option value="Autre" <?= $valSexe === 'Autre' ? 'selected' : '' ?>><?= $t(['fr' => 'Autre','en' => 'Other']) ?></option>
            </select>

            <label><?= $t(['fr' => 'Adresse','en' => 'Address']) ?></label>
            <input type="text" name="adresse" value="<?= $valAdresse ?>">

            <label><?= $t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
            <input type="text" name="cp" value="<?= $valCP ?>">

            <label><?= $t(['fr' => 'Ville','en' => 'City']) ?></label>
            <input type="text" name="ville" value="<?= $valVille ?>">

            <label><?= $t(['fr' => 'Email personnel *','en' => 'Personal Email *']) ?></label>
            <input type="email" name="email_perso" value="<?= $valEmailP ?>" required>

            <label><?= $t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
            <input type="email" name="email_amu" value="<?= $valEmailA ?>"
                <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

            <label><?= $t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
            <input type="text" name="telephone" value="<?= $valTel ?>" required>

            <label><?= $t(['fr' => 'Composante','en' => 'Component']) ?></label>
            <input type="text" name="composante" value="<?= $valComposante ?>"
                <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

            <label><?= $t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
            <input type="text" name="departement" value="<?= $valDept ?>"
                <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

            <label><?= $t(['fr' => 'Discipline','en' => 'Discipline']) ?></label>
            <input type="text" name="discipline" value="<?= $valDiscipline ?>">

            <label><?= $t(['fr' => 'Formation','en' => 'Degree Program']) ?></label>
            <input type="text" name="formation" value="<?= $valFormation ?>">

            <label><?= $t(['fr' => 'Campus','en' => 'Campus']) ?></label>
            <input type="text" name="campus" value="<?= $valCampus ?>" disabled style="background:#eee; cursor:not-allowed;" title="<?= $t(['fr' => 'Réservé à l\'administration', 'en' => 'Administration only']) ?>">

            <label><?= $t(['fr' => 'Niveau d\'étude','en' => 'Study Level']) ?></label>
            <input type="text" name="niveau_etude" value="<?= $valNiveauEtude ?>" disabled style="background:#eee; cursor:not-allowed;" title="<?= $t(['fr' => 'Réservé à l\'administration', 'en' => 'Administration only']) ?>">

            <label><?= $t(['fr' => 'Moyenne Bac','en' => 'High School Average']) ?></label>
            <input type="text" name="moyenne_bac" value="<?= $valMoyenneBac ?>" disabled style="background:#eee; cursor:not-allowed;" title="<?= $t(['fr' => 'Réservé à l\'administration', 'en' => 'Administration only']) ?>">

            <label><?= $t(['fr' => 'Moyenne sans Bac','en' => 'Average w/o High School']) ?></label>
            <input type="text" name="moyenne_sans_bac" value="<?= $valMoyenneSansBac ?>" disabled style="background:#eee; cursor:not-allowed;" title="<?= $t(['fr' => 'Réservé à l\'administration', 'en' => 'Administration only']) ?>">

            <label><?= $t(['fr' => 'Avis DRI','en' => 'DRI Advice']) ?></label>
            <input type="text" name="avis_dri" value="<?= $valAvisDRI ?>" disabled style="background:#eee; cursor:not-allowed;" title="<?= $t(['fr' => 'Réservé à l\'administration', 'en' => 'Administration only']) ?>">

            <label><?= $t(['fr' => 'Date de début','en' => 'Start Date']) ?></label>
            <input type="text" name="date_debut" value="<?= $valDateDebut ?>" disabled style="background:#eee; cursor:not-allowed;" title="<?= $t(['fr' => 'Réservé à l\'administration', 'en' => 'Administration only']) ?>">

            <label><?= $t(['fr' => 'A déjà effectué une mobilité','en' => 'Previous Mobility']) ?></label>
            <input type="text" name="mobilite_anterieure" value="<?= $valMobiliteAnterieure ?>" disabled style="background:#eee; cursor:not-allowed;" title="<?= $t(['fr' => 'Réservé à l\'administration', 'en' => 'Administration only']) ?>">
            <label><?= $t(['fr' => 'Pays','en' => 'Country']) ?></label>
            <input type="text" name="pays" value="<?= $valPays ?>">

            <label for="type"><?= $t(['fr' => 'Type *','en' => 'Type *']) ?></label>
            <select name="type" id="type" <?= $isCreateMode ? 'required' : 'disabled style="background:#f7f7f7;"' ?>>
                <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                <option value="entrant" <?= $valType === 'entrant' ? 'selected' : '' ?>><?= $t(['fr' => 'Entrant','en' => 'Incoming']) ?></option>
                <option value="sortant" <?= $valType === 'sortant' ? 'selected' : '' ?>><?= $t(['fr' => 'Sortant','en' => 'Outgoing']) ?></option>
            </select>

            <label for="zone"><?= $t(['fr' => 'Zone *','en' => 'Zone *']) ?></label>
            <select name="zone" id="zone" <?= $isCreateMode ? 'required' : 'disabled style="background:#f7f7f7;"' ?>>
                <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                <option value="europe" <?= $valZone === 'europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Europe','en' => 'Europe']) ?></option>
                <option value="hors_europe" <?= $valZone === 'hors_europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Hors Europe','en' => 'Non-Europe']) ?></option>
            </select>

            <label for="mobilite_type"><?= $t(['fr' => 'Type de mobilité','en' => 'Mobility Type']) ?></label>
            <select name="mobilite_type" id="mobilite_type"
                <?= $isCreateMode ? '' : 'disabled style="background:#f7f7f7;"' ?>>
                <option value=""><?= $t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                <option value="stage" <?= $detectedType === 'stage' ? 'selected' : '' ?>><?= $t(['fr' => 'Stage','en' => 'Internship']) ?></option>
                <option value="etudes" <?= $detectedType === 'etudes' ? 'selected' : '' ?>><?= $t(['fr' => 'Études','en' => 'Studies']) ?></option>
            </select>
        </div>

        <div class="form-section" style="margin-top: 40px;">
            <h2 style="grid-column: 1 / -1; margin-bottom: 20px;"><?= $t(['fr' => 'Mes pièces justificatives','en' => 'My Documents']) ?></h2>

            <?php
            $docTypes = [
                'photo' => ['label' => $t(['fr' => 'Photo', 'en' => 'Photo']), 'id' => 'doc_photo', 'name' => 'photo', 'accept' => 'image/*'],
                'cv' => ['label' => $t(['fr' => 'CV', 'en' => 'CV']), 'id' => 'doc_cv', 'name' => 'cv', 'accept' => '.pdf,.doc,.docx'],
                'convention' => ['label' => $t(['fr' => 'Convention de stage', 'en' => 'Internship Agreement']), 'id' => 'justificatif_convention', 'name' => 'convention', 'accept' => '.pdf,.doc,.docx'],
                'lettre_motivation' => ['label' => $t(['fr' => 'Lettre de motivation', 'en' => 'Motivation Letter']), 'id' => 'lettre_motivation', 'name' => 'lettre_motivation', 'accept' => '.pdf,.doc,.docx'],
                'langues' => ['label' => $t(['fr' => 'Attestation de langues', 'en' => 'Language Certificate']), 'id' => 'doc_langues', 'name' => 'langues_file', 'accept' => '.pdf,.doc,.docx,.jpg,.png']
            ];

            $statusText = [
                'pending' => $t(['fr' => 'En attente', 'en' => 'Pending']),
                'accepted' => $t(['fr' => 'Accepté', 'en' => 'Accepted']),
                'refused' => $t(['fr' => 'Refusé', 'en' => 'Refused'])
            ];
            $statusColor = [
                'pending' => '#ffc107',
                'accepted' => '#28a745',
                'refused' => '#dc3545'
            ];

            foreach ($docTypes as $key => $info) :
                $doc = $pieces[$key] ?? null;
                $hasFile = !empty($doc['file']);
                $status = $doc['status'] ?? 'pending';
                $comment = $doc['comment'] ?? '';

                $extraStyle = ($key === 'convention' || $key === 'lettre_motivation') ? 'display: none;' : '';
                ?>

                <div id="<?= $info['id'] ?>" style="<?= $extraStyle ?> grid-column: 1 / -1; margin-bottom: 20px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fdfdfd; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <label style="margin: 0; font-size: 1.1em; color: var(--primary-color); font-weight: bold;"><?= $info['label'] ?></label>
                        <?php if ($hasFile): ?>
                            <span style="background: <?= $statusColor[$status] ?>; color: <?= $status === 'pending' ? '#333' : 'white' ?>; padding: 4px 12px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                            <?= $statusText[$status] ?>
                        </span>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic; font-size: 0.9em;"><?= $t(['fr' => 'Non fourni', 'en' => 'Not provided']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($hasFile) : ?>
                        <div style="margin-bottom: 15px;">
                            <a href="data:application/octet-stream;base64,<?= strval($doc['file']) ?>"
                               download="<?= $key ?>_<?= htmlspecialchars($studentId) ?>"
                               class="btn-secondary" style="font-size: 0.9em; padding: 6px 12px; text-decoration: none; display: inline-block;">
                                📥 <?= $t(['fr' => 'Télécharger mon fichier actuel', 'en' => 'Download my current file']) ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($comment)): ?>
                        <div style="background: #fff3cd; color: #856404; padding: 12px; border-left: 4px solid #ffeeba; margin-bottom: 15px; border-radius: 4px; font-size: 0.95em;">
                            <strong><?= $t(['fr' => 'Commentaire de l\'administration :', 'en' => 'Administration comment:']) ?></strong><br>
                            <span style="display: inline-block; margin-top: 5px;"><?= nl2br(htmlspecialchars($comment)) ?></span>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 10px; border-top: 1px dashed #ddd; padding-top: 15px;">
                        <label style="font-size: 0.9em; color:#555; font-weight: bold; display: block; margin-bottom: 5px;">
                            <?= $hasFile ? $t(['fr' => 'Remplacer ce fichier :', 'en' => 'Replace this file:']) : $t(['fr' => 'Ajouter un fichier :', 'en' => 'Add a file:']) ?>
                        </label>
                        <input type="file" name="<?= $info['name'] ?>" accept="<?= $info['accept'] ?>" style="width: 100%;">
                    </div>
                </div>

            <?php endforeach; ?>
        </div>

        <div class="form-actions" style="margin-top: 30px; grid-column: 1 / -1; display: flex; justify-content: center; gap: 20px;">
            <?php if ($isCreateMode) : ?>
                <button type="submit" class="btn-primary" style="padding: 10px 30px; font-size: 1.1em;">
                    <?= $t(['fr' => 'Déposer ma demande','en' => 'Submit my application']) ?>
                </button>
            <?php else : ?>
                <button type="submit" class="btn-primary" style="padding: 10px 30px; font-size: 1.1em;">
                    <?= $t(['fr' => 'Enregistrer mes modifications','en' => 'Save changes']) ?>
                </button>
            <?php endif; ?>

            <button type="button" class="btn-secondary" style="padding: 10px 20px;" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-student']) ?>'">
                <?= $t(['fr' => 'Annuler','en' => 'Cancel']) ?>
            </button>
        </div>
    </form>

<?php
$content = ob_get_clean();

// Variables passées au layout global "base.php"
$title = $t([
    'fr' => 'Mon dossier - Étudiant',
    'en' => 'My Folder - Student'
]);

$styles = ['styles/folders.css', 'styles/chatbot.css'];
$scripts = ['js/chatbot.js', 'js/folders.js'];
$activeMenu = 'folders';
$userRole = 'student';

// Inclusion du layout
include __DIR__ . '/../Layout/base.php';
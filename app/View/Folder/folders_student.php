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
    if (!empty($pieces['convention'])) {
        $detectedType = 'stage';
    } elseif (!empty($pieces['lettre_motivation'])) {
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

            <label><?= $t(['fr' => 'Email personnel *','en' => 'Personal Email *']) ?></label>
            <input type="email" name="email_perso" value="<?= $valEmailP ?>" required>

            <label><?= $t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
            <input type="email" name="email_amu" value="<?= $valEmailA ?>"
                   <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

            <label><?= $t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
            <input type="text" name="telephone" value="<?= $valTel ?>" required>

            <label><?= $t(['fr' => 'Adresse','en' => 'Address']) ?></label>
            <input type="text" name="adresse" value="<?= $valAdresse ?>">

            <label><?= $t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
            <input type="text" name="cp" value="<?= $valCP ?>">

            <label><?= $t(['fr' => 'Ville','en' => 'City']) ?></label>
            <input type="text" name="ville" value="<?= $valVille ?>">
            
            <label><?= $t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
            <input type="text" name="departement" value="<?= $valDept ?>"
                   <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

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

        <div class="form-section" style="margin-top: 30px;">
            <h2><?= $t(['fr' => 'Mes documents','en' => 'My Documents']) ?></h2>

            <?php
            $docs = ['photo' => 'Photo', 'cv' => 'CV'];
            foreach ($docs as $key => $label) :
                $hasFile = !empty($pieces[$key]);
                ?>
            <div style="margin-bottom: 20px;">
                <label><?= $t(['fr' => $label,'en' => $label]) ?></label>
                <?php if ($hasFile) : ?>
                    <div style="margin-top: 10px;">
                        <a href="data:application/octet-stream;base64,<?= strval($pieces[$key]) ?>"
                           download="<?= $key ?>_<?= htmlspecialchars($studentId) ?>.<?= $key === 'photo' ? 'jpg' : 'pdf' ?>"
                           class="btn-secondary">
                           <?= $t(['fr' => 'Télécharger','en' => 'Download']) ?>
                        </a>
                    </div>
                <?php else : ?>
                    <p style="color:#999;"><?= $t(['fr' => 'Aucun fichier','en' => 'No file']) ?></p>
                <?php endif; ?>
                <input type="file" name="<?= $key ?>" accept="<?= $key === 'photo' ? 'image/*' : '.pdf' ?>">
            </div>
            <?php endforeach; ?>

            <div id="justificatif_convention" style="display: none; margin-bottom: 20px;">
                <label><?= $t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>
                <?php if (!empty($pieces['convention'])) : ?>
                     <div style="margin-top: 10px;">
                        <a href="data:application/pdf;base64,<?= strval($pieces['convention']) ?>" download="convention.pdf" class="btn-secondary">
                            <?= $t(['fr' => 'Télécharger','en' => 'Download']) ?>
                        </a>
                    </div>
                <?php endif; ?>
                <input type="file" name="convention" accept=".pdf,.doc,.docx">
            </div>

            <div id="lettre_motivation" style="display: none; margin-bottom: 20px;">
                <label><?= $t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>
                <?php if (!empty($pieces['lettre_motivation'])) : ?>
                     <div style="margin-top: 10px;">
                        <a href="data:application/pdf;base64,<?= strval($pieces['lettre_motivation']) ?>" download="lettre.pdf" class="btn-secondary">
                            <?= $t(['fr' => 'Télécharger','en' => 'Download']) ?>
                        </a>
                    </div>
                <?php endif; ?>
                <input type="file" name="lettre_motivation" accept=".pdf,.doc,.docx">
            </div>
        </div>

        <div class="form-actions">
            <?php if ($isCreateMode) : ?>
                <button type="submit" class="btn-secondary">
                    <?= $t(['fr' => 'Déposer ma demande','en' => 'Submit my application']) ?>
                </button>
            <?php else : ?>
                <button type="submit" class="btn-secondary">
                    <?= $t(['fr' => 'Enregistrer mes modifications','en' => 'Save changes']) ?>
                </button>
            <?php endif; ?>
            
            <button type="button" class="btn-secondary" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-student']) ?>'">
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
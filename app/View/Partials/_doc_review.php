<?php
/**
 * Partial : Revue des pièces justificatives
 *
 * @var array<string, mixed> $pieces           tableau des pièces du dossier
 * @var array<string, mixed> $statuts          tableau des statuts par document
 * @var string               $numEtu           NumEtu déjà htmlspecialchars-é
 * @var bool                 $languesEditable  true = afficher l'input file pour langues
 * @var Closure              $t
 */

$languesEditable = $languesEditable ?? false;

$docTypes = [
    'photo'             => $t(['fr' => 'Photo',                  'en' => 'Photo']),
    'cv'                => $t(['fr' => 'CV',                     'en' => 'CV']),
    'convention'        => $t(['fr' => 'Convention de stage',    'en' => 'Internship Agreement']),
    'lettre_motivation' => $t(['fr' => 'Lettre de motivation',   'en' => 'Motivation Letter']),
    'langues'           => $t(['fr' => 'Attestation de langues', 'en' => 'Language Certificate']),
];
?>

<div class="doc-review-list">
    <?php foreach ($docTypes as $key => $label) :
        $doc     = $pieces[$key] ?? null;
        $hasDoc  = !empty($doc['file']);
        $statut  = $statuts[$key] ?? ($doc['status'] ?? 'pending');
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

                <?php if ($key === 'langues' && $languesEditable) : ?>
                    <br><br>
                    <label class="update-file-label">
                        <?= $t(['fr' => 'Mettre à jour l\'attestation :', 'en' => 'Update certificate:']) ?>
                    </label>
                    <input type="file" name="langues_file"
                           accept=".pdf,.doc,.docx,.jpg,.png"
                           class="file-input-margin">
                <?php elseif ($key !== 'langues' && isset($allFilesEditable) && $allFilesEditable) : ?>
                    <!-- admin : tous les fichiers éditables via btn Modifier -->
                    <br><br>
                    <label class="update-file-label">
                        <?= $t(['fr' => 'Mettre à jour le fichier (Optionnel) :', 'en' => 'Update file (Optional):']) ?>
                    </label>
                    <input type="file"
                           name="<?= $key === 'langues' ? 'langues_file' : $key ?>"
                           accept="<?= $key === 'photo' ? 'image/*' : '.pdf,.doc,.docx' ?>"
                           disabled class="input-disabled file-input-margin">
                <?php endif; ?>
            </div>

            <div class="doc-actions <?= !$hasDoc ? 'disabled-area' : '' ?>">
                <div class="status-radios">
                    <label class="radio-accept">
                        <input type="radio" name="status_<?= $key ?>" value="accepted"
                            <?= ($statut === 'accepted' || $statut === 'pending') ? 'checked' : '' ?>
                            <?= !$hasDoc ? 'disabled' : '' ?>>
                        <?= $t(['fr' => 'Accepter', 'en' => 'Accept']) ?>
                    </label>
                    <label class="radio-refuse">
                        <input type="radio" name="status_<?= $key ?>" value="refused"
                            <?= $statut === 'refused' ? 'checked' : '' ?>
                            <?= !$hasDoc ? 'disabled' : '' ?>>
                        <?= $t(['fr' => 'Refuser', 'en' => 'Refuse']) ?>
                    </label>
                </div>
                <textarea name="comment_<?= $key ?>"
                          placeholder="<?= $t(['fr' => 'Ajouter un commentaire pour l\'étudiant...', 'en' => 'Add a comment for the student...']) ?>"
                          <?= !$hasDoc ? 'disabled' : '' ?>><?= htmlspecialchars($comment) ?></textarea>

                <div class="doc-confirm-wrapper">
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
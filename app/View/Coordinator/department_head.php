<?php
/**
 * Page Chef de département
 *
 * @var string $lang
 * @var Closure $t
 * @var Closure $buildUrl
 * @var array<string, mixed> $filters
 * @var array<int, array<string, mixed>> $paginatedData
 * @var int $totalCount
 * @var array<string, mixed>|null $studentData
 * @var string $action
 * @var string $message
 */

$PAGE     = 'chef-departement';
$EDITABLE = ['niveau_etude', 'moyenne_sans_bac'];

$hasActiveFilters = (strval($filters['type']       ?? 'all')) !== 'all'
    || (strval($filters['zone']       ?? 'all')) !== 'all'
    || (strval($filters['complet']    ?? 'all')) !== 'all'
    || (strval($filters['composante'] ?? 'all')) !== 'all'
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
        $detectedType  = !empty($pieces['convention']['file']) ? 'stage' : (!empty($pieces['lettre_motivation']['file']) ? 'etudes' : '');
        $numEtu        = htmlspecialchars(strval($studentData['NumEtu'] ?? ''));
        $dateLimite    = $studentData['DateLimite'] ?? null;
        $currentStatus = $studentData['status'] ?? 'depot';
        ?>

        <h1><?= $t(['fr' => 'Dossier étudiant', 'en' => 'Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => $PAGE]) ?>'" class="btn-secondary">
                <?= $t(['fr' => 'Retour à la liste', 'en' => 'Back to List']) ?>
            </button>
        </div>

        <?php if (!empty($message)) : ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Spécifique chef de département : boutons Accepter / Refuser -->
        <?php include __DIR__ . '/../Partials/_banniere_decision.php'; ?>

        <?php
        $redirectPage = $PAGE;
        include __DIR__ . '/../Partials/_banniere_date_limite.php';
        ?>

        <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <?php
                $editableFields = $EDITABLE;
                $allEditable    = false;
                include __DIR__ . '/../Partials/_form_fields.php';
                ?>
            </div>

            <h2><?= $t(['fr' => 'Revue des Pièces Justificatives', 'en' => 'Documents Review']) ?></h2>
            <div class="form-section documents-section full-width">
                <?php
                $languesEditable = true;
                include __DIR__ . '/../Partials/_doc_review.php';
                ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-secondary">
                    <?= $t(['fr' => 'Enregistrer les modifications', 'en' => 'Save Changes']) ?>
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

            // setDecision : spécifique chef de département
            document.addEventListener('DOMContentLoaded', function () {
                if (window.folderManager) {
                    window.folderManager.setDecision = function (numetu, decision, btn) {
                        fetch('index.php?page=update_global_status&lang=<?= htmlspecialchars($lang) ?>', {
                            method : 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body   : 'numetu=' + encodeURIComponent(numetu) + '&status=' + encodeURIComponent(decision),
                        })
                            .then(r => r.json())
                            .then(data => {
                                const ind = document.getElementById('decision_indicator');
                                if (data.success) {
                                    document.querySelectorAll('.btn-decision').forEach(b => b.classList.remove('btn-decision-active'));
                                    btn.classList.add('btn-decision-active');
                                    ind.textContent = '✓ <?= $t(['fr' => 'Enregistré', 'en' => 'Saved']) ?>';
                                    ind.className   = 'status-indicator status-ok';
                                } else {
                                    ind.textContent = '✗ <?= $t(['fr' => 'Erreur', 'en' => 'Error']) ?>';
                                    ind.className   = 'status-indicator status-error';
                                }
                                setTimeout(() => { ind.textContent = ''; ind.className = 'status-indicator'; }, 3000);
                            })
                            .catch(() => {
                                const ind = document.getElementById('decision_indicator');
                                ind.textContent = '✗ <?= $t(['fr' => 'Erreur réseau', 'en' => 'Network error']) ?>';
                                ind.className   = 'status-indicator status-error';
                            });
                    };
                }
            });
        </script>

    <?php endif; ?>

<?php else : ?>

    <h1><?= $t(['fr' => 'Étudiants en mobilité d\'étude et de stage', 'en' => 'Study and Internship Mobility Students']) ?></h1>

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
    </div>

    <div class="filters-container">
        <p class="filters-title"><?= $t(['fr' => 'Filtres', 'en' => 'Filters']) ?></p>
        <?php
        $resetUrl         = $buildUrl('index.php', ['page' => $PAGE]);
        $showAccordFilter = false;
        $viewPage         = $PAGE;
        include __DIR__ . '/../Partials/_filters.php';
        include __DIR__ . '/../Partials/_table_etudiants.php';
        ?>
    </div>

<?php endif; ?>

<?php
$content = ob_get_clean();

$title           = $t(['fr' => 'Chef de département - Relations Internationales AMU', 'en' => 'Department Head - International Relations AMU']);
$styles          = ['styles/index.css', 'styles/folders.css', 'styles/chatbot.css'];
$scripts         = ['js/folders.js'];
$activeMenu      = $PAGE;
$userRole        = $_SESSION['role'] ?? 'chef_departement';
$metaDescription = $t(['fr' => 'Espace chef de département — gestion des mobilités étudiantes.', 'en' => 'Department head space — student mobility management.']);

include __DIR__ . '/../Layout/base.php';
<?php

/**
 * Page Chef de département
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
        'page' => 'chef-departement',
        'lang' => $lang,
    ]);
    return 'index.php?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
};

$hasActiveFilters = ($filters['type']   ?? 'all') !== 'all'
    || ($filters['zone']   ?? 'all') !== 'all'
    || ($filters['complet'] ?? 'all') !== 'all'
    || !empty($filters['search']);

ob_start();
?>

    <header>
        <div class="top-bar">
            <img class="logo_amu" src="img/logo.png" alt="AMU Logo">
            <div class="right-buttons">
                <div class="lang-dropdown">
                    <button class="dropbtn"><?= htmlspecialchars($lang) ?></button>
                    <div class="dropdown-content">
                        <a href="#" onclick="window.mainApp.changeLang('fr'); return false;">Français</a>
                        <a href="#" onclick="window.mainApp.changeLang('en'); return false;">English</a>
                    </div>
                </div>
                <?php if ($isLoggedIn): ?>
                    <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'logout']) ?>'">
                        <?= $t(['fr' => 'Se déconnecter', 'en' => 'Log out']) ?>
                    </button>
                <?php else: ?>
                    <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'login']) ?>'">
                        <?= $t(['fr' => 'Se connecter', 'en' => 'Log in']) ?>
                    </button>
                <?php endif; ?>
                <button id="theme-toggle" title="Enable tritanopia accessibility mode">
                    <span class="toggle-switch"></span>
                </button>
            </div>
        </div>

        <nav class="menu">
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'home-coordinateur']) ?>'">
                <?= $t(['fr' => 'Accueil', 'en' => 'Home']) ?>
            </button>
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'coordinateur-etude']) ?>'">
                <?= $t(['fr' => 'Coordinateur d\'étude', 'en' => 'Study Coordinator']) ?>
            </button>
            <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'coordinateur-stage']) ?>'">
                <?= $t(['fr' => 'Coordinateur de stage', 'en' => 'Internship Coordinator']) ?>
            </button>
            <button class= "active" onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'chef-departement']) ?>'">
                <?= $t(['fr' => 'Chef de département', 'en' => 'Department Head']) ?>
            </button>
        </nav>
    </header>

    <main class="folders">

        <?php if ($action === 'view' && $studentData): ?>

            <?php
            $pieces      = is_array($studentData['pieces'] ?? null) ? $studentData['pieces'] : [];
            $numEtu      = htmlspecialchars(strval($studentData['NumEtu'] ?? ''));
            $isComplete  = intval($studentData['IsComplete'] ?? 0);
            ?>

            <h1><?= $t(['fr' => 'Dossier étudiant', 'en' => 'Student Folder']) ?></h1>

            <div class="form-back-button">
                <button onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'chef-departement']) ?>'" class="btn-secondary">
                    <?= $t(['fr' => 'Retour à la liste', 'en' => 'Back to List']) ?>
                </button>
            </div>

            <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($lang) ?>" enctype="multipart/form-data" class="creation-form">
                <div class="form-section">
                    <label><?= $t(['fr' => 'NumÉtu', 'en' => 'Student ID']) ?></label>
                    <input type="text" value="<?= $numEtu ?>" disabled class="input-disabled">
                    <input type="hidden" name="numetu" value="<?= $numEtu ?>">

                    <label><?= $t(['fr' => 'Nom', 'en' => 'Last Name']) ?></label>
                    <input type="text" name="nom" value="<?= htmlspecialchars(strval($studentData['Nom'] ?? '')) ?>" disabled class="input-disabled">

                    <label><?= $t(['fr' => 'Prénom', 'en' => 'First Name']) ?></label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars(strval($studentData['Prenom'] ?? '')) ?>" disabled class="input-disabled">

                    <label><?= $t(['fr' => 'Né(e) le', 'en' => 'Date of Birth']) ?></label>
                    <input type="date" name="naissance" value="<?= htmlspecialchars(strval($studentData['DateNaissance'] ?? '')) ?>" disabled class="input-disabled">

                    <label><?= $t(['fr' => 'Sexe', 'en' => 'Gender']) ?></label>
                    <select name="sexe" disabled class="input-disabled">
                        <option value="M" <?= ($studentData['Sexe'] ?? '') === 'M' ? 'selected' : '' ?>><?= $t(['fr' => 'Masculin', 'en' => 'Male']) ?></option>
                        <option value="F" <?= ($studentData['Sexe'] ?? '') === 'F' ? 'selected' : '' ?>><?= $t(['fr' => 'Féminin', 'en' => 'Female']) ?></option>
                        <option value="Autre" <?= ($studentData['Sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>><?= $t(['fr' => 'Autre', 'en' => 'Other']) ?></option>
                    </select>

                    <label><?= $t(['fr' => 'Email Personnel', 'en' => 'Personal Email']) ?></label>
                    <input type="email" name="email_perso" value="<?= htmlspecialchars(strval($studentData['EmailPersonnel'] ?? '')) ?>" disabled class="input-disabled">

                    <label><?= $t(['fr' => 'Email AMU', 'en' => 'AMU Email']) ?></label>
                    <input type="email" name="email_amu" value="<?= htmlspecialchars(strval($studentData['EmailAMU'] ?? '')) ?>" disabled class="input-disabled">

                    <label><?= $t(['fr' => 'Téléphone', 'en' => 'Phone']) ?></label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars(strval($studentData['Telephone'] ?? '')) ?>" disabled class="input-disabled">

                    <label><?= $t(['fr' => 'Code Département', 'en' => 'Department Code']) ?></label>
                    <input type="text" name="departement" value="<?= htmlspecialchars(strval($studentData['CodeDepartement'] ?? '')) ?>" disabled class="input-disabled">

                    <label><?= $t(['fr' => 'Type', 'en' => 'Type']) ?></label>
                    <select name="type" disabled class="input-disabled">
                        <option value="entrant" <?= ($studentData['Type'] ?? '') === 'entrant' ? 'selected' : '' ?>><?= $t(['fr' => 'Entrant', 'en' => 'Incoming']) ?></option>
                        <option value="sortant" <?= ($studentData['Type'] ?? '') === 'sortant' ? 'selected' : '' ?>><?= $t(['fr' => 'Sortant', 'en' => 'Outgoing']) ?></option>
                    </select>

                    <label><?= $t(['fr' => 'Zone', 'en' => 'Zone']) ?></label>
                    <select name="zone" disabled class="input-disabled">
                        <option value="europe"      <?= ($studentData['Zone'] ?? '') === 'europe'      ? 'selected' : '' ?>><?= $t(['fr' => 'Europe', 'en' => 'Europe']) ?></option>
                        <option value="hors_europe" <?= ($studentData['Zone'] ?? '') === 'hors_europe' ? 'selected' : '' ?>><?= $t(['fr' => 'Hors Europe', 'en' => 'Non-Europe']) ?></option>
                    </select>
                </div>

                <!-- Documents -->
                <div class="form-section documents-section">
                    <h2><?= $t(['fr' => 'Pièces Justificatives', 'en' => 'Supporting Documents']) ?></h2>

                    <div class="document-block">
                        <label><?= $t(['fr' => 'Photo', 'en' => 'Photo']) ?></label>
                        <?php if (!empty($pieces['photo'])): ?>
                            <div class="document-preview">
                                <img src="data:image/jpeg;base64,<?= strval($pieces['photo']) ?>" alt="Photo" class="photo-preview"><br>
                                <a href="data:image/jpeg;base64,<?= strval($pieces['photo']) ?>" download="photo_<?= $numEtu ?>.jpg" class="btn-download">
                                    <?= $t(['fr' => 'Télécharger', 'en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="no-document"><?= $t(['fr' => 'Aucune photo', 'en' => 'No photo']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="document-block">
                        <label><?= $t(['fr' => 'CV', 'en' => 'CV']) ?></label>
                        <?php if (!empty($pieces['cv'])): ?>
                            <div class="document-preview">
                                <a href="data:application/pdf;base64,<?= strval($pieces['cv']) ?>" download="cv_<?= $numEtu ?>.pdf" class="btn-download">
                                    <?= $t(['fr' => 'Télécharger le CV', 'en' => 'Download CV']) ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="no-document"><?= $t(['fr' => 'Aucun CV', 'en' => 'No CV']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="document-block">
                        <label><?= $t(['fr' => 'Lettre de motivation', 'en' => 'Motivation Letter']) ?></label>
                        <?php if (!empty($pieces['lettre_motivation'])): ?>
                            <div class="document-preview">
                                <a href="data:application/pdf;base64,<?= strval($pieces['lettre_motivation']) ?>" download="lettre_<?= $numEtu ?>.pdf" class="btn-download">
                                    <?= $t(['fr' => 'Télécharger', 'en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="no-document"><?= $t(['fr' => 'Aucune lettre', 'en' => 'No letter']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="status-block status-<?= $isComplete ? 'complete' : 'incomplete' ?>">
                        <strong><?= $t(['fr' => 'Statut du dossier :', 'en' => 'Folder status:']) ?></strong>
                        <span class="status-text-<?= $isComplete ? 'complete' : 'incomplete' ?>">
                        <?= $isComplete ? $t(['fr' => 'Complet', 'en' => 'Complete']) : $t(['fr' => 'Incomplet', 'en' => 'Incomplete']) ?>
                    </span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary"
                            onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'chef-departement']) ?>'">
                        <?= $t(['fr' => 'Retour', 'en' => 'Back']) ?>
                    </button>
                </div>
            </form>

        <?php else: ?>

            <h1><?= $t(['fr' => 'Étudiants en mobilité d\'étude et de stage', 'en' => 'Study and Internship Mobility Students']) ?></h1>

            <?php if (!empty($message)):?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Barre de recherche + filtres -->
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

                    <!-- Entrant / Sortant -->
                    <div class="filter-group">
                        <label>
                            <input type="checkbox" name="entrant_sortant" value="entrant"
                                <?= ($filters['type'] ?? '') === 'entrant' ? 'checked' : '' ?>>
                            <?= $t(['fr' => 'Entrant', 'en' => 'Incoming']) ?>
                        </label>
                        <label>
                            <input type="checkbox" name="entrant_sortant" value="sortant"
                                   id="filter-sortant"
                                <?= ($filters['type'] ?? '') === 'sortant' ? 'checked' : '' ?>>
                            <?= $t(['fr' => 'Sortant', 'en' => 'Outgoing']) ?>
                        </label>
                    </div>

                    <!-- Zone — visible seulement si Sortant coché -->
                    <div class="filter-group" id="zone-filter-group"
                         style="<?= ($filters['type'] ?? '') === 'sortant' ? '' : 'display:none;' ?>">
                        <label>
                            <input type="checkbox" name="zone" value="europe"
                                <?= ($filters['zone'] ?? '') === 'europe' ? 'checked' : '' ?>>
                            <?= $t(['fr' => 'Europe', 'en' => 'Europe']) ?>
                        </label>
                        <label>
                            <input type="checkbox" name="zone" value="hors_europe"
                                <?= ($filters['zone'] ?? '') === 'hors_europe' ? 'checked' : '' ?>>
                            <?= $t(['fr' => 'Hors-Europe', 'en' => 'Non-Europe']) ?>
                        </label>
                    </div>

                    <!-- Statut -->
                    <div class="filter-group">
                        <label for="filter-complet"><?= $t(['fr' => 'Statut :', 'en' => 'Status:']) ?></label>
                        <select id="filter-complet">
                            <option value="all"  <?= ($filters['complet'] ?? 'all') === 'all' ? 'selected' : '' ?>><?= $t(['fr' => 'Tous', 'en' => 'All']) ?></option>
                            <option value="1"    <?= ($filters['complet'] ?? '') === '1'       ? 'selected' : '' ?>><?= $t(['fr' => 'Complet', 'en' => 'Complete']) ?></option>
                            <option value="0"    <?= ($filters['complet'] ?? '') === '0'       ? 'selected' : '' ?>><?= $t(['fr' => 'Incomplet', 'en' => 'Incomplete']) ?></option>
                        </select>
                    </div>

                    <?php if ($hasActiveFilters): ?>
                        <div class="filter-group">
                            <a href="<?= $buildUrl('index.php', ['page' => 'chef-departement']) ?>" class="btn-reset">
                                <?= $t(['fr' => 'Réinitialiser', 'en' => 'Reset']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <p class="results-count">
                <?= $totalCount ?> <?= $t(['fr' => 'étudiant(s) trouvé(s)', 'en' => 'student(s) found']) ?>
            </p>

            <table id="table-etudiants">
                <thead>
                <tr>
                    <th><?= $t(['fr' => 'Nom', 'en' => 'Last Name']) ?></th>
                    <th><?= $t(['fr' => 'Prénom', 'en' => 'First Name']) ?></th>
                    <th><?= $t(['fr' => 'Né(e) le', 'en' => 'Birth Date']) ?></th>
                    <th><?= $t(['fr' => 'Type', 'en' => 'Type']) ?></th>
                    <th><?= $t(['fr' => 'Zone', 'en' => 'Zone']) ?></th>
                    <th><?= $t(['fr' => 'Statut', 'en' => 'Status']) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($paginatedData as $etudiant):
                    $numEtu     = strval($etudiant['NumEtu'] ?? '');
                    $type       = strval($etudiant['Type']   ?? '');
                    $zone       = strval($etudiant['Zone']   ?? '');
                    $isComplete = intval($etudiant['IsComplete'] ?? 0);
                    ?>
                    <tr class="clickable-row"
                        data-numetu="<?= htmlspecialchars($numEtu) ?>"
                        onclick="window.location.href='<?= $buildUrl('index.php', ['page' => 'chef-departement', 'action' => 'view', 'numetu' => $numEtu]) ?>'">
                        <td><?= htmlspecialchars(strval($etudiant['Nom']    ?? '')) ?></td>
                        <td><?= htmlspecialchars(strval($etudiant['Prenom'] ?? '')) ?></td>
                        <td><?= htmlspecialchars(strval($etudiant['DateNaissance'] ?? '')) ?></td>
                        <td><?= $t(['fr' => ($type === 'entrant' ? 'Entrant' : 'Sortant'), 'en' => ($type === 'entrant' ? 'Incoming' : 'Outgoing')]) ?></td>
                        <td><?= $t(['fr' => ($zone === 'europe' ? 'Europe' : 'Hors Europe'), 'en' => ($zone === 'europe' ? 'Europe' : 'Non-Europe')]) ?></td>
                        <td>
                            <?= $isComplete
                                ? '<span class="status-complete">Complet</span>'
                                : '<span class="status-incomplete">Incomplet</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <button onclick="window.location.href='<?= $buildPaginationUrl(1) ?>'">«</button>
                        <button onclick="window.location.href='<?= $buildPaginationUrl($page - 1) ?>'">‹</button>
                    <?php else: ?>
                        <button disabled>«</button>
                        <button disabled>‹</button>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <button class="<?= $i === $page ? 'active' : '' ?>"
                                onclick="window.location.href='<?= $buildPaginationUrl($i) ?>'"><?= $i ?></button>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <button onclick="window.location.href='<?= $buildPaginationUrl($page + 1) ?>'">›</button>
                        <button onclick="window.location.href='<?= $buildPaginationUrl($totalPages) ?>'">»</button>
                    <?php else: ?>
                        <button disabled>›</button>
                        <button disabled>»</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="coordinateur"
         style="display:none;">
    </div>

    <script>
        // Affiche/masque le filtre zone selon si "Sortant" est coché
        (function () {
            const sortantCb  = document.getElementById('filter-sortant');
            const zoneGroup  = document.getElementById('zone-filter-group');
            if (!sortantCb || !zoneGroup) return;

            sortantCb.addEventListener('change', function () {
                zoneGroup.style.display = this.checked ? '' : 'none';
                if (!this.checked) {
                    zoneGroup.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
                }
            });
        })();
    </script>

<?php
$content = ob_get_clean();

$title   = $t([
    'fr' => 'Chef de département - Relations Internationales AMU',
    'en' => 'Internship Coordinator - International Relations AMU',
]);

$styles     = ['styles/homepage.css', 'styles/folders.css'];
$scripts    = ['js/coordinator.js'];
$userRole   = 'coordinateur';

$metaDescription = $t([
    'fr' => "Espace chef de département — gestion des mobilités étudiantes.",
    'en' => 'Internship coordinator space — student mobility management.',
]);

include __DIR__ . '/../Layout/base_home.php';
<?php
/**
 * Super Admin - Gestion des comptes
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array<int, array{login: string, role: string, departement: string|null, site: string|null, created_at: string}> $accounts
 * @var array<int, string> $departments
 * @var array<int, string> $sites
 * @var string|null $success
 * @var string|null $error
 * @var bool $tritanopia
 */

ob_start();
?>

    <div class="sa-wrapper">

        <div class="sa-header">
            <div class="sa-header-icon">⚙</div>
            <div>
                <h1 class="sa-title"><?= $t(['fr' => 'Administration', 'en' => 'Administration']) ?></h1>
                <p class="sa-subtitle"><?= $t(['fr' => 'Gestion des comptes secrétaires & coordinateurs', 'en' => 'Secretary & coordinator account management']) ?></p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="sa-alert sa-alert--success"><span class="sa-alert-icon">✓</span><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="sa-alert sa-alert--error"><span class="sa-alert-icon">✗</span><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="sa-grid">

            <!-- FORMULAIRE CRÉATION -->
            <div class="sa-card">
                <h2 class="sa-card-title">
                    <span class="sa-card-icon">＋</span>
                    <?= $t(['fr' => 'Créer un compte', 'en' => 'Create an account']) ?>
                </h2>

                <form method="POST" class="sa-form" id="createForm">
                    <input type="hidden" name="action" value="create">

                    <div class="sa-field">
                        <label class="sa-label" for="login"><?= $t(['fr' => 'Email (login)', 'en' => 'Email (login)']) ?></label>
                        <input class="sa-input" type="email" id="login" name="login" required
                               placeholder="prenom.nom@univ-amu.fr" autocomplete="off">
                    </div>

                    <div class="sa-field">
                        <label class="sa-label" for="password"><?= $t(['fr' => 'Mot de passe', 'en' => 'Password']) ?></label>
                        <div class="sa-input-group">
                            <input class="sa-input" type="text" id="password" name="password" required
                                   placeholder="<?= $t(['fr' => 'Mot de passe temporaire', 'en' => 'Temporary password']) ?>"
                                   autocomplete="new-password">
                            <button type="button" class="sa-btn-generate" onclick="SuperAdmin.generatePassword()">
                                <?= $t(['fr' => 'Générer', 'en' => 'Generate']) ?>
                            </button>
                        </div>
                    </div>

                    <div class="sa-field">
                        <label class="sa-label"><?= $t(['fr' => 'Rôle', 'en' => 'Role']) ?></label>
                        <div class="sa-role-selector">
                            <label class="sa-role-option">
                                <input type="radio" name="role_type" value="admin" required onchange="SuperAdmin.toggleRoleFields('admin')">
                                <span class="sa-role-card">
                                <span class="sa-role-icon">🗂</span>
                                <span class="sa-role-name"><?= $t(['fr' => 'Secrétaire', 'en' => 'Secretary']) ?></span>
                                <span class="sa-role-desc"><?= $t(['fr' => 'Gestion des dossiers', 'en' => 'File management']) ?></span>
                            </span>
                            </label>
                            <label class="sa-role-option">
                                <input type="radio" name="role_type" value="coordinateur" required onchange="SuperAdmin.toggleRoleFields('coordinateur')">
                                <span class="sa-role-card">
                                <span class="sa-role-icon">🎓</span>
                                <span class="sa-role-name"><?= $t(['fr' => 'Coordinateur', 'en' => 'Coordinator']) ?></span>
                                <span class="sa-role-desc"><?= $t(['fr' => 'Supervision & validation', 'en' => 'Supervision & validation']) ?></span>
                            </span>
                            </label>
                        </div>
                    </div>

                    <!-- Type de coordinateur -->
                    <div class="sa-field" id="coordTypeField" style="display:none;">
                        <label class="sa-label"><?= $t(['fr' => 'Type de coordinateur', 'en' => 'Coordinator type']) ?></label>
                        <div class="sa-coord-type-grid">
                            <label class="sa-coord-option">
                                <input type="radio" name="role" value="coordinateur_etude">
                                <span class="sa-coord-card">
                                <span class="sa-coord-icon">📚</span>
                                <span><?= $t(['fr' => 'Coordinateur d\'étude', 'en' => 'Study coordinator']) ?></span>
                            </span>
                            </label>
                            <label class="sa-coord-option">
                                <input type="radio" name="role" value="coordinateur_stage">
                                <span class="sa-coord-card">
                                <span class="sa-coord-icon">💼</span>
                                <span><?= $t(['fr' => 'Coordinateur de stage', 'en' => 'Internship coordinator']) ?></span>
                            </span>
                            </label>
                            <label class="sa-coord-option">
                                <input type="radio" name="role" value="coordinateur">
                                <span class="sa-coord-card">
                                <span class="sa-coord-icon">🔄</span>
                                <span><?= $t(['fr' => 'Étude & Stage', 'en' => 'Study & Internship']) ?></span>
                            </span>
                            </label>
                            <label class="sa-coord-option">
                                <input type="radio" name="role" value="chef_departement">
                                <span class="sa-coord-card">
                                <span class="sa-coord-icon">🏛</span>
                                <span><?= $t(['fr' => 'Chef de département', 'en' => 'Department head']) ?></span>
                            </span>
                            </label>
                        </div>
                    </div>

                    <!-- Département -->
                    <div class="sa-field" id="deptField" style="display:none;">
                        <label class="sa-label" for="departement"><?= $t(['fr' => 'Département', 'en' => 'Department']) ?></label>
                        <div class="sa-input-group">
                            <select class="sa-input" id="departement" name="departement">
                                <option value=""><?= $t(['fr' => '-- Choisir un département --', 'en' => '-- Choose a department --']) ?></option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="sa-add-dept">
                            <p class="sa-label"><?= $t(['fr' => 'Ou ajouter un nouveau département :', 'en' => 'Or add a new department:']) ?></p>
                            <div class="sa-add-dept-row">
                                <input class="sa-input" type="text" id="newDeptInput"
                                       placeholder="<?= $t(['fr' => 'Ex: DROIT', 'en' => 'E.g. LAW']) ?>"
                                       style="text-transform:uppercase;">
                                <button type="button" class="sa-btn-generate" onclick="SuperAdmin.addDepartment()">
                                    <?= $t(['fr' => 'Ajouter', 'en' => 'Add']) ?>
                                </button>
                            </div>
                            <p id="deptMsg" class="sa-inline-msg" style="display:none;"></p>
                        </div>
                    </div>

                    <!-- Site -->
                    <div class="sa-field" id="siteField" style="display:none;">
                        <label class="sa-label" for="site"><?= $t(['fr' => 'Site', 'en' => 'Site']) ?></label>
                        <div class="sa-input-group">
                            <select class="sa-input" id="site" name="site">
                                <option value=""><?= $t(['fr' => '-- Choisir un site --', 'en' => '-- Choose a site --']) ?></option>
                                <?php foreach ($sites as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="sa-add-dept">
                            <p class="sa-label"><?= $t(['fr' => 'Ou ajouter un nouveau site :', 'en' => 'Or add a new site:']) ?></p>
                            <div class="sa-add-dept-row">
                                <input class="sa-input" type="text" id="newSiteInput"
                                       placeholder="<?= $t(['fr' => 'Ex: Site Schuman', 'en' => 'E.g. Schuman Site']) ?>">
                                <button type="button" class="sa-btn-generate" onclick="SuperAdmin.addSite()">
                                    <?= $t(['fr' => 'Ajouter', 'en' => 'Add']) ?>
                                </button>
                            </div>
                            <p id="siteMsg" class="sa-inline-msg" style="display:none;"></p>
                        </div>
                    </div>

                    <input type="hidden" id="roleHiddenAdmin" name="role" value="">

                    <button type="submit" class="sa-btn-submit">
                        <?= $t(['fr' => 'Créer le compte & envoyer l\'email', 'en' => 'Create account & send email']) ?>
                    </button>
                </form>
            </div>

            <!-- LISTE DES COMPTES -->
            <div class="sa-card">
                <h2 class="sa-card-title">
                    <span class="sa-card-icon">👥</span>
                    <?= $t(['fr' => 'Comptes existants', 'en' => 'Existing accounts']) ?>
                    <span class="sa-badge"><?= count($accounts) ?></span>
                </h2>

                <?php if (empty($accounts)): ?>
                    <div class="sa-empty"><?= $t(['fr' => 'Aucun compte créé pour l\'instant.', 'en' => 'No accounts created yet.']) ?></div>
                <?php else: ?>
                    <div class="sa-accounts-list">
                        <?php foreach ($accounts as $account): ?>
                            <div class="sa-account-item">
                                <div class="sa-account-info">
                                    <div class="sa-account-avatar"><?= strtoupper(substr($account['login'], 0, 1)) ?></div>
                                    <div class="sa-account-details">
                                        <div class="sa-account-login"><?= htmlspecialchars($account['login']) ?></div>
                                        <div class="sa-account-meta">
                                        <span class="sa-role-badge sa-role-badge--<?= htmlspecialchars($account['role']) ?>">
                                            <?= match($account['role']) {
                                                'admin'              => $t(['fr' => 'Secrétaire',                'en' => 'Secretary']),
                                                'coordinateur'       => $t(['fr' => 'Coord. Étude & Stage',      'en' => 'Study & Internship Coord.']),
                                                'coordinateur_etude' => $t(['fr' => 'Coord. d\'étude',          'en' => 'Study Coordinator']),
                                                'coordinateur_stage' => $t(['fr' => 'Coord. de stage',          'en' => 'Internship Coordinator']),
                                                'chef_departement'   => $t(['fr' => 'Chef de département',      'en' => 'Department Head']),
                                                default              => htmlspecialchars($account['role']),
                                            } ?>
                                        </span>
                                            <?php if (!empty($account['departement'])): ?>
                                                <span class="sa-dept-badge">🏛 <?= htmlspecialchars($account['departement']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($account['site'])): ?>
                                                <span class="sa-dept-badge sa-dept-badge--site">📍 <?= htmlspecialchars($account['site']) ?></span>
                                            <?php endif; ?>
                                            <span class="sa-account-date">
                                            <?= $t(['fr' => 'Créé le', 'en' => 'Created on']) ?>
                                            <?= htmlspecialchars(date('d/m/Y', strtotime($account['created_at']))) ?>
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <form method="POST" onsubmit="return SuperAdmin.confirmDelete('<?= htmlspecialchars($account['login']) ?>')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="login" value="<?= htmlspecialchars($account['login']) ?>">
                                    <button type="submit" class="sa-btn-delete" title="<?= $t(['fr' => 'Supprimer', 'en' => 'Delete']) ?>">🗑</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <form method="POST" id="addDeptForm" style="display:none;">
        <input type="hidden" name="action" value="add_department">
        <input type="hidden" name="new_department" id="hiddenDeptValue">
    </form>
    <form method="POST" id="addSiteForm" style="display:none;">
        <input type="hidden" name="action" value="add_site">
        <input type="hidden" name="new_site" id="hiddenSiteValue">
    </form>

<?php
$deleteLabel = $t(['fr' => 'Supprimer le compte', 'en' => 'Delete account']);
?>
    <script>
        window.SA_DELETE_LABEL = <?= json_encode($deleteLabel) ?>;
    </script>

<?php
$content = ob_get_clean();
$title      = 'Super Admin — AMU';
$styles     = ['styles/super_admin.css', 'styles/homepage.css'];
$scripts    = ['js/super_admin.js'];
$activeMenu = '';
$userRole   = 'super_admin';
include __DIR__ . '/../Layout/base_superadmin.php';
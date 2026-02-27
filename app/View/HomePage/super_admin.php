<?php
/**
 * Super Admin - Gestion des comptes
 *
 * Vue permettant au super administrateur :
 * - De créer des comptes (secrétaire / coordinateur)
 * - De supprimer des comptes existants
 * - De visualiser la liste des comptes
 *
 * Variables attendues :
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array<int, array{login: string, role: string, created_at: string}> $accounts
 * @var string|null $success
 * @var string|null $error
 * @var bool $tritanopia  Indique si le mode daltonien (tritanopie) est actif
 */

ob_start();
?>

    <div class="sa-wrapper">

        <!-- En-tête -->
        <div class="sa-header">
            <div class="sa-header-icon">⚙</div>
            <div>
                <h1 class="sa-title"><?= $t(['fr' => 'Administration', 'en' => 'Administration']) ?></h1>
                <p class="sa-subtitle">
                    <?= $t([
                        'fr' => 'Gestion des comptes secrétaires & coordinateurs',
                        'en' => 'Secretary & coordinator account management'
                    ]) ?>
                </p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="sa-alert sa-alert--success">
                <span class="sa-alert-icon">✓</span>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="sa-alert sa-alert--error">
                <span class="sa-alert-icon">✗</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="sa-grid">

            <!-- =============================
                 FORMULAIRE DE CRÉATION
            ============================== -->
            <div class="sa-card">
                <h2 class="sa-card-title">
                    <span class="sa-card-icon">＋</span>
                    <?= $t(['fr' => 'Créer un compte', 'en' => 'Create an account']) ?>
                </h2>

                <form method="POST" class="sa-form">
                    <input type="hidden" name="action" value="create">

                    <div class="sa-field">
                        <label class="sa-label" for="login">
                            <?= $t(['fr' => 'Email (login)', 'en' => 'Email (login)']) ?>
                        </label>
                        <input
                                class="sa-input"
                                type="email"
                                id="login"
                                name="login"
                                required
                                placeholder="prenom.nom@univ-amu.fr"
                                autocomplete="off"
                        >
                    </div>

                    <div class="sa-field">
                        <label class="sa-label" for="password">
                            <?= $t(['fr' => 'Mot de passe', 'en' => 'Password']) ?>
                        </label>
                        <div class="sa-input-group">
                            <input
                                    class="sa-input"
                                    type="text"
                                    id="password"
                                    name="password"
                                    required
                                    placeholder="<?= $t(['fr' => 'Mot de passe temporaire', 'en' => 'Temporary password']) ?>"
                                    autocomplete="new-password"
                            >
                            <button type="button" class="sa-btn-generate" onclick="generatePassword()">
                                <?= $t(['fr' => 'Générer', 'en' => 'Generate']) ?>
                            </button>
                        </div>
                    </div>

                    <div class="sa-field">
                        <label class="sa-label">
                            <?= $t(['fr' => 'Rôle', 'en' => 'Role']) ?>
                        </label>

                        <div class="sa-role-selector">
                            <label class="sa-role-option">
                                <input type="radio" name="role" value="admin" required>
                                <span class="sa-role-card">
                                <span class="sa-role-icon">🗂</span>
                                <span class="sa-role-name">
                                    <?= $t(['fr' => 'Secrétaire', 'en' => 'Secretary']) ?>
                                </span>
                                <span class="sa-role-desc">
                                    <?= $t(['fr' => 'Gestion des dossiers', 'en' => 'File management']) ?>
                                </span>
                            </span>
                            </label>

                            <label class="sa-role-option">
                                <input type="radio" name="role" value="coordinateur" required>
                                <span class="sa-role-card">
                                <span class="sa-role-icon">🎓</span>
                                <span class="sa-role-name">
                                    <?= $t(['fr' => 'Coordinateur', 'en' => 'Coordinator']) ?>
                                </span>
                                <span class="sa-role-desc">
                                    <?= $t(['fr' => 'Supervision & validation', 'en' => 'Supervision & validation']) ?>
                                </span>
                            </span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="sa-btn-submit">
                        <?= $t([
                            'fr' => 'Créer le compte & envoyer l\'email',
                            'en' => 'Create account & send email'
                        ]) ?>
                    </button>
                </form>
            </div>

            <!-- =============================
                 LISTE DES COMPTES
            ============================== -->
            <div class="sa-card">
                <h2 class="sa-card-title">
                    <span class="sa-card-icon">👥</span>
                    <?= $t(['fr' => 'Comptes existants', 'en' => 'Existing accounts']) ?>
                    <span class="sa-badge"><?= count($accounts) ?></span>
                </h2>

                <?php if (empty($accounts)): ?>
                    <div class="sa-empty">
                        <?= $t([
                            'fr' => 'Aucun compte créé pour l\'instant.',
                            'en' => 'No accounts created yet.'
                        ]) ?>
                    </div>
                <?php else: ?>
                    <div class="sa-accounts-list">
                        <?php foreach ($accounts as $account): ?>
                            <div class="sa-account-item">
                                <div class="sa-account-info">

                                    <div class="sa-account-avatar">
                                        <?= strtoupper(substr($account['login'], 0, 1)) ?>
                                    </div>

                                    <div class="sa-account-details">
                                        <div class="sa-account-login">
                                            <?= htmlspecialchars($account['login']) ?>
                                        </div>

                                        <div class="sa-account-meta">
                                        <span class="sa-role-badge sa-role-badge--<?= htmlspecialchars($account['role']) ?>">
                                            <?php
                                            echo match($account['role']) {
                                                'admin'        => $t(['fr' => 'Secrétaire', 'en' => 'Secretary']),
                                                'coordinateur' => $t(['fr' => 'Coordinateur', 'en' => 'Coordinator']),
                                                default        => htmlspecialchars($account['role']),
                                            };
                                            ?>
                                        </span>

                                            <span class="sa-account-date">
                                            <?= $t(['fr' => 'Créé le', 'en' => 'Created on']) ?>
                                            <?= htmlspecialchars(date('d/m/Y', strtotime($account['created_at']))) ?>
                                        </span>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST"
                                      onsubmit="return confirmDelete('<?= htmlspecialchars($account['login']) ?>')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="login" value="<?= htmlspecialchars($account['login']) ?>">

                                    <button type="submit"
                                            class="sa-btn-delete"
                                            title="<?= $t(['fr' => 'Supprimer', 'en' => 'Delete']) ?>">
                                        🗑
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
        function generatePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
            let pwd = '';
            for (let i = 0; i < 12; i++) {
                pwd += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = pwd;
        }

        function confirmDelete(login) {
            return confirm(
                '<?= $t(['fr' => 'Supprimer le compte', 'en' => 'Delete account']) ?> ' + login + ' ?'
            );
        }
    </script>

<?php
$content = ob_get_clean();

$title      = 'Super Admin — AMU';
$styles     = ['styles/super_admin.css', 'styles/homepage.css'];
$scripts    = [];
$activeMenu = '';
$userRole   = 'super_admin';

include __DIR__ . '/../Layout/base_superadmin.php';
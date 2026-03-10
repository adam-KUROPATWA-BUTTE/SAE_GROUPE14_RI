<?php
/**
 * Dashboard Student - Contenu uniquement
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var string $progressStyle
 * @var string $status
 * @var array<string, mixed> $folder
 */

ob_start();

$decisionClass = '';
if ($status === 'accepte') $decisionClass = 'decision-accepted';
elseif ($status === 'refuse') $decisionClass = 'decision-refused';
?>

    <h1><?= $t(['fr' => 'Suivi du dossier', 'en' => 'File Tracking']) ?></h1>

    <div class="progress-container <?= $decisionClass ?>">
        <div class="progress-line" style="<?= $progressStyle ?>"></div>

        <div class="progress-step <?= in_array($status, ['depot', 'instruction', 'accepte', 'refuse'], true) ? 'active' : '' ?>">
            <div class="progress-icon"><img src="img/depot.png" alt="Dépôt"></div>
            <div class="progress-circle"></div>
            <span><?= $t(['fr' => 'Dépôt de la demande', 'en' => 'Application Submitted']) ?></span>
        </div>

        <div class="progress-step <?= in_array($status, ['instruction', 'accepte', 'refuse'], true) ? 'active' : '' ?>">
            <div class="progress-icon"><img src="img/rafraichir.png" alt="Instruction"></div>
            <div class="progress-circle"></div>
            <span><?= $t(['fr' => 'Instruction en cours', 'en' => 'Under Review']) ?></span>
        </div>

        <div class="progress-step <?= in_array($status, ['accepte', 'refuse'], true) ? 'active' : '' ?>">
            <div class="progress-icon"><img src="img/decision.png" alt="Décision"></div>
            <div class="progress-circle"></div>
            <span><?= $t(['fr' => 'Décision prise', 'en' => 'Decision Made']) ?></span>
        </div>
    </div>

    <div class="contact-info-box">
        <p class="contact-title"><?= $t(['fr' => 'Une question ou besoin d\'assistance ?', 'en' => 'A question or need assistance?']) ?></p>
        <p><?= $t(['fr' => 'Pour toute information complémentaire...', 'en' => 'For any additional information...']) ?></p>
        <p class="contact-email">
            <a href="mailto:relations.internationale@amu-univ.fr">relations.internationale@amu-univ.fr</a>
        </p>
    </div>

<div id="app-config"
     data-lang="<?= htmlspecialchars($lang) ?>"
     data-role="student"
     style="display:none;">
</div>
<?php
$content = ob_get_clean();

$title = $t(['fr' => 'Tableau de bord (Étudiant)', 'en' => 'Student Dashboard']);
$styles = ['styles/dashboard.css', 'styles/index.css', 'styles/chatbot.css'];
$scripts = [];
$activeMenu = 'dashboard';
$userRole = 'student';

include __DIR__ . '/../Layout/base.php';
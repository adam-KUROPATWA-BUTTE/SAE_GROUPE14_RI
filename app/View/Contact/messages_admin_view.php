<?php
/**
 * Détail d'un message admin
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var \Model\Entity\ContactMessage $message
 */

ob_start();

$subjects = [
    'mobility' => $t(['fr' => 'Question sur ma mobilité', 'en' => 'Mobility question']),
    'documents' => $t(['fr' => 'Documents requis', 'en' => 'Required documents']),
    'partners' => $t(['fr' => 'Universités partenaires', 'en' => 'Partner universities']),
    'technical' => $t(['fr' => 'Problème technique', 'en' => 'Technical issue']),
    'other' => $t(['fr' => 'Autre', 'en' => 'Other'])
];
?>

    <div class="message-view-container">
        <div class="message-view-header">
            <a href="index.php?page=messages-admin&lang=<?= $lang ?>" class="back-btn">
                ← <?= $t(['fr' => 'Retour', 'en' => 'Back']) ?>
            </a>

            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="delete-btn"
                        onclick="return confirm('<?= $t(['fr' => 'Supprimer ce message ?', 'en' => 'Delete this message?']) ?>')">
                    🗑️ <?= $t(['fr' => 'Supprimer', 'en' => 'Delete']) ?>
                </button>
            </form>
        </div>

        <div class="message-detail">
            <div class="message-info">
                <div class="info-row">
                    <span class="info-label"><?= $t(['fr' => 'De :', 'en' => 'From:']) ?></span>
                    <span class="info-value">
                    <?= htmlspecialchars($message->getName()) ?>
                    <small>(<?= htmlspecialchars($message->getStudentNumEtu()) ?>)</small>
                </span>
                </div>

                <div class="info-row">
                    <span class="info-label"><?= $t(['fr' => 'Email :', 'en' => 'Email:']) ?></span>
                    <span class="info-value">
                    <a href="mailto:<?= htmlspecialchars($message->getEmail()) ?>">
                        <?= htmlspecialchars($message->getEmail()) ?>
                    </a>
                </span>
                </div>

                <div class="info-row">
                    <span class="info-label"><?= $t(['fr' => 'Sujet :', 'en' => 'Subject:']) ?></span>
                    <span class="info-value">
                    <?= htmlspecialchars($subjects[$message->getSubject()] ?? $message->getSubject()) ?>
                </span>
                </div>

                <div class="info-row">
                    <span class="info-label"><?= $t(['fr' => 'Date :', 'en' => 'Date:']) ?></span>
                    <span class="info-value"><?= $message->getCreatedAt()->format('d/m/Y H:i') ?></span>
                </div>
            </div>

            <div class="message-content">
                <h3><?= $t(['fr' => 'Message', 'en' => 'Message']) ?></h3>
                <div class="message-text">
                    <?= nl2br(htmlspecialchars($message->getMessage())) ?>
                </div>
            </div>

            <?php if ($message->getAdminResponse()): ?>
                <div class="admin-response-view">
                    <h3><?= $t(['fr' => 'Votre réponse', 'en' => 'Your response']) ?></h3>
                    <div class="response-text">
                        <?= nl2br(htmlspecialchars($message->getAdminResponse())) ?>
                    </div>
                    <?php
                    $respondedAt = $message->getRespondedAt();
                    ?>
                    <small>
                        <?= $t(['fr' => 'Envoyée le', 'en' => 'Sent on']) ?>
                        <?= $respondedAt
                            ? $respondedAt->format('d/m/Y H:i')
                            : $t(['fr' => 'Date inconnue', 'en' => 'Unknown date']) ?>
                    </small>
                </div>
            <?php else: ?>
                <div class="admin-response-form">
                    <h3><?= $t(['fr' => 'Répondre à l\'étudiant', 'en' => 'Reply to student']) ?></h3>

                    <form method="POST" action="index.php?page=messages-admin&action=respond&id=<?= $message->getId() ?>&lang=<?= $lang ?>">
                    <textarea
                        name="response"
                        rows="6"
                        required
                        placeholder="<?= $t(['fr' => 'Votre réponse...', 'en' => 'Your response...']) ?>"
                    ></textarea>

                        <button type="submit" class="btn-submit">
                            <?= $t(['fr' => 'Envoyer la réponse', 'en' => 'Send response']) ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="admin"
         style="display:none;">
    </div>

<?php
$content = ob_get_clean();

$title = $t(['fr' => 'Message de', 'en' => 'Message from']) . ' ' . $message->getName();
$styles = ['styles/messages_admin.css'];
$scripts = [];
$activeMenu = 'messages';
$userRole = 'admin';

include __DIR__ . '/../Layout/base.php';
<?php
/**
 * Détail d'une conversation admin
 */

ob_start();

$subjects = [
    'mobility'  => $t(['fr' => 'Question sur ma mobilité', 'en' => 'Mobility question']),
    'documents' => $t(['fr' => 'Documents requis', 'en' => 'Required documents']),
    'partners'  => $t(['fr' => 'Universités partenaires', 'en' => 'Partner universities']),
    'technical' => $t(['fr' => 'Problème technique', 'en' => 'Technical issue']),
    'other'     => $t(['fr' => 'Autre', 'en' => 'Other'])
];
?>

<div class="message-view-container">
    <div class="message-view-header">
        <a href="index.php?page=messages-admin&lang=<?= $lang ?>" class="back-btn">
            ← <?= $t(['fr' => 'Retour', 'en' => 'Back']) ?>
        </a>

        <form method="POST" action="index.php?page=messages-admin&lang=<?= $lang ?>" class="inline-form">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $conversation->getId() ?>"> 
            <button type="submit" class="delete-btn"
                    onclick="return confirm('<?= $t(['fr' => 'Supprimer ?', 'en' => 'Delete?']) ?>')">
                🗑️ <?= $t(['fr' => 'Supprimer', 'en' => 'Delete']) ?>
            </button>
        </form>
    </div>

    <div class="message-detail">
        <div class="message-info">
            <div class="info-row">
                <span class="info-label"><?= $t(['fr' => 'Étudiant :', 'en' => 'Student:']) ?></span>
                <span class="info-value">
                    <?= htmlspecialchars($conversation->getName()) ?>
                    <small>(<?= htmlspecialchars($conversation->getStudentNumEtu()) ?>)</small>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label"><?= $t(['fr' => 'Email :', 'en' => 'Email:']) ?></span>
                <span class="info-value">
                    <a href="mailto:<?= htmlspecialchars($conversation->getEmail()) ?>">
                        <?= htmlspecialchars($conversation->getEmail()) ?>
                    </a>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label"><?= $t(['fr' => 'Sujet :', 'en' => 'Subject:']) ?></span>
                <span class="info-value">
                    <?= htmlspecialchars($subjects[$conversation->getSubject()] ?? $conversation->getSubject()) ?>
                </span>
            </div>
        </div>

        <div class="message-content">
            <h3><?= $t(['fr' => 'Fil de discussion', 'en' => 'Discussion thread']) ?></h3>
            
            <div class="chat-history">
                <?php foreach ($conversation->getMessages() as $msg): ?>
                    <?php $isAdmin = $msg->getSenderType() === 'admin'; ?>
                    
                    <div class="chat-message <?= $isAdmin ? 'chat-message--admin' : 'chat-message--student' ?>">
                        <div class="chat-message-meta">
                            <strong><?= $isAdmin ? $t(['fr' => 'Vous (Service RI)', 'en' => 'You (IR Office)']) : htmlspecialchars($conversation->getName()) ?></strong>
                            <span class="chat-message-date"><?= $msg->getCreatedAt()->format('d/m/Y H:i') ?></span>
                        </div>
                        <div class="chat-message-text">
                            <?= nl2br(htmlspecialchars($msg->getContent())) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="admin-response-form">
            <h3><?= $t(['fr' => 'Répondre', 'en' => 'Reply']) ?></h3>

            <form method="POST" action="index.php?page=messages-admin&action=respond&id=<?= $conversation->getId() ?>&lang=<?= $lang ?>">
                <textarea
                    name="response"
                    rows="4"
                    required
                    placeholder="<?= $t(['fr' => 'Écrivez votre réponse ici...', 'en' => 'Type your reply here...']) ?>"
                ></textarea>

                <button type="submit" class="btn-submit">
                    <?= $t(['fr' => 'Envoyer', 'en' => 'Send']) ?>
                </button>
            </form>
        </div>
    </div>
</div>

<div id="app-config" class="is-hidden" data-lang="<?= htmlspecialchars($lang) ?>" data-role="admin"></div>

<?php
$content = ob_get_clean();
$title = $t(['fr' => 'Ticket de', 'en' => 'Ticket from']) . ' ' . $conversation->getName();
$styles = ['styles/messages_admin.css'];
$scripts = [];
$activeMenu = 'messages';
$userRole = 'admin';

include __DIR__ . '/../Layout/base.php';
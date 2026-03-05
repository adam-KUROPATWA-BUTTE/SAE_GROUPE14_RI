<?php
/**
 * Liste des conversations admin — layout style Outlook
 * @var string $lang
 * @var array $conversations
 * @var string $filter
 * @var Closure $t
 */

ob_start();

$subjects = [
    'mobility'  => $t(['fr' => 'Question sur ma mobilité',  'en' => 'Mobility question']),
    'documents' => $t(['fr' => 'Documents requis',          'en' => 'Required documents']),
    'partners'  => $t(['fr' => 'Universités partenaires',   'en' => 'Partner universities']),
    'technical' => $t(['fr' => 'Problème technique',        'en' => 'Technical issue']),
    'other'     => $t(['fr' => 'Autre',                     'en' => 'Other']),
];

// On récupère l'ID de la conversation ouverte dans l'URL pour gérer l'état "sélectionné"
$currentId = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>

<div class="outlook-shell">
    <div class="outlook-toolbar">
        <h1>📬 <?= $t(['fr' => 'Demandes des étudiants', 'en' => 'Student Requests']) ?></h1>

        <a href="index.php?page=messages-admin&filter=all&lang=<?= $lang ?>"
           class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
            <?= $t(['fr' => 'Toutes', 'en' => 'All']) ?> (<?= count($conversations) ?>)
        </a>
        <a href="index.php?page=messages-admin&filter=unread&lang=<?= $lang ?>"
           class="filter-btn <?= $filter === 'unread' ? 'active' : '' ?>">
            <?= $t(['fr' => 'Non lues', 'en' => 'Unread']) ?>
        </a>
    </div>

    <div class="outlook-panes">
        <div class="outlook-list-pane" id="msgList">
            <?php if (empty($conversations)): ?>
                <div class="no-messages">
                    <?= $t(['fr' => 'Aucune conversation.', 'en' => 'No conversations.']) ?>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <?php
                    $isUnread = $conv->hasUnreadMessagesFor('admin');
                    $isSelected = ($currentId === $conv->getId());
                    $subjectLabel = htmlspecialchars($subjects[$conv->getSubject()] ?? $conv->getSubject());
                    $lastMsg = $conv->getLastMessage();
                    $preview = $lastMsg ? htmlspecialchars(mb_substr($lastMsg->getContent(), 0, 80)) : '';
                    ?>
                    
                    <a href="index.php?page=messages-admin&action=view&id=<?= $conv->getId() ?>&lang=<?= $lang ?>" 
                       class="message-item <?= $isUnread ? 'unread' : '' ?> <?= $isSelected ? 'selected' : '' ?>"
                       style="text-decoration: none !important; outline: none !important;">
                        
                        <?php if ($isUnread): ?>
                            <div class="unread-dot"></div>
                        <?php endif; ?>

                        <div class="msg-item-top">
                            <span class="msg-item-name"><?= htmlspecialchars($conv->getName()) ?></span>
                            <span class="msg-item-date"><?= $conv->getCreatedAt()->format('d/m H:i') ?></span>
                        </div>
                        <div class="msg-item-subject"><?= $subjectLabel ?></div>
                        <div class="msg-item-preview"><?= $preview ?>…</div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="outlook-reading-pane" id="readingPane">
            <div class="reading-empty">
                <div class="reading-empty-icon">✉️</div>
                <span><?= $t(['fr' => 'Sélectionnez une conversation', 'en' => 'Select a conversation']) ?></span>
            </div>
        </div>
    </div>
</div>

<?php
$content    = ob_get_clean();
$title      = $t(['fr' => 'Messages', 'en' => 'Messages']);
$styles     = ['styles/messages_admin.css'];
$scripts    = []; 
$activeMenu = 'messages';
$userRole   = 'admin';

include __DIR__ . '/../Layout/base.php';
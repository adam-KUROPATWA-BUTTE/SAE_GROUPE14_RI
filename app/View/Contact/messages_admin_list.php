<?php
/**
 * Liste des messages admin — layout style Outlook
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array<int, \Model\Entity\ContactMessage> $messages
 * @var string $filter
 */

ob_start();

$subjects = [
    'mobility'  => $t(['fr' => 'Question sur ma mobilité',  'en' => 'Mobility question']),
    'documents' => $t(['fr' => 'Documents requis',          'en' => 'Required documents']),
    'partners'  => $t(['fr' => 'Universités partenaires',   'en' => 'Partner universities']),
    'technical' => $t(['fr' => 'Problème technique',        'en' => 'Technical issue']),
    'other'     => $t(['fr' => 'Autre',                     'en' => 'Other']),
];

// Serialize messages for JS consumption
$messagesJson = json_encode(array_map(function ($m) {
    return [
        'id'            => $m->getId(),
        'name'          => $m->getName(),
        'numEtu'        => $m->getStudentNumEtu(),
        'email'         => $m->getEmail(),
        'subject'       => $m->getSubject(),
        'message'       => $m->getMessage(),
        'createdAt'     => $m->getCreatedAt()->format('d/m/Y H:i'),
        'isRead'        => $m->isRead(),
        'adminResponse' => $m->getAdminResponse(),
        'respondedAt'   => $m->getAdminResponse() ? $m->getRespondedAt()->format('d/m/Y H:i') : null,
    ];
}, $messages), JSON_UNESCAPED_UNICODE);
?>

    <div class="outlook-shell">

        <div class="outlook-toolbar">
            <h1>📬 <?= $t(['fr' => 'Messages des étudiants', 'en' => 'Student Messages']) ?></h1>

            <a href="index.php?page=messages-admin&filter=all&lang=<?= $lang ?>"
               class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
                <?= $t(['fr' => 'Tous', 'en' => 'All']) ?> (<?= count($messages) ?>)
            </a>
            <a href="index.php?page=messages-admin&filter=unread&lang=<?= $lang ?>"
               class="filter-btn <?= $filter === 'unread' ? 'active' : '' ?>">
                <?= $t(['fr' => 'Non lus', 'en' => 'Unread']) ?>
            </a>
        </div>

        <div class="outlook-panes">

            <div class="outlook-list-pane" id="msgList">
                <?php if (empty($messages)): ?>
                    <div class="no-messages">
                        <?= $t(['fr' => 'Aucun message.', 'en' => 'No messages.']) ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <?php
                        $isUnread = !$message->isRead();
                        $subject  = htmlspecialchars($subjects[$message->getSubject()] ?? $message->getSubject());
                        $preview  = htmlspecialchars(mb_substr($message->getMessage(), 0, 80));
                        ?>
                        <div class="message-item <?= $isUnread ? 'unread' : '' ?>"
                             data-id="<?= $message->getId() ?>"
                             onclick="window.messagesAdmin.loadMessage(<?= $message->getId() ?>, this)">

                            <?php if ($isUnread): ?>
                                <div class="unread-dot"></div>
                            <?php endif; ?>

                            <div class="msg-item-top">
                                <span class="msg-item-name"><?= htmlspecialchars($message->getName()) ?></span>
                                <span class="msg-item-date"><?= $message->getCreatedAt()->format('d/m H:i') ?></span>
                            </div>
                            <div class="msg-item-subject"><?= $subject ?></div>
                            <div class="msg-item-preview"><?= $preview ?>…</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="outlook-reading-pane" id="readingPane">
                <div class="reading-empty" id="readingEmpty">
                    <div class="reading-empty-icon">✉️</div>
                    <span><?= $t(['fr' => 'Sélectionnez un message', 'en' => 'Select a message']) ?></span>
                </div>

                <div id="readingContent" style="display:none; flex-direction:column;">
                    <div class="reading-header" id="readingHeader"></div>
                    <div class="reading-body"   id="readingBody"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="admin"
         data-base="index.php?page=messages-admin"
         data-messages="<?= htmlspecialchars($messagesJson) ?>"
         style="display:none;">
    </div>

<?php
$content    = ob_get_clean();
$title      = $t(['fr' => 'Messages', 'en' => 'Messages']);
$styles     = ['styles/messages_admin.css'];
$scripts    = ['js/messages_admin.js'];
$activeMenu = 'messages';
$userRole   = 'admin';

include __DIR__ . '/../Layout/base.php';
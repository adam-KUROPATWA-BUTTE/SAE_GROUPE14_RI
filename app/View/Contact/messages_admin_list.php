<?php
/**
 * Liste des messages admin
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array<int, \Model\Entity\ContactMessage> $messages
 * @var string $filter
 */

ob_start();
?>

    <div class="messages-container">
        <div class="messages-header">
            <h1><?= $t(['fr' => 'Messages des étudiants', 'en' => 'Student Messages']) ?></h1>

            <div class="filter-buttons">
                <a href="index.php?page=messages-admin&filter=all&lang=<?= $lang ?>"
                   class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
                    <?= $t(['fr' => 'Tous', 'en' => 'All']) ?> (<?= count($messages) ?>)
                </a>
                <a href="index.php?page=messages-admin&filter=unread&lang=<?= $lang ?>"
                   class="filter-btn <?= $filter === 'unread' ? 'active' : '' ?>">
                    <?= $t(['fr' => 'Non lus', 'en' => 'Unread']) ?>
                </a>
            </div>
        </div>

        <?php if (empty($messages)): ?>
            <div class="no-messages">
                <p><?= $t(['fr' => 'Aucun message.', 'en' => 'No messages.']) ?></p>
            </div>
        <?php else: ?>
            <div class="messages-list">
                <?php foreach ($messages as $message): ?>
                    <div class="message-item <?= $message->isRead() ? '' : 'unread' ?>"
                         onclick="window.location.href='index.php?page=messages-admin&action=view&id=<?= $message->getId() ?>&lang=<?= $lang ?>'">

                        <div class="message-header-item">
                            <div class="message-from">
                                <strong><?= htmlspecialchars($message->getName()) ?></strong>
                                <span class="message-numetu"><?= htmlspecialchars($message->getStudentNumEtu()) ?></span>
                            </div>
                            <div class="message-date">
                                <?= $message->getCreatedAt()->format('d/m/Y H:i') ?>
                            </div>
                        </div>

                        <div class="message-subject">
                            <?php
                            $subjects = [
                                'mobility' => $t(['fr' => 'Question sur ma mobilité', 'en' => 'Mobility question']),
                                'documents' => $t(['fr' => 'Documents requis', 'en' => 'Required documents']),
                                'partners' => $t(['fr' => 'Universités partenaires', 'en' => 'Partner universities']),
                                'technical' => $t(['fr' => 'Problème technique', 'en' => 'Technical issue']),
                                'other' => $t(['fr' => 'Autre', 'en' => 'Other'])
                            ];
                            echo htmlspecialchars($subjects[$message->getSubject()] ?? $message->getSubject());
                            ?>
                        </div>

                        <div class="message-preview">
                            <?= htmlspecialchars(mb_substr($message->getMessage(), 0, 100)) ?>...
                        </div>

                        <?php if (!$message->isRead()): ?>
                            <span class="unread-badge">●</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="admin"
         style="display:none;">
    </div>

<?php
$content = ob_get_clean();

$title = $t(['fr' => 'Messages', 'en' => 'Messages']);
$styles = ['styles/messages_admin.css'];
$scripts = [];
$activeMenu = 'messages';
$userRole = 'admin';

include __DIR__ . '/../Layout/base.php';
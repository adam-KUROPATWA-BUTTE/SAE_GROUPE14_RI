<?php
/**
 * Page de contact pour les étudiants
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array{email: string, phone: string, address: array<string, string>, hours: array<string, string>} $contactInfo
 * @var bool $messageSent
 * @var string|null $error
 * @var array<int, \Model\Entity\ContactMessage> $studentMessages
 */

ob_start();

$subjects = [
    'mobility'  => $t(['fr' => 'Question sur ma mobilité',  'en' => 'Mobility question']),
    'documents' => $t(['fr' => 'Documents requis',          'en' => 'Required documents']),
    'partners'  => $t(['fr' => 'Universités partenaires',   'en' => 'Partner universities']),
    'technical' => $t(['fr' => 'Problème technique',        'en' => 'Technical issue']),
    'other'     => $t(['fr' => 'Autre',                     'en' => 'Other']),
];

$unreadCount = count(array_filter($studentMessages, fn($m) => !$m->isRead() && $m->getAdminResponse() !== null));
?>

    <div class="contact-container">
        <h1><?= $t(['fr' => 'Nous Contacter', 'en' => 'Contact Us']) ?></h1>

        <div class="contact-intro">
            <p><?= $t([
                    'fr' => 'Une question sur votre mobilité ? Notre équipe est là pour vous aider.',
                    'en' => 'A question about your mobility? Our team is here to help you.'
                ]) ?></p>

            <?php if (!empty($studentMessages)): ?>
                <button class="btn-my-messages" onclick="toggleMessages()">
                    📬 <?= $t(['fr' => 'Mes messages', 'en' => 'My messages']) ?>
                    <span class="msg-count-badge"><?= count($studentMessages) ?></span>

                    <span class="btn-arrow" id="msgArrow">▼</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- ── Historique des messages ── -->
        <?php if (!empty($studentMessages)): ?>
            <div class="contact-history" id="messagesPanel" style="display:none;">
                <div class="history-list">
                    <?php foreach ($studentMessages as $msg):
                        $hasResponse     = $msg->getAdminResponse() !== null;
                        $hasStudentReply = $msg->getStudentReply() !== null;
                        $subjectLabel    = $subjects[$msg->getSubject()] ?? $msg->getSubject();
                        ?>
                        <div class="history-card <?= $hasResponse ? 'history-card--answered' : 'history-card--pending' ?>">

                            <!-- Header -->
                            <div class="history-card-header">
                                <div class="history-meta">
                                    <span class="history-subject"><?= htmlspecialchars($subjectLabel) ?></span>
                                    <span class="history-date"><?= $msg->getCreatedAt()->format('d/m/Y H:i') ?></span>
                                </div>
                                <span class="history-status <?= $hasResponse ? 'history-status--answered' : 'history-status--pending' ?>">
                                <?= $hasResponse
                                    ? '✓ ' . $t(['fr' => 'Répondu', 'en' => 'Answered'])
                                    : '⏳ ' . $t(['fr' => 'En attente', 'en' => 'Pending']) ?>
                            </span>
                            </div>

                            <!-- Message étudiant -->
                            <div class="history-message-block">
                                <div class="history-label"><?= $t(['fr' => 'Votre message', 'en' => 'Your message']) ?></div>
                                <div class="history-text"><?= nl2br(htmlspecialchars($msg->getMessage())) ?></div>
                            </div>

                            <?php if ($hasResponse): ?>

                                <!-- Réponse admin -->
                                <div class="history-response-block">
                                    <div class="history-label history-label--response">
                                        <?= $t(['fr' => 'Réponse du service', 'en' => 'Service response']) ?>
                                        <span class="history-response-date">— <?= $msg->getRespondedAt()?->format('d/m/Y H:i') ?></span>
                                    </div>
                                    <div class="history-response-text">
                                        <?= nl2br(htmlspecialchars((string) $msg->getAdminResponse())) ?>                                    </div>
                                </div>

                                <?php if ($hasStudentReply): ?>

                                    <!-- Réponse étudiant déjà envoyée -->
                                    <div class="history-student-reply-block">
                                        <div class="history-label history-label--student-reply">
                                            <?= $t(['fr' => 'Votre réponse', 'en' => 'Your reply']) ?>
                                            <span class="history-response-date">— <?= $msg->getStudentRepliedAt()?->format('d/m/Y H:i') ?></span>
                                        </div>
                                        <div class="history-student-reply-text">
                                            <?= nl2br(htmlspecialchars((string) $msg->getStudentReply())) ?>                                        </div>
                                    </div>

                                <?php else: ?>

                                    <!-- Formulaire réponse étudiant -->
                                    <div class="history-reply-form">
                                        <button type="button"
                                                class="btn-toggle-reply"
                                                onclick="toggleReplyForm(<?= $msg->getId() ?>)">
                                            ↩ <?= $t(['fr' => 'Répondre', 'en' => 'Reply']) ?>
                                        </button>

                                        <div class="reply-form-inner" id="replyForm<?= $msg->getId() ?>" style="display:none;">
                                            <form method="POST"
                                                  action="index.php?page=contact-student&action=reply&id=<?= $msg->getId() ?>&lang=<?= $lang ?>">
                                            <textarea
                                                    name="student_reply"
                                                    rows="4"
                                                    required
                                                    placeholder="<?= $t(['fr' => 'Votre réponse...', 'en' => 'Your reply...']) ?>"
                                            ></textarea>
                                                <div class="reply-form-actions">
                                                    <button type="submit" class="btn-send-reply">
                                                        <?= $t(['fr' => 'Envoyer', 'en' => 'Send']) ?>
                                                    </button>
                                                    <button type="button"
                                                            class="btn-cancel-reply"
                                                            onclick="toggleReplyForm(<?= $msg->getId() ?>)">
                                                        <?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                <?php endif; ?>

                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="contact-content">

            <!-- ── Formulaire nouveau message ── -->
            <div class="contact-form-section">
                <h2><?= $t(['fr' => 'Envoyez-nous un message', 'en' => 'Send us a message']) ?></h2>

                <?php if ($messageSent): ?>
                    <div class="success-message">
                        ✓ <?= $t([
                            'fr' => 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.',
                            'en' => 'Your message has been sent successfully! We will reply to you as soon as possible.'
                        ]) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message">✗ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name"><?= $t(['fr' => 'Nom complet', 'en' => 'Full name']) ?> *</label>
                        <input type="text" id="name" name="name" required
                               placeholder="<?= $t(['fr' => 'Votre nom', 'en' => 'Your name']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><?= $t(['fr' => 'Email', 'en' => 'Email']) ?> *</label>
                        <input type="email" id="email" name="email" required placeholder="votre.email@etu.univ-amu.fr">
                    </div>
                    <div class="form-group">
                        <label for="subject"><?= $t(['fr' => 'Sujet', 'en' => 'Subject']) ?> *</label>
                        <select id="subject" name="subject" required>
                            <option value=""><?= $t(['fr' => 'Sélectionnez un sujet', 'en' => 'Select a subject']) ?></option>
                            <option value="mobility"><?= $t(['fr' => 'Question sur ma mobilité', 'en' => 'Question about my mobility']) ?></option>
                            <option value="documents"><?= $t(['fr' => 'Documents requis', 'en' => 'Required documents']) ?></option>
                            <option value="partners"><?= $t(['fr' => 'Universités partenaires', 'en' => 'Partner universities']) ?></option>
                            <option value="technical"><?= $t(['fr' => 'Problème technique', 'en' => 'Technical issue']) ?></option>
                            <option value="other"><?= $t(['fr' => 'Autre', 'en' => 'Other']) ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message"><?= $t(['fr' => 'Message', 'en' => 'Message']) ?> *</label>
                        <textarea id="message" name="message" rows="6" required
                                  placeholder="<?= $t(['fr' => 'Votre message...', 'en' => 'Your message...']) ?>"></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><?= $t(['fr' => 'Envoyer', 'en' => 'Send']) ?></button>
                </form>
            </div>

            <!-- ── Coordonnées ── -->
            <div class="contact-info-section">
                <h2><?= $t(['fr' => 'Nos coordonnées', 'en' => 'Our contact information']) ?></h2>
                <div class="contact-info-item">
                    <div class="contact-icon">📧</div>
                    <div class="contact-details">
                        <h3><?= $t(['fr' => 'Email', 'en' => 'Email']) ?></h3>
                        <a href="mailto:<?= htmlspecialchars($contactInfo['email']) ?>"><?= htmlspecialchars($contactInfo['email']) ?></a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-details">
                        <h3><?= $t(['fr' => 'Téléphone', 'en' => 'Phone']) ?></h3>
                        <a href="tel:<?= htmlspecialchars($contactInfo['phone']) ?>"><?= htmlspecialchars($contactInfo['phone']) ?></a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-details">
                        <h3><?= $t(['fr' => 'Adresse', 'en' => 'Address']) ?></h3>
                        <p><?= $contactInfo['address'][$lang] ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon">🕒</div>
                    <div class="contact-details">
                        <h3><?= $t(['fr' => 'Horaires', 'en' => 'Opening hours']) ?></h3>
                        <p><?= htmlspecialchars($contactInfo['hours'][$lang]) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="app-config" data-lang="<?= htmlspecialchars($lang) ?>" data-role="student" style="display:none;"></div>



<?php
$content    = ob_get_clean();
$title      = $t(['fr' => 'Contact - Relations Internationales', 'en' => 'Contact - International Relations']);
$styles     = ['styles/contact.css'];
$scripts    = ['js/contact_student.js'];
$activeMenu = 'contact';
$userRole   = 'student';

include __DIR__ . '/../Layout/base.php';
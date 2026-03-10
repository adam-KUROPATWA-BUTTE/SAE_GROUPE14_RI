<?php
/**
 * Page de contact pour les étudiants
 * @var callable $t
 * @var string $lang
 * @var array<int, \Model\Entity\Conversation> $studentConversations
 * @var bool $messageSent
 * @var string $error
 * @var array{email: string, phone: string, address: array<string, string>, hours: array<string, string>} $contactInfo
 */

ob_start();

$subjects = [
    'mobility'  => $t(['fr' => 'Question sur ma mobilité',  'en' => 'Mobility question']),
    'documents' => $t(['fr' => 'Documents requis',          'en' => 'Required documents']),
    'partners'  => $t(['fr' => 'Universités partenaires',   'en' => 'Partner universities']),
    'technical' => $t(['fr' => 'Problème technique',        'en' => 'Technical issue']),
    'other'     => $t(['fr' => 'Autre',                     'en' => 'Other']),
];
?>

<div class="contact-container">
    <h1><?= $t(['fr' => 'Nous Contacter', 'en' => 'Contact Us']) ?></h1>

    <div class="contact-intro">
        <p><?= $t([
            'fr' => 'Une question sur votre mobilité ? Notre équipe est là pour vous aider.',
            'en' => 'A question about your mobility? Our team is here to help you.'
        ]) ?></p>

        <?php if (!empty($studentConversations)): ?>
            <button class="btn-my-messages" onclick="toggleMessages()">
                📬 <?= $t(['fr' => 'Mes demandes en cours', 'en' => 'My current requests']) ?>
                <span class="msg-count-badge"><?= count($studentConversations) ?></span>
                <span class="btn-arrow" id="msgArrow">▼</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($studentConversations)): ?>
        <div class="contact-history" id="messagesPanel" style="display:none;">
            <div class="history-list">
                <?php foreach ($studentConversations as $conv): 
                    $subjectLabel = $subjects[$conv->getSubject()] ?? $conv->getSubject();
                ?>
                    <div class="history-card">
                        
                        <div class="history-card-header">
                            <h3><?= htmlspecialchars($subjectLabel) ?></h3>
                            <small class="history-date">Ticket créé le <?= $conv->getCreatedAt()->format('d/m/Y H:i') ?></small>
                        </div>

                        <div class="chat-thread">
                            <?php foreach ($conv->getMessages() as $msg): ?>
                                <?php $isStudent = $msg->getSenderType() === 'student'; ?>
                                <div class="chat-message <?= $isStudent ? 'chat-message--student' : 'chat-message--admin' ?>">
                                    <div class="chat-message-meta">
                                        <strong><?= $isStudent ? $t(['fr' => 'Vous', 'en' => 'You']) : $t(['fr' => 'Service RI', 'en' => 'IR Office']) ?></strong> 
                                        — <?= $msg->getCreatedAt()->format('d/m/Y H:i') ?>
                                    </div>
                                    <div class="chat-message-content">
                                        <?= nl2br(htmlspecialchars($msg->getContent())) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="history-reply-form">
                            <form method="POST" action="index.php?page=contact-student&action=reply&id=<?= $conv->getId() ?>&lang=<?= $lang ?>">
                                <textarea
                                    name="student_reply"
                                    rows="2"
                                    required
                                    class="reply-textarea"
                                    placeholder="<?= $t(['fr' => 'Ajouter une réponse à ce ticket...', 'en' => 'Add a reply to this ticket...']) ?>"
                                ></textarea>
                                <button type="submit" class="btn-submit reply-submit">
                                    <?= $t(['fr' => 'Répondre', 'en' => 'Reply']) ?>
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="contact-content">

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

<div id="app-config"
     data-lang="<?= htmlspecialchars($lang) ?>"
     data-role="admin"
     style="display:none;">
</div>
<?php
$content    = ob_get_clean();
$title      = $t(['fr' => 'Contact - Relations Internationales', 'en' => 'Contact - International Relations']);
$styles     = ['styles/contact.css'];
$scripts    = ['js/contact_student.js'];
$activeMenu = 'contact';
$userRole   = 'student';

include __DIR__ . '/../Layout/base.php';
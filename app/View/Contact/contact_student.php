<?php
/**
 * Page de contact pour les étudiants
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array{email: string, phone: string, address: array, hours: array} $contactInfo
 * @var bool $messageSent
 * @var string|null $error
 */

/** @var array{email: string, phone: string, address: array<string, string>, hours: array<string, string>} $contactInfo**/
ob_start();
?>

    <div class="contact-container">
        <h1><?= $t(['fr' => 'Nous Contacter', 'en' => 'Contact Us']) ?></h1>

        <div class="contact-intro">
            <p><?= $t([
                    'fr' => 'Une question sur votre mobilité ? Notre équipe est là pour vous aider.',
                    'en' => 'A question about your mobility? Our team is here to help you.'
                ]) ?></p>
        </div>

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
                    <div class="error-message">
                        ✗ <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">
                            <?= $t(['fr' => 'Nom complet', 'en' => 'Full name']) ?> *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            placeholder="<?= $t(['fr' => 'Votre nom', 'en' => 'Your name']) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <?= $t(['fr' => 'Email', 'en' => 'Email']) ?> *
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            placeholder="<?= $t(['fr' => 'votre.email@etu.univ-amu.fr', 'en' => 'your.email@etu.univ-amu.fr']) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="subject">
                            <?= $t(['fr' => 'Sujet', 'en' => 'Subject']) ?> *
                        </label>
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
                        <label for="message">
                            <?= $t(['fr' => 'Message', 'en' => 'Message']) ?> *
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            required
                            placeholder="<?= $t(['fr' => 'Votre message...', 'en' => 'Your message...']) ?>"
                        ></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <?= $t(['fr' => 'Envoyer', 'en' => 'Send']) ?>
                    </button>
                </form>
            </div>

            <div class="contact-info-section">
                <h2><?= $t(['fr' => 'Nos coordonnées', 'en' => 'Our contact information']) ?></h2>

                <div class="contact-info-item">
                    <div class="contact-icon">📧</div>
                    <div class="contact-details">
                        <h3><?= $t(['fr' => 'Email', 'en' => 'Email']) ?></h3>
                        <a href="mailto:<?= htmlspecialchars($contactInfo['email']) ?>">
                            <?= htmlspecialchars($contactInfo['email']) ?>
                        </a>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-details">
                        <h3><?= $t(['fr' => 'Téléphone', 'en' => 'Phone']) ?></h3>
                        <a href="tel:<?= htmlspecialchars($contactInfo['phone']) ?>">
                            <?= htmlspecialchars($contactInfo['phone']) ?>
                        </a>
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
         data-role="student"
         style="display:none;">
    </div>

<?php
$content = ob_get_clean();

$title = $t(['fr' => 'Contact - Relations Internationales', 'en' => 'Contact - International Relations']);
$styles = ['styles/contact.css'];
$scripts = [];
$activeMenu = 'contact';
$userRole = 'student';

include __DIR__ . '/../Layout/base.php';
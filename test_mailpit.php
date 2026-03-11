<?php
/**
 * test_mailpit.php — Script de test des emails vers Mailpit
 *
 * USAGE :
 *   php test_mailpit.php [template]
 *
 *   templates disponibles :
 *     all                  → tous les templates (défaut)
 *     relance
 *     folder_update
 *     validation_complete
 *     document_deposited
 *     document_validated
 *     message_notification
 *
 * PRÉREQUIS :
 *   1. Mailpit démarré :
 *        docker run -d -p 1025:1025 -p 8025:8025 axllent/mailpit
 *      ou avec le binaire :
 *        mailpit
 *
 *   2. PHPMailer installé :
 *        composer require phpmailer/phpmailer
 *
 *   3. Ce fichier doit être placé à la racine du projet
 *      (là où se trouvent vendor/ et app/).
 *
 *   Interface Mailpit : http://localhost:8025
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

// ── Pointez vers vos templates ────────────────────────────────────────────────
define('ROOT_PATH', __DIR__);

// ── Inclure le service adapté Mailpit ────────────────────────────────────────
require_once __DIR__ . '/EmailReminderService_mailpit.php';

use Service\Email\EmailReminderService;

// ── Adresse de test (interceptée par Mailpit, peu importe la valeur) ─────────
const TEST_EMAIL = 'etudiant.test@test.local';

$template = $argv[1] ?? 'all';

$tests = [

    'relance' => function (): void {
        echo "📧 [1/6] sendRelance… ";
        $ok = EmailReminderService::sendRelance(
            toEmail        : TEST_EMAIL,
            dossierId      : 42,
            studentName    : 'Marie Dupont',
            itemsToComplete: ['CV manquant', 'Lettre de motivation non déposée', 'Convention non signée']
        );
        echo $ok ? "✅ OK\n" : "❌ ERREUR\n";
    },

    'folder_update' => function (): void {
        echo "📧 [2/6] sendFolderUpdateNotification… ";
        $ok = EmailReminderService::sendFolderUpdateNotification(
            toEmail    : TEST_EMAIL,
            studentName: 'Marie Dupont',
            numEtu     : '21809999',
            updates    : [
                '__SECTION_ACCEPTEES__<li>CV</li><li>Photo</li>__END_SECTION__',
                '__SECTION_REFUSEES__<li>Convention de Stage (illisible)</li>__END_SECTION__',
                '__STATUT_GLOBAL__En instruction__END_STATUT__',
                '__DATE_LIMITE__15 avril 2025__END_DATE__',
            ]
        );
        echo $ok ? "✅ OK\n" : "❌ ERREUR\n";
    },

    'validation_complete' => function (): void {
        echo "📧 [3/6] sendValidationComplete… ";
        $ok = EmailReminderService::sendValidationComplete(
            toEmail            : TEST_EMAIL,
            studentName        : 'Marie Dupont',
            numEtu             : '21809999',
            validatedDocuments : ['CV', 'Photo', 'Convention de Stage', 'Lettre de Motivation']
        );
        echo $ok ? "✅ OK\n" : "❌ ERREUR\n";
    },

    'document_deposited' => function (): void {
        echo "📧 [4/6] sendDocumentDeposited… ";
        $ok = EmailReminderService::sendDocumentDeposited(
            toEmail     : TEST_EMAIL,
            studentName : 'Marie Dupont',
            documentType: 'convention',
            numEtu      : '21809999'
        );
        echo $ok ? "✅ OK\n" : "❌ ERREUR\n";
    },

    'document_validated' => function (): void {
        echo "📧 [5/6] sendDocumentValidated… ";
        $ok = EmailReminderService::sendDocumentValidated(
            toEmail     : TEST_EMAIL,
            studentName : 'Marie Dupont',
            documentType: 'cv',
            numEtu      : '21809999'
        );
        echo $ok ? "✅ OK\n" : "❌ ERREUR\n";
    },

    'message_notification' => function (): void {
        echo "📧 [6/6] sendMessageNotification… ";
        $ok = EmailReminderService::sendMessageNotification(
            toEmail      : TEST_EMAIL,
            recipientName: 'Marie Dupont',
            senderName   : 'Responsable RI',
            messagePreview: 'Bonjour Marie, votre dossier a bien été reçu. Merci de compléter la convention de stage dès que possible.',
            platformLink : 'https://ri-amu.app/'
        );
        echo $ok ? "✅ OK\n" : "❌ ERREUR\n";
    },

];

echo "\n🚀 Test des emails — Mailpit (http://localhost:8025)\n";
echo str_repeat('─', 50) . "\n";

if ($template === 'all') {
    foreach ($tests as $fn) {
        $fn();
    }
} elseif (isset($tests[$template])) {
    $tests[$template]();
} else {
    echo "❌ Template inconnu : {$template}\n";
    echo "   Disponibles : " . implode(', ', array_keys($tests)) . ", all\n";
    exit(1);
}

echo str_repeat('─', 50) . "\n";
echo "✔ Terminé. Vérifiez vos emails sur http://localhost:8025\n\n";
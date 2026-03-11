<?php

namespace Service\Email;

/**
 * EmailReminderService — version Mailpit (tests locaux)
 *
 * Remplace le client Mailjet par un envoi SMTP direct via PHPMailer,
 * configuré pour intercepter les mails dans Mailpit (localhost:1025).
 *
 * PRÉREQUIS :
 *   composer require phpmailer/phpmailer tijsverkoyen/css-to-inline-styles
 *
 * LANCER MAILPIT :
 *   docker run -d -p 1025:1025 -p 8025:8025 axllent/mailpit
 *   → Interface web : http://localhost:8025
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class EmailReminderService
{
    private static string $fromEmail = 'relance-iut-amu@ri-amu.app';
    private static string $fromName  = 'IUT Aix - Gestion Dossiers';
    private static string $logoUrl   = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';

    /** Hôte SMTP Mailpit */
    private static string $smtpHost = 'localhost';
    /** Port SMTP Mailpit (pas de TLS) */
    private static int    $smtpPort = 1025;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Crée et retourne un PHPMailer configuré pour Mailpit.
     */
    private static function createMailer(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host       = self::$smtpHost;
        $mailer->Port       = self::$smtpPort;
        $mailer->SMTPAuth   = false;   // Mailpit n'exige pas d'auth
        $mailer->SMTPSecure = '';      // Pas de TLS/SSL en local
        $mailer->CharSet    = 'UTF-8';
        $mailer->setFrom(self::$fromEmail, self::$fromName);
        return $mailer;
    }

    /**
     * Render un template PHP en HTML.
     * @param array<string, mixed> $data
     */
    private static function renderEmailTemplate(string $template, array $data = []): string
    {
        extract($data);
        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 3);
        $file = $root . '/app/View/Email/' . $template . '.php';

        if (!file_exists($file)) {
            error_log("❌ Template introuvable : {$file}");
            return '';
        }

        ob_start();
        require $file;
        $html = ob_get_clean() ?: '';

        // Injection du CSS inline
        $cssFile = $root . '/public/styles/emails.css';
        if (file_exists($cssFile)) {
            $html = (new CssToInlineStyles())->convert($html, file_get_contents($cssFile) ?: '');
        } else {
            error_log("⚠️ emails.css introuvable : {$cssFile}");
        }

        return $html;
    }

    /**
     * Envoi générique d'un email via Mailpit.
     */
    private static function send(string $toEmail, string $toName, string $subject, string $html): bool
    {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($toEmail, $toName);
            $mailer->Subject  = $subject;
            $mailer->isHTML(true);
            $mailer->Body     = $html;
            $mailer->AltBody  = strip_tags($html);
            $mailer->send();
            error_log("✅ Email envoyé à {$toEmail} — {$subject}");
            return true;
        } catch (MailerException $e) {
            error_log("❌ Erreur envoi email à {$toEmail} : " . $e->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Méthodes publiques (même signature que la version Mailjet)
    // -------------------------------------------------------------------------

    /** @param array<string> $itemsToComplete */
    public static function sendRelance(
        string $toEmail,
        int|string $dossierId,
        string $studentName = '',
        array $itemsToComplete = []
    ): bool {
        $html = self::renderEmailTemplate('relance', [
            'logoUrl'         => self::$logoUrl,
            'studentName'     => trim($studentName),
            'dossierId'       => $dossierId,
            'itemsToComplete' => $itemsToComplete,
            'folderLink'      => 'https://ri-amu.app/index.php?page=folders-student&action=view&id=' . urlencode((string) $dossierId),
        ]);
        return self::send($toEmail, $studentName, "Rappel : Dossier incomplet (ID {$dossierId})", $html);
    }

    /** @param array<int, string> $updates */
    public static function sendFolderUpdateNotification(
        string $toEmail,
        string $studentName,
        string $numEtu,
        array $updates
    ): bool {
        if (empty($updates)) {
            return true;
        }

        $sectionAcceptees = '';
        $sectionRefusees  = '';
        $statutGlobal     = '';
        $dateLimite       = '';
        $autresLignes     = [];

        foreach ($updates as $upd) {
            if (str_starts_with($upd, '__SECTION_ACCEPTEES__')) {
                $sectionAcceptees = str_replace(['__SECTION_ACCEPTEES__', '__END_SECTION__'], '', $upd);
            } elseif (str_starts_with($upd, '__SECTION_REFUSEES__')) {
                $sectionRefusees = str_replace(['__SECTION_REFUSEES__', '__END_SECTION__'], '', $upd);
            } elseif (str_starts_with($upd, '__STATUT_GLOBAL__')) {
                $statutGlobal = str_replace(['__STATUT_GLOBAL__', '__END_STATUT__'], '', $upd);
            } elseif (str_starts_with($upd, '__DATE_LIMITE__')) {
                $dateLimite = str_replace(['__DATE_LIMITE__', '__END_DATE__'], '', $upd);
            } else {
                $autresLignes[] = $upd;
            }
        }

        $html = self::renderEmailTemplate('folder_update', [
            'logoUrl'          => self::$logoUrl,
            'studentName'      => trim($studentName),
            'sectionAcceptees' => $sectionAcceptees,
            'sectionRefusees'  => $sectionRefusees,
            'statutGlobal'     => $statutGlobal,
            'dateLimite'       => $dateLimite,
            'autresLignes'     => $autresLignes,
        ]);
        return self::send($toEmail, $studentName, "Mise à jour de votre dossier RI (ID {$numEtu})", $html);
    }

    /** @param array<string> $validatedDocuments */
    public static function sendValidationComplete(
        string $toEmail,
        string $studentName,
        string $numEtu,
        array $validatedDocuments
    ): bool {
        $html = self::renderEmailTemplate('validation_complete', [
            'logoUrl'            => self::$logoUrl,
            'studentName'        => trim($studentName),
            'numEtu'             => $numEtu,
            'validatedDocuments' => $validatedDocuments,
            'folderLink'         => 'https://ri-amu.app/index.php?page=folders-student&action=view&numetu=' . urlencode($numEtu),
        ]);
        return self::send($toEmail, $studentName, "✓ Dossier validé — {$numEtu}", $html);
    }

    public static function sendDocumentDeposited(
        string $toEmail,
        string $studentName,
        string $documentType,
        string $numEtu
    ): bool {
        $labels   = [
            'photo'             => 'Photo',
            'cv'                => 'CV',
            'convention'        => 'Convention de Stage',
            'lettre_motivation' => 'Lettre de Motivation',
            'langues_file'      => 'Attestation de Langues',
        ];
        $docLabel = $labels[$documentType] ?? ucfirst($documentType);

        $html = self::renderEmailTemplate('document_deposited', [
            'logoUrl'       => self::$logoUrl,
            'studentName'   => trim($studentName),
            'documentLabel' => $docLabel,
            'folderLink'    => 'https://ri-amu.app/index.php?page=folders-student&action=view&numetu=' . urlencode($numEtu),
        ]);
        return self::send($toEmail, $studentName, "Document déposé - {$docLabel}", $html);
    }

    public static function sendDocumentValidated(
        string $toEmail,
        string $studentName,
        string $documentType,
        string $numEtu
    ): bool {
        $labels   = [
            'photo'             => 'Photo',
            'cv'                => 'CV',
            'convention'        => 'Convention de Stage',
            'lettre_motivation' => 'Lettre de Motivation',
            'langues_file'      => 'Attestation de Langues',
        ];
        $docLabel = $labels[$documentType] ?? ucfirst($documentType);

        $html = self::renderEmailTemplate('document_validated', [
            'logoUrl'       => self::$logoUrl,
            'studentName'   => trim($studentName),
            'documentLabel' => $docLabel,
            'folderLink'    => 'https://ri-amu.app/index.php?page=folders-student&action=view&numetu=' . urlencode($numEtu),
        ]);
        return self::send($toEmail, $studentName, "Document validé ✓ - {$docLabel}", $html);
    }

    public static function sendMessageNotification(
        string $toEmail,
        string $recipientName,
        string $senderName,
        string $messagePreview,
        string $platformLink
    ): bool {
        $preview = strlen($messagePreview) > 150 ? substr($messagePreview, 0, 150) : $messagePreview;

        $html = self::renderEmailTemplate('message_notification', [
            'recipientName'  => trim($recipientName),
            'senderName'     => trim($senderName),
            'messagePreview' => $preview,
            'platformLink'   => $platformLink,
        ]);
        return self::send($toEmail, $recipientName, "Nouveau message de {$senderName}", $html);
    }
}
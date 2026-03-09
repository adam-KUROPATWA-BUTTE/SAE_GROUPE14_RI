<?php

// phpcs:disable Generic.Files.LineLength

namespace Service\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailReminderService
{
    /**
     * Sender email address
     */
    private static string $fromEmail = 'no-reply@ri-amu.local';

    /**
     * Sender display name
     */
    private static string $fromName = 'IUT Aix - Gestion Dossiers';

    /**
     * Configure and return a PHPMailer instance.
     *
     * Comportement :
     *  - Si SMTP_HOST pointe sur 127.0.0.1 / localhost  →  Mailpit (pas d'auth, pas de TLS)
     *  - Sinon (Gmail, Mailtrap, prod…)                 →  STARTTLS + authentification
     */
    private static function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $host     = $_ENV['SMTP_HOST']   ?? 'smtp.gmail.com';
        $port     = (int)($_ENV['SMTP_PORT'] ?? 587);
        $user     = $_ENV['SMTP_USER']   ?? '';
        $pass     = $_ENV['SMTP_PASS']   ?? '';
        $secure   = $_ENV['SMTP_SECURE'] ?? 'tls'; // 'tls' | 'ssl' | '' pour Mailpit

        $mail->isSMTP();
        $mail->Host    = $host;
        $mail->Port    = $port;
        $mail->CharSet = 'UTF-8';

        $isLocal = in_array($host, ['127.0.0.1', 'localhost'], true);

        if ($isLocal) {
            // ── Mailpit / MailHog : pas d'auth, pas de chiffrement ──
            $mail->SMTPAuth   = false;
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        } else {
            // ── Serveur réel (Gmail, Mailtrap, prod) ────────────────
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = $secure === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
        }

        return $mail;
    }

    /**
     * Send an email reminder for incomplete folders
     * @param array<int, string> $itemsToComplete
     */
    public static function sendRelance(
        string $toEmail,
        int|string $dossierId,
        string $studentName = '',
        array $itemsToComplete = []
    ): bool {
        try {
            $mail = self::getMailer();

            $mail->setFrom(self::$fromEmail, self::$fromName);
            $mail->addAddress($toEmail, $studentName);

            $mail->isHTML(true);
            $mail->Subject = "Rappel : Dossier incomplet (ID {$dossierId})";
            $htmlMessage   = self::buildMessage($dossierId, $studentName, $itemsToComplete);
            $mail->Body    = $htmlMessage;
            $mail->AltBody = strip_tags($htmlMessage);

            $mail->send();
            error_log("✅ PHPMailer: Relance envoyée à {$toEmail}");
            return true;
        } catch (\Throwable $e) {
            error_log("❌ PHPMailer (Relance): {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send a notification when a folder is updated by administration
     * @param array<int, string> $updates
     */
    public static function sendFolderUpdateNotification(
        string $toEmail,
        string $studentName,
        string $dossierId,
        array $updates
    ): bool {
        if (empty($updates)) return true;

        try {
            $mail = self::getMailer();

            $mail->setFrom(self::$fromEmail, self::$fromName);
            $mail->addAddress($toEmail, $studentName);

            $mail->isHTML(true);
            $mail->Subject = "Mise à jour de votre dossier RI (ID {$dossierId})";
            $htmlMessage   = self::buildUpdateMessage($dossierId, $studentName, $updates);
            $mail->Body    = $htmlMessage;
            $mail->AltBody = strip_tags($htmlMessage);

            $mail->send();
            error_log("✅ PHPMailer: Notification envoyée à {$toEmail}");
            return true;
        } catch (\Throwable $e) {
            error_log("❌ PHPMailer (Notification): {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Build the HTML message for missing documents reminder
     * @param array<int, string> $itemsToComplete
     */
    private static function buildMessage(int|string $dossierId, string $studentName, array $itemsToComplete): string
    {
        $safeName  = htmlspecialchars(trim($studentName ?: ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $itemsHtml = '';

        if (!empty($itemsToComplete)) {
            $itemsHtml .= '<ul style="margin:0 0 16px 20px;">';
            foreach ($itemsToComplete as $it) {
                $itemsHtml .= '<li>' . htmlspecialchars((string)$it, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
            }
            $itemsHtml .= '</ul>';
        } else {
            $itemsHtml = '<p>Veuillez compléter les documents manquants dans votre dossier.</p>';
        }

        $logoUrl    = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';
        $encodedId  = urlencode((string)$dossierId);
        $link       = "https://ri-amu.app/index.php?page=folders-student&action=view&id={$encodedId}";

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Rappel dossier</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#1d7ac6;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">Rappel — Dossier incomplet</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Votre dossier n°<strong>{$dossierId}</strong> est actuellement <strong>incomplet</strong>.</p>
      {$itemsHtml}
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">Accéder à mon dossier</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">Pour toute question, contactez le service RI.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Email automatique • Service RI</p>
    </div>
  </div>
</body>
</html>";
    }

    /**
     * Build the HTML message for folder update notification
     * @param array<int, string> $updates
     */
    private static function buildUpdateMessage(string $dossierId, string $studentName, array $updates): string
    {
        $safeName = htmlspecialchars(trim($studentName ?: ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $logoUrl  = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';

        // ── Parse les sections structurées ────────────────────────────────────
        $sectionAcceptees = '';
        $sectionRefusees  = '';
        $statutGlobal     = '';
        $dateLimite       = '';
        $autresLignes     = [];

        foreach ($updates as $upd) {
            if (str_starts_with($upd, '__SECTION_ACCEPTEES__')) {
                $sectionAcceptees = (string) str_replace(['__SECTION_ACCEPTEES__', '__END_SECTION__'], '', $upd);
            } elseif (str_starts_with($upd, '__SECTION_REFUSEES__')) {
                $sectionRefusees = (string) str_replace(['__SECTION_REFUSEES__', '__END_SECTION__'], '', $upd);
            } elseif (str_starts_with($upd, '__STATUT_GLOBAL__')) {
                $statutGlobal = (string) str_replace(['__STATUT_GLOBAL__', '__END_STATUT__'], '', $upd);
            } elseif (str_starts_with($upd, '__DATE_LIMITE__')) {
                $dateLimite = (string) str_replace(['__DATE_LIMITE__', '__END_DATE__'], '', $upd);
            } else {
                $autresLignes[] = $upd;
            }
        }

        // ── Construit le bloc principal du mail ───────────────────────────────
        $updatesHtml = '';

        if (!empty($sectionAcceptees)) {
            $updatesHtml .= '
            <p style="margin:0 0 6px;font-weight:bold;color:#2e7d32;">✅ Documents validés :</p>
            <ul style="margin:0 0 16px 20px;line-height:1.8;color:#333;">'
                . $sectionAcceptees .
            '</ul>';
        }

        if (!empty($sectionRefusees)) {
            $updatesHtml .= '
            <p style="margin:0 0 6px;font-weight:bold;color:#c62828;">❌ Documents non validés :</p>
            <ul style="margin:0 0 16px 20px;line-height:1.8;color:#333;">'
                . $sectionRefusees .
            '</ul>';
        }

        foreach ($autresLignes as $ligne) {
            $updatesHtml .= '<p style="margin:4px 0;">' . $ligne . '</p>';
        }

        // ── Statut global ─────────────────────────────────────────────────────
        $statutColors = [
            'Dépôt'          => '#607d8b',
            'En instruction' => '#f57c00',
            'Accepté'        => '#2e7d32',
            'Refusé'         => '#c62828',
        ];
        $statutColor = $statutColors[$statutGlobal] ?? '#1d7ac6';
        $statutBlock = !empty($statutGlobal) ? '
            <div style="margin-top:20px;padding:12px 16px;border-radius:6px;background:#f5f5f5;border-left:4px solid ' . $statutColor . ';">
                <span style="font-weight:bold;color:#333;">Statut de votre dossier :</span>
                <span style="margin-left:8px;font-weight:bold;color:' . $statutColor . ';">' . htmlspecialchars($statutGlobal, ENT_QUOTES, 'UTF-8') . '</span>
            </div>' : '';

        // ── Date limite ───────────────────────────────────────────────────────
        $dateLimiteBlock = !empty($dateLimite) ? '
            <p style="margin-top:12px;color:#555;">📅 Date limite de remise des pièces : <b>' . htmlspecialchars($dateLimite, ENT_QUOTES, 'UTF-8') . '</b></p>' : '';

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Mise à jour dossier</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#1d7ac6;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">Mise à jour de votre dossier</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Votre dossier a été examiné par l'administration. Voici le récapitulatif :</p>
      <div style=\"background-color:#f9f9f9;border-radius:6px;padding:18px 20px;margin:16px 0;\">
        {$updatesHtml}
      </div>
      {$statutBlock}
      {$dateLimiteBlock}
      <div style=\"text-align:center;margin:28px 0 16px;\">
        <a href=\"https://ri-amu.app/\" style=\"background-color:#1d7ac6;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:bold;\">Consulter mon espace</a>
      </div>
      <p style=\"font-size:13px;color:#888;\">Pour toute question, contactez le service Relations Internationales.</p>
      <p style=\"color:#bbb;font-size:12px;margin:8px 0 0;\">Email automatique • Service RI AMU</p>
    </div>
  </div>
</body>
</html>";
    }
}
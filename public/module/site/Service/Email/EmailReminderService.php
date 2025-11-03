<?php
namespace Service\Email;

class EmailReminderService
{
    private static string $fromEmail = 'noreply@iut-aix.fr';
    private static string $fromName = 'IUT Aix - Gestion Dossiers';

    /**
     * Envoie une relance pour dossier incomplet.
     * $itemsToComplete : tableau optionnel des pièces manquantes.
     */
    public static function sendRelance(string $toEmail, int $dossierId, string $studentName = '', array $itemsToComplete = []): bool
    {
        $subject = "Relance : dossier incomplet (n°{$dossierId})";

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: ' . self::$fromName . ' <' . self::$fromEmail . '>';
        $headers[] = 'Reply-To: ' . self::$fromEmail;
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        $message = self::buildMessage($dossierId, $studentName, $itemsToComplete);

        $result = mail($toEmail, $subject, $message, implode("\r\n", $headers));

        if (!$result) {
            error_log("RelanceEmailService: échec envoi mail à {$toEmail} pour dossier {$dossierId}");
        }

        return $result;
    }

    private static function buildMessage(int $dossierId, string $studentName, array $itemsToComplete): string
    {
        $safeName = htmlspecialchars(trim($studentName ?: ''));
        $itemsHtml = '';

        if (!empty($itemsToComplete)) {
            $itemsHtml .= '<ul>';
            foreach ($itemsToComplete as $it) {
                $itemsHtml .= '<li>' . htmlspecialchars($it) . '</li>';
            }
            $itemsHtml .= '</ul>';
        } else {
            $itemsHtml = '<p>Merci de compléter les pièces manquantes de votre dossier afin que nous puissions poursuivre le traitement.</p>';
        }

        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&id={$dossierId}";

        return "
<!DOCTYPE html>
<html>
<head><meta charset='utf-8'></head>
<body style='font-family: Arial, sans-serif; color:#333;'>
  <div style='max-width:600px;margin:0 auto;padding:20px;'>
    <h2 style='color:#2c3e50;'>Relance : dossier incomplet</h2>
    <p>Bonjour {$safeName},</p>
    <p>Votre dossier n°{$dossierId} est actuellement incomplet.</p>
    {$itemsHtml}
    <p style='text-align:center;margin:20px 0;'>
      <a href='{$link}' style='background:#2c3e50;color:#fff;padding:10px 16px;text-decoration:none;border-radius:4px;'>Accéder à mon dossier</a>
    </p>
    <p style='color:#666;font-size:13px;'>Si vous avez déjà transmis les documents, veuillez ignorer ce message.</p>
    <hr style='border:none;border-top:1px solid #eee;'/>
    <p style='font-size:12px;color:#999;'>IUT Aix-en-Provence</p>
  </div>
</body>
</html>
        ";
    }
}
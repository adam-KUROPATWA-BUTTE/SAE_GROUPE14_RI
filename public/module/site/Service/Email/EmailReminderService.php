<?php
namespace Service\Email;

class EmailReminderService
{
    private static string $fromEmail = 'noreply@iut-aix.fr';
    private static string $fromName = 'IUT Aix - Gestion Dossiers';


    public static function sendRelance(string $toEmail, int|string $dossierId, string $studentName = '', array $itemsToComplete = []): bool
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
            error_log("EmailReminderService: échec envoi mail à {$toEmail} pour dossier {$dossierId}");
        }

        return $result;
    }

    /**
     * Build the HTML email body.
     *
     * @param int|string $dossierId
     * @param string $studentName
     * @param array $itemsToComplete
     * @return string
     */
    private static function buildMessage(int|string $dossierId, string $studentName, array $itemsToComplete): string
    {
        $safeName = htmlspecialchars(trim($studentName ?: ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $itemsHtml = '';

        if (!empty($itemsToComplete)) {
            $itemsHtml .= '<ul style="margin:0 0 16px 20px;">';
            foreach ($itemsToComplete as $it) {
                $itemsHtml .= '<li>' . htmlspecialchars((string)$it, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
            }
            $itemsHtml .= '</ul>';
        } else {
            $itemsHtml = '<p>Merci de compléter les pièces manquantes de votre dossier afin que nous puissions poursuivre le traitement.</p>';
        }

        // Logo AMU — remplacez par votre URL d'image hébergée si nécessaire
        $logoUrl = 'https://www.univ-amu.fr/sites/all/themes/amu/logo.png';

        // Si le dossierId contient des caractères non numériques (ex: NumEtu), on l'encode pour l'URL
        $encodedId = urlencode((string)$dossierId);
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&id={$encodedId}";

        // Bouton HTML
        $buttonHtml = '<div style="text-align:center;margin:18px 0;">
            <a href="' . htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" style="background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;">Accéder à mon dossier</a>
        </div>';

        // Footer
        $footer = '<p style="color:#666;font-size:13px;margin:12px 0 0;">Mail automatique • Service Relation International</p>
                   <p style="color:#999;font-size:12px;margin:8px 0 0;">Copyright © IUT Aix-en-Provence</p>';

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head>
  <meta charset=\"utf-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <title>Relance dossier</title>
</head>
<body style=\"font-family: Arial, Helvetica, sans-serif; background:#f6f6f6; margin:0; padding:20px;\">
  <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
    <tr>
      <td align=\"center\">
        <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" style=\"background:#ffffff;padding:20px;border-radius:4px;\">
          <tr>
            <td style=\"text-align:left\">
              <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:48px;display:block;margin-bottom:18px;\">
              <h2 style=\"margin:0 0 12px 0;\">Dossier Incomplet</h2>
              <p style=\"margin:0 0 12px 0;\">Bonjour {$safeName},</p>
              <p style=\"margin:0 0 12px 0;\">Votre dossier <strong>n°" . htmlspecialchars((string)$dossierId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</strong> est actuellement incomplet.</p>
              {$itemsHtml}
              {$buttonHtml}
              <h4 style=\"margin:18px 0 6px 0;\">Questions?</h4>
              <p style=\"margin:0 0 12px 0;\">Si vous avez des questions, contactez le support à <a href=\"mailto:support@iut-aix.fr\">support@iut-aix.fr</a>.</p>
              {$footer}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
        ";
    }
}
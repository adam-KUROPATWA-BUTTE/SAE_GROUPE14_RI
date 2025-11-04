<?php
namespace Service\Email;

use Mailjet\Client;
use Mailjet\Resources;

class EmailReminderService
{
    private static string $fromEmail = 'relance-iut@ri-amu.app';
    private static string $fromName = 'IUT Aix - Gestion Dossiers';

    public static function sendRelance(string $toEmail, int|string $dossierId, string $studentName = '', array $itemsToComplete = []): bool
    {
        try {
            // Initialiser le client Mailjet
            $mj = new Client(
                $_ENV['MAILJET_API_KEY'] ?? '',
                $_ENV['MAILJET_SECRET_KEY'] ?? '',
                true,
                ['version' => 'v3.1']
            );

            $subject = "Relance : dossier incomplet (n°{$dossierId})";
            $htmlMessage = self::buildMessage($dossierId, $studentName, $itemsToComplete);

            // Construire le payload Mailjet
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => self::$fromEmail,
                            'Name' => self::$fromName
                        ],
                        'To' => [
                            [
                                'Email' => $toEmail,
                                'Name' => $studentName ?: ''
                            ]
                        ],
                        'Subject' => $subject,
                        'HTMLPart' => $htmlMessage,
                        'TextPart' => strip_tags($htmlMessage)
                    ]
                ]
            ];

            // Envoyer via l'API Mailjet
            $response = $mj->post(Resources::$Email, ['body' => $body]);

            if ($response->success()) {
                error_log("✅ Email envoyé avec succès à {$toEmail} via Mailjet");
                return true;
            } else {
                error_log("❌ Erreur Mailjet: " . json_encode($response->getData()));
                return false;
            }

        } catch (\Exception $e) {
            error_log("❌ Exception Mailjet pour {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    private static function buildMessage(int|string $dossierId, string $studentName, array $itemsToComplete): string
    {
        $safeName = htmlspecialchars(trim($studentName ?: ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $itemsHtml = '';

        if (!empty($itemsToComplete)) {
            $itemsHtml .= '<ul style="margin:0 0 16px 20px;">';
            foreach ($itemsToComplete as $it) {
                $itemsHtml .= '<li>' . htmlspecialchars((string)$it, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
                $itemsHtml .= '<li>' . htmlspecialchars((string)$it, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
            }
            $itemsHtml .= '</ul>';
        } else {
            $itemsHtml = '<p>Merci de compléter les pièces manquantes de votre dossier.</p>';
        }

        $logoUrl = 'https://www.univ-amu.fr/sites/all/themes/amu/logo.png';
        $encodedId = urlencode((string)$dossierId);
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&id={$encodedId}";

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Relance dossier</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#1d7ac6;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">Relance - Dossier Incomplet</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Votre dossier n°<strong>{$dossierId}</strong> est actuellement <strong>incomplet</strong>.</p>
      {$itemsHtml}
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">Accéder à mon dossier</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">Pour toute question, contactez le service RI.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Mail automatique • Service RI</p>
    </div>
  </div>
</body>
</html>";
    }
}
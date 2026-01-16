<?php

// phpcs:disable Generic.Files.LineLength

namespace Service\Email;

use Mailjet\Client;
use Mailjet\Resources;

class EmailReminderService
{
    /**
     * Sender email address
     */
    private static string $fromEmail = 'relance-iut-amu@ri-amu.app';

    /**
     * Sender display name
     */
    private static string $fromName = 'IUT Aix - Gestion Dossiers';

    /**
     * @param array<string> $itemsToComplete List of missing items (strings)
     */
    public static function sendRelance(
        string $toEmail,
        int|string $dossierId,
        string $studentName = '',
        array $itemsToComplete = []
    ): bool {
        try {
            // Initialize Mailjet client
            $mj = new Client(
                $_ENV['MAILJET_API_KEY'] ?? '',
                $_ENV['MAILJET_SECRET_KEY'] ?? '',
                true,
                ['version' => 'v3.1']
            );

            $subject = "Reminder: Incomplete Folder (ID {$dossierId})";
            $htmlMessage = self::buildMessage($dossierId, $studentName, $itemsToComplete);

            // Build Mailjet payload
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

            // Send via Mailjet API
            $response = $mj->post(Resources::$Email, ['body' => $body]);

            if ($response->success()) {
                error_log("✅ Email successfully sent to {$toEmail} via Mailjet");
                return true;
            } else {
                error_log("❌ Mailjet error: " . json_encode($response->getData()));
                return false;
            }
        } catch (\Exception $e) {
            error_log("❌ Mailjet exception for {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array<string> $itemsToComplete List of missing items (strings)
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
            $itemsHtml = '<p>Please complete the missing documents in your folder.</p>';
        }

        $logoUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';
        $encodedId = urlencode((string)$dossierId);
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&id={$encodedId}";

        return "
<!DOCTYPE html>
<html lang=\"en\">
<head><meta charset=\"utf-8\"><title>Folder Reminder</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#1d7ac6;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">Reminder - Incomplete Folder</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Hello {$safeName},</p>
      <p>Your folder ID <strong>{$dossierId}</strong> is currently <strong>incomplete</strong>.</p>
      {$itemsHtml}
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">Access My Folder</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">For any questions, contact the RI service.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Automatic email • RI Service</p>
    </div>
  </div>
</body>
</html>";
    }
}

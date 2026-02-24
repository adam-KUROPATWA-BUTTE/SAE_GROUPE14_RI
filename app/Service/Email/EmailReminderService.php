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
            // Disable SSL verification for local dev only
            $mj->addRequestOption('verify', false);

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

    /**
     * Send validation confirmation email when documents are accepted
     *
     * @param string $toEmail Recipient email address
     * @param string $studentName Student's full name
     * @param array<string> $validatedDocuments List of validated document names
     * @param string $numEtu Student ID
     */
    public static function sendValidationConfirmation(
        string $toEmail,
        string $studentName,
        array $validatedDocuments,
        string $numEtu
    ): bool {
        try {
            // Initialize Mailjet client
            $mj = new Client(
                $_ENV['MAILJET_API_KEY'] ?? '',
                $_ENV['MAILJET_SECRET_KEY'] ?? '',
                true,
                ['version' => 'v3.1']
            );
            // Disable SSL verification for local dev only
            $mj->addRequestOption('verify', false);

            $subject = "Documents Validated - Folder #{$numEtu}";
            $htmlMessage = self::buildValidationMessage($studentName, $validatedDocuments, $numEtu);

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
                                'Name' => $studentName
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
                error_log("✅ Validation email successfully sent to {$toEmail}");
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
     * Send email confirmation when student deposits a document
     *
     * @param string $toEmail Student's email
     * @param string $studentName Student's name
     * @param string $documentType Type of document deposited
     * @param string $numEtu Student ID
     */
    public static function sendDocumentDeposited(
        string $toEmail,
        string $studentName,
        string $documentType,
        string $numEtu
    ): bool {
        try {
            // Initialize Mailjet client
            $mj = new Client(
                $_ENV['MAILJET_API_KEY'] ?? '',
                $_ENV['MAILJET_SECRET_KEY'] ?? '',
                true,
                ['version' => 'v3.1']
            );
            // Disable SSL verification for local dev only
            $mj->addRequestOption('verify', false);

            $documentLabels = [
                'photo' => 'Photo',
                'cv' => 'CV',
                'convention' => 'Convention de Stage',
                'lettre_motivation' => 'Lettre de Motivation'
            ];
            $docLabel = $documentLabels[$documentType] ?? ucfirst($documentType);

            $subject = "Document déposé - {$docLabel}";
            $htmlMessage = self::buildDepositMessage($studentName, $docLabel, $numEtu);

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
                                'Name' => $studentName
                            ]
                        ],
                        'Subject' => $subject,
                        'HTMLPart' => $htmlMessage,
                        'TextPart' => strip_tags($htmlMessage)
                    ]
                ]
            ];

            $response = $mj->post(Resources::$Email, ['body' => $body]);

            if ($response->success()) {
                error_log("✅ Document deposit email sent to {$toEmail} for {$docLabel}");
                return true;
            }

            error_log("❌ Mailjet error: " . json_encode($response->getData()));
            return false;

        } catch (\Exception $e) {
            error_log("❌ Mailjet exception for {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email confirmation when admin validates a specific document
     *
     * @param string $toEmail Student's email
     * @param string $studentName Student's name
     * @param string $documentType Type of document validated
     * @param string $numEtu Student ID
     */
    public static function sendDocumentValidated(
        string $toEmail,
        string $studentName,
        string $documentType,
        string $numEtu
    ): bool {
        try {
            // Initialize Mailjet client
            $mj = new Client(
                $_ENV['MAILJET_API_KEY'] ?? '',
                $_ENV['MAILJET_SECRET_KEY'] ?? '',
                true,
                ['version' => 'v3.1']
            );
            // Disable SSL verification for local dev only
            $mj->addRequestOption('verify', false);

            $documentLabels = [
                'photo' => 'Photo',
                'cv' => 'CV',
                'convention' => 'Convention de Stage',
                'lettre_motivation' => 'Lettre de Motivation'
            ];
            $docLabel = $documentLabels[$documentType] ?? ucfirst($documentType);

            $subject = "Document validé ✓ - {$docLabel}";
            $htmlMessage = self::buildSingleValidationMessage($studentName, $docLabel, $numEtu);

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
                                'Name' => $studentName
                            ]
                        ],
                        'Subject' => $subject,
                        'HTMLPart' => $htmlMessage,
                        'TextPart' => strip_tags($htmlMessage)
                    ]
                ]
            ];

            $response = $mj->post(Resources::$Email, ['body' => $body]);

            if ($response->success()) {
                error_log("✅ Document validation email sent to {$toEmail} for {$docLabel}");
                return true;
            }

            error_log("❌ Mailjet error: " . json_encode($response->getData()));
            return false;

        } catch (\Exception $e) {
            error_log("❌ Mailjet exception for {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build HTML message for document deposit confirmation
     */
    private static function buildDepositMessage(string $studentName, string $documentLabel, string $numEtu): string
    {
        $safeName = htmlspecialchars(trim($studentName), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDoc = htmlspecialchars($documentLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $logoUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&numetu=" . urlencode($numEtu);

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Document déposé</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#0056b3;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">📄 Document déposé</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Nous confirmons la réception de votre document :</p>
      <div style=\"background:#e3f2fd;padding:15px;border-left:4px solid #0056b3;margin:16px 0;\">
        <strong style=\"color:#0056b3;font-size:16px;\">📎 {$safeDoc}</strong>
      </div>
      <p>Votre document sera examiné par notre équipe. Vous recevrez une notification une fois validé.</p>
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">Voir mon dossier</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">Si vous avez des questions, contactez le service RI.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Notification automatique • Service RI - IUT Aix</p>
    </div>
  </div>
</body>
</html>";
    }

    /**
     * Build HTML message for single document validation
     */
    private static function buildSingleValidationMessage(string $studentName, string $documentLabel, string $numEtu): string
    {
        $safeName = htmlspecialchars(trim($studentName), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDoc = htmlspecialchars($documentLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $logoUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&numetu=" . urlencode($numEtu);

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Document validé</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#28a745;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">✓ Document validé</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Bonne nouvelle ! Votre document a été <strong style=\"color:#28a745;\">validé</strong> :</p>
      <div style=\"background:#d4edda;padding:15px;border-left:4px solid #28a745;margin:16px 0;\">
        <strong style=\"color:#28a745;font-size:16px;\">✓ {$safeDoc}</strong>
      </div>
      <p>Votre dossier progresse bien. N'oubliez pas de déposer les autres documents si nécessaire.</p>
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">Voir mon dossier</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">Si vous avez des questions, contactez le service RI.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Notification automatique • Service RI - IUT Aix</p>
    </div>
  </div>
</body>
</html>";
    }

    /**
     * Build HTML message for document validation confirmation
     *
     * @param string $studentName Student's full name
     * @param array<string> $validatedDocuments List of validated document names
     * @param string $numEtu Student ID
     */
    private static function buildValidationMessage(string $studentName, array $validatedDocuments, string $numEtu): string
    {
        $safeName = htmlspecialchars(trim($studentName), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Map technical names to user-friendly names
        $documentLabels = [
            'photo' => 'Photo',
            'cv' => 'CV',
            'convention' => 'Internship Agreement',
            'lettre_motivation' => 'Motivation Letter'
        ];

        $docsHtml = '<ul style="margin:0 0 16px 20px;">';
        foreach ($validatedDocuments as $doc) {
            $label = $documentLabels[$doc] ?? ucfirst($doc);
            $docsHtml .= '<li style="color:#28a745;"><strong>✓ ' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong></li>';
        }
        $docsHtml .= '</ul>';

        $logoUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';
        $encodedId = urlencode($numEtu);
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&numetu={$encodedId}";

        return "
<!DOCTYPE html>
<html lang=\"en\">
<head><meta charset=\"utf-8\"><title>Documents Validated</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#28a745;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">✓ Documents Validated</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Hello {$safeName},</p>
      <p>Good news! Your folder <strong>{$numEtu}</strong> has been reviewed and the following documents have been <strong style=\"color:#28a745;\">validated</strong>:</p>
      {$docsHtml}
      <p style=\"color:#28a745;font-weight:bold;\">Your folder is now complete and approved!</p>
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">View My Folder</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">If you have any questions, please contact the RI service.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Automatic notification • RI Service - IUT Aix</p>
    </div>
  </div>
</body>
</html>";
    }
}

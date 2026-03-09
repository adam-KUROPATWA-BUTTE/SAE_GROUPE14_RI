<?php

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

            $subject = "Rappel : Dossier incomplet (ID {$dossierId})";
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
            $itemsHtml = '<p>Veuillez compléter les documents manquants dans votre dossier.</p>';
        }

        $logoUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';
        $encodedId = urlencode((string)$dossierId);
        $link = "https://ri-amu.app/index.php?page=folders-student&action=view&id={$encodedId}";

        return "
<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Rappel dossier</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#1d7ac6;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">⚠️ Rappel — Dossier incomplet</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Votre dossier n°<strong>{$dossierId}</strong> est actuellement <strong>incomplet</strong>.</p>
      {$itemsHtml}
      <div style=\"text-align:center;margin:18px 0;\">
        <a href=\"{$link}\" style=\"background-color:#1d7ac6;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;\">Accéder à mon dossier</a>
      </div>
      <p style=\"font-size:14px;color:#666;\">Pour toute question, contactez le service RI.</p>
      <p style=\"color:#666;font-size:13px;margin:12px 0 0;\">Email automatique • Service RI - IUT Aix</p>
    </div>
  </div>
</body>
</html>";
    }

    /**
     * Send folder update notification email
     *
     * @param string $toEmail Student's email
     * @param string $studentName Student's name
     * @param string $numEtu Student ID
     * @param array<int, string> $updates List of updates to notify
     */
    public static function sendFolderUpdateNotification(
        string $toEmail,
        string $studentName,
        string $numEtu,
        array $updates
    ): bool {
        if (empty($updates)) {
            return true;
        }

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

            $subject = "Mise à jour de votre dossier RI (ID {$numEtu})";
            $htmlMessage = self::buildUpdateMessage($numEtu, $studentName, $updates);

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
                error_log("✅ Folder update notification sent to {$toEmail} via Mailjet");
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

            $subject = "Documents Validés - Dossier #{$numEtu}";
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
    <div style=\"background:#1d7ac6;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">📄 Document déposé</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Nous confirmons la réception de votre document :</p>
      <div style=\"background:#e3f2fd;padding:15px;border-left:4px solid #1d7ac6;margin:16px 0;\">
        <strong style=\"color:#1d7ac6;font-size:16px;\">📎 {$safeDoc}</strong>
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
            'convention' => 'Convention de Stage',
            'lettre_motivation' => 'Lettre de Motivation'
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
<html lang=\"fr\">
<head><meta charset=\"utf-8\"><title>Documents Validés</title></head>
<body style=\"font-family:Arial,sans-serif;background:#f6f6f6;margin:0;padding:20px;\">
  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);\">
    <div style=\"background:#28a745;padding:20px;text-align:center;\">
      <img src=\"{$logoUrl}\" alt=\"AMU\" style=\"height:50px;\">
      <h2 style=\"color:#fff;margin:10px 0 0;\">✓ Documents Validés</h2>
    </div>
    <div style=\"padding:30px;\">
      <p>Bonjour {$safeName},</p>
      <p>Bonne nouvelle ! Votre dossier <strong>{$numEtu}</strong> a été examiné et les documents suivants ont été <strong style=\"color:#28a745;\">validés</strong> :</p>
      {$docsHtml}
      <p style=\"color:#28a745;font-weight:bold;\">Votre dossier est maintenant complet et approuvé !</p>
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
     * Build HTML message for folder update notification
     * @param string $numEtu Student ID
     * @param string $studentName Student's name
     * @param array<int, string> $updates List of updates
     */
    private static function buildUpdateMessage(string $numEtu, string $studentName, array $updates): string
    {
        $safeName = htmlspecialchars(trim($studentName ?: ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $logoUrl  = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';

        // Parse structured sections
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

        // Build main content block
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

        // Global status section
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

        // Deadline section
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

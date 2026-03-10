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
     * Logo URL used in all email templates
     */
    private static string $logoUrl = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBl1mF7ktLaJxYCRD64rZyUJ1WcUDvcJBcIw&s';

    /**
     * Render an email template and return HTML string
     * @param string $template Template name (without .php)
     * @param array<string, mixed> $data Variables to pass to template
     * @return string Rendered HTML
     */
    private static function renderEmailTemplate(string $template, array $data = []): string
    {
        extract($data);
        // From app/Service/Email, go up 3 levels to project root
        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 3);
        $file = $root . '/app/View/Email/' . $template . '.php';

        if (!file_exists($file)) {
            error_log("❌ Email template not found: {$file}");
            return '';
        }

        ob_start();
        require $file;
        return ob_get_clean() ?: '';
    }

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
            // Disable SSL verification and increase timeout for local dev
            $mj->addRequestOption('verify', false);
            $mj->addRequestOption('timeout', 10);
            $mj->addRequestOption('connect_timeout', 10);

            $subject = "Rappel : Dossier incomplet (ID {$dossierId})";
            
            // Render email template
            $htmlMessage = self::renderEmailTemplate('relance', [
                'logoUrl' => self::$logoUrl,
                'studentName' => trim($studentName ?: ''),
                'dossierId' => $dossierId,
                'itemsToComplete' => $itemsToComplete,
                'folderLink' => "https://ri-amu.app/index.php?page=folders-student&action=view&id=" . urlencode((string)$dossierId)
            ]);

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
            // Disable SSL verification and increase timeout for local dev
            $mj->addRequestOption('verify', false);
            $mj->addRequestOption('timeout', 10);
            $mj->addRequestOption('connect_timeout', 10);

            $subject = "Mise à jour de votre dossier RI (ID {$numEtu})";
            
            // Parse structured sections from updates
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
            
            $htmlMessage = self::renderEmailTemplate('folder_update', [
                'logoUrl' => self::$logoUrl,
                'studentName' => trim($studentName),
                'sectionAcceptees' => $sectionAcceptees,
                'sectionRefusees' => $sectionRefusees,
                'statutGlobal' => $statutGlobal,
                'dateLimite' => $dateLimite,
                'autresLignes' => $autresLignes
            ]);

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
            // Disable SSL verification and increase timeout for local dev
            $mj->addRequestOption('verify', false);
            $mj->addRequestOption('timeout', 10);
            $mj->addRequestOption('connect_timeout', 10);

            $subject = "Documents Validés - Dossier #{$numEtu}";
            
            // Map technical names to user-friendly names
            $documentLabels = [
                'photo' => 'Photo',
                'cv' => 'CV',
                'convention' => 'Convention de Stage',
                'lettre_motivation' => 'Lettre de Motivation',
                'langues_file' => 'Attestation de Langues'
            ];
            $mappedDocs = array_map(fn($doc) => $documentLabels[$doc] ?? ucfirst($doc), $validatedDocuments);
            
            $htmlMessage = self::renderEmailTemplate('validation_complete', [
                'logoUrl' => self::$logoUrl,
                'studentName' => trim($studentName),
                'validatedDocuments' => $mappedDocs,
                'numEtu' => $numEtu,
                'folderLink' => "https://ri-amu.app/index.php?page=folders-student&action=view&numetu=" . urlencode($numEtu)
            ]);

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
            // Disable SSL verification and increase timeout for local dev
            $mj->addRequestOption('verify', false);
            $mj->addRequestOption('timeout', 10);
            $mj->addRequestOption('connect_timeout', 10);

            $documentLabels = [
                'photo' => 'Photo',
                'cv' => 'CV',
                'convention' => 'Convention de Stage',
                'lettre_motivation' => 'Lettre de Motivation',
                'langues_file' => 'Attestation de Langues'
            ];
            $docLabel = $documentLabels[$documentType] ?? ucfirst($documentType);

            $subject = "Document déposé - {$docLabel}";
            $htmlMessage = self::renderEmailTemplate('document_deposited', [
                'logoUrl' => self::$logoUrl,
                'studentName' => trim($studentName),
                'documentLabel' => $docLabel,
                'folderLink' => "https://ri-amu.app/index.php?page=folders-student&action=view&numetu=" . urlencode($numEtu)
            ]);

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
            // Disable SSL verification and increase timeout for local dev
            $mj->addRequestOption('verify', false);
            $mj->addRequestOption('timeout', 10);
            $mj->addRequestOption('connect_timeout', 10);

            $documentLabels = [
                'photo' => 'Photo',
                'cv' => 'CV',
                'convention' => 'Convention de Stage',
                'lettre_motivation' => 'Lettre de Motivation',
                'langues_file' => 'Attestation de Langues'
            ];
            $docLabel = $documentLabels[$documentType] ?? ucfirst($documentType);

            $subject = "Document validé ✓ - {$docLabel}";
            $htmlMessage = self::renderEmailTemplate('document_validated', [
                'logoUrl' => self::$logoUrl,
                'studentName' => trim($studentName),
                'documentLabel' => $docLabel,
                'folderLink' => "https://ri-amu.app/index.php?page=folders-student&action=view&numetu=" . urlencode($numEtu)
            ]);

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
     * Send notification when a new message is received
     */
    public static function sendMessageNotification(
        string $toEmail,
        string $recipientName,
        string $senderName,
        string $messagePreview,
        string $platformLink
    ): bool {
        try {
            $mj = new Client(
                $_ENV['MAILJET_API_KEY'] ?? '',
                $_ENV['MAILJET_SECRET_KEY'] ?? '',
                true,
                ['version' => 'v3.1']
            );
            // Disable SSL verification and increase timeout for local dev
            $mj->addRequestOption('verify', false);
            $mj->addRequestOption('timeout', 10);
            $mj->addRequestOption('connect_timeout', 10);

            $subject = "Nouveau message de {$senderName}";
            
            // Truncate message preview to 150 characters
            $truncatedPreview = strlen($messagePreview) > 150 
                ? substr($messagePreview, 0, 150) 
                : $messagePreview;
            
            $htmlMessage = self::renderEmailTemplate('message_notification', [
                'recipientName' => trim($recipientName),
                'senderName' => trim($senderName),
                'messagePreview' => $truncatedPreview,
                'platformLink' => $platformLink
            ]);

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
                                'Name' => $recipientName
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
                error_log("✅ Message notification email sent to {$toEmail} from {$senderName}");
                return true;
            }

            error_log("❌ Mailjet error: " . json_encode($response->getData()));
            return false;

        } catch (\Exception $e) {
            error_log("❌ Mailjet exception for {$toEmail}: " . $e->getMessage());
            return false;
        }
    }
}

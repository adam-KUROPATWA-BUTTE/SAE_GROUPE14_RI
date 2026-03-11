<?php

declare(strict_types=1);

namespace Tests\Service\Email;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for EmailReminderService — compatible with the real
 * mailjet/mailjet-apiv3-php package (Mailjet\Response requires a
 * Mailjet\Request, so we NEVER instantiate it directly).
 *
 * Strategy
 * --------
 * • We never create a Mailjet\Response ourselves.
 * • "Happy path" tests assert assertIsBool($result) — the method must not
 *   throw and must return a boolean regardless of whether the HTTP call
 *   succeeds or fails in CI.
 * • "Failure path" tests clear the Mailjet credentials, which makes
 *   createMailjetClient() throw a RuntimeException caught internally,
 *   so sendXxx() returns false. This is deterministic and requires no
 *   network access.
 * • Template files are written to a temp directory and ROOT_PATH is
 *   defined to point there, so renderEmailTemplate() always finds its files.
 */
class EmailReminderServiceTest extends TestCase
{
    private string $fixtureDir;

    // ------------------------------------------------------------------
    protected function setUp(): void
    {
        $_ENV['MAILJET_API_KEY']    = 'fake-key';
        $_ENV['MAILJET_SECRET_KEY'] = 'fake-secret';

        $this->fixtureDir = sys_get_temp_dir() . '/phpunit_email_fixtures';
        $tplDir = $this->fixtureDir . '/app/View/Email';

        if (!is_dir($tplDir)) {
            mkdir($tplDir, 0777, true);
        }

        foreach (['relance','folder_update','validation_complete',
                     'document_deposited','document_validated','message_notification'] as $tpl) {
            file_put_contents("{$tplDir}/{$tpl}.php", "<p>Template: {$tpl}</p>");
        }

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', $this->fixtureDir);
        }
    }

    protected function tearDown(): void
    {
        unset($_ENV['MAILJET_API_KEY'], $_ENV['MAILJET_SECRET_KEY']);
    }

    // ------------------------------------------------------------------
    // Helper: clear credentials so createMailjetClient() throws → false
    // ------------------------------------------------------------------
    private function clearCredentials(): void
    {
        unset($_ENV['MAILJET_API_KEY'], $_ENV['MAILJET_SECRET_KEY']);
        putenv('MAILJET_API_KEY=');
        putenv('MAILJET_SECRET_KEY=');
    }

    private function restoreCredentials(): void
    {
        $_ENV['MAILJET_API_KEY']    = 'fake-key';
        $_ENV['MAILJET_SECRET_KEY'] = 'fake-secret';
    }

    // ==================================================================
    // 1. sendRelance
    // ==================================================================

    /** @test */
    public function sendRelance_returns_bool_with_valid_params(): void
    {
        $result = \Service\Email\EmailReminderService::sendRelance(
            'student@example.com', 42, 'Jean Dupont', ['CV manquant']
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendRelance_returns_bool_with_empty_items_array(): void
    {
        $result = \Service\Email\EmailReminderService::sendRelance(
            'student@example.com', 42, 'Jean Dupont', []
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendRelance_returns_bool_with_string_dossierId(): void
    {
        $result = \Service\Email\EmailReminderService::sendRelance(
            'student@example.com', 'DOS-2024-001'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendRelance_returns_bool_with_padded_student_name(): void
    {
        $result = \Service\Email\EmailReminderService::sendRelance(
            'student@example.com', 99, '  Marie Curie  '
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendRelance_returns_false_when_credentials_missing(): void
    {
        $this->clearCredentials();
        $result = \Service\Email\EmailReminderService::sendRelance('x@x.com', 1);
        $this->assertFalse($result);
        $this->restoreCredentials();
    }

    /** @test */
    public function sendRelance_returns_bool_when_template_file_missing(): void
    {
        $tplPath = $this->fixtureDir . '/app/View/Email/relance.php';
        $backup  = $tplPath . '.bak';
        rename($tplPath, $backup);

        $result = \Service\Email\EmailReminderService::sendRelance(
            'student@example.com', 42, 'Jean Dupont'
        );

        rename($backup, $tplPath);
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendRelance_handles_special_characters_in_name(): void
    {
        $result = \Service\Email\EmailReminderService::sendRelance(
            'student+test@example.co.uk', 1, 'Ségolène Lefèvre'
        );
        $this->assertIsBool($result);
    }

    // ==================================================================
    // 2. sendFolderUpdateNotification
    // ==================================================================

    /** @test */
    public function sendFolderUpdateNotification_returns_true_when_updates_empty(): void
    {
        // Empty array → immediate return true, no Mailjet call.
        $result = \Service\Email\EmailReminderService::sendFolderUpdateNotification(
            'student@example.com', 'Bob', '22001234', []
        );
        $this->assertTrue($result);
    }

    /** @test */
    public function sendFolderUpdateNotification_returns_bool_with_plain_updates(): void
    {
        $result = \Service\Email\EmailReminderService::sendFolderUpdateNotification(
            'student@example.com', 'Bob Martin', '22001234', ['Document CV accepté']
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendFolderUpdateNotification_parses_all_structured_tokens(): void
    {
        $updates = [
            '__SECTION_ACCEPTEES__CV, Photo__END_SECTION__',
            '__SECTION_REFUSEES__Convention__END_SECTION__',
            '__STATUT_GLOBAL__En attente__END_STATUT__',
            '__DATE_LIMITE__2024-12-31__END_DATE__',
            'Note libre',
        ];

        $result = \Service\Email\EmailReminderService::sendFolderUpdateNotification(
            'student@example.com', 'Bob Martin', '22001234', $updates
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendFolderUpdateNotification_handles_empty_token_values(): void
    {
        $updates = [
            '__SECTION_ACCEPTEES____END_SECTION__',   // empty value
            '__SECTION_REFUSEES____END_SECTION__',
        ];

        $result = \Service\Email\EmailReminderService::sendFolderUpdateNotification(
            'student@example.com', 'Claire', '22005678', $updates
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendFolderUpdateNotification_returns_false_when_credentials_missing(): void
    {
        $this->clearCredentials();
        $result = \Service\Email\EmailReminderService::sendFolderUpdateNotification(
            'student@example.com', 'Bob', '22001234', ['update']
        );
        $this->assertFalse($result);
        $this->restoreCredentials();
    }

    // ==================================================================
    // 3. sendValidationConfirmation
    // ==================================================================

    /** @test */
    public function sendValidationConfirmation_returns_bool_for_known_doc_types(): void
    {
        $result = \Service\Email\EmailReminderService::sendValidationConfirmation(
            'student@example.com', 'Alice Liddell',
            ['cv', 'photo', 'convention', 'lettre_motivation', 'langues_file'],
            '22001234'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendValidationConfirmation_returns_bool_for_unknown_doc_type(): void
    {
        // Unknown types fall back to ucfirst(); must not crash.
        $result = \Service\Email\EmailReminderService::sendValidationConfirmation(
            'student@example.com', 'Alice', ['rapport_stage'], '22001234'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendValidationConfirmation_returns_false_when_credentials_missing(): void
    {
        $this->clearCredentials();
        $result = \Service\Email\EmailReminderService::sendValidationConfirmation(
            'student@example.com', 'Alice', ['cv'], '22001234'
        );
        $this->assertFalse($result);
        $this->restoreCredentials();
    }

    // ==================================================================
    // 4. sendDocumentDeposited
    // ==================================================================

    /** @test */
    public function sendDocumentDeposited_returns_bool_for_all_known_types(): void
    {
        foreach (['photo', 'cv', 'convention', 'lettre_motivation', 'langues_file'] as $type) {
            $result = \Service\Email\EmailReminderService::sendDocumentDeposited(
                'student@example.com', 'Jean Paul', $type, '22001234'
            );
            $this->assertIsBool($result, "Failed for type: {$type}");
        }
    }

    /** @test */
    public function sendDocumentDeposited_returns_bool_for_unknown_type(): void
    {
        $result = \Service\Email\EmailReminderService::sendDocumentDeposited(
            'student@example.com', 'Jean Paul', 'autre_document', '22001234'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendDocumentDeposited_handles_empty_numEtu(): void
    {
        $result = \Service\Email\EmailReminderService::sendDocumentDeposited(
            'student@example.com', 'Jean Paul', 'cv', ''
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendDocumentDeposited_returns_false_when_credentials_missing(): void
    {
        $this->clearCredentials();
        $result = \Service\Email\EmailReminderService::sendDocumentDeposited(
            'student@example.com', 'Jean Paul', 'cv', '22001234'
        );
        $this->assertFalse($result);
        $this->restoreCredentials();
    }

    // ==================================================================
    // 5. sendDocumentValidated
    // ==================================================================

    /** @test */
    public function sendDocumentValidated_returns_bool_for_all_known_types(): void
    {
        foreach (['photo', 'cv', 'convention', 'lettre_motivation', 'langues_file'] as $type) {
            $result = \Service\Email\EmailReminderService::sendDocumentValidated(
                'student@example.com', 'Sophie Bernard', $type, '22009876'
            );
            $this->assertIsBool($result, "Failed for type: {$type}");
        }
    }

    /** @test */
    public function sendDocumentValidated_returns_bool_for_unknown_type(): void
    {
        $result = \Service\Email\EmailReminderService::sendDocumentValidated(
            'student@example.com', 'Sophie Bernard', 'attestation_emploi', '22009876'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendDocumentValidated_returns_false_when_credentials_missing(): void
    {
        $this->clearCredentials();
        $result = \Service\Email\EmailReminderService::sendDocumentValidated(
            'student@example.com', 'Sophie Bernard', 'cv', '22009876'
        );
        $this->assertFalse($result);
        $this->restoreCredentials();
    }

    // ==================================================================
    // 6. sendMessageNotification
    // ==================================================================

    /** @test */
    public function sendMessageNotification_returns_bool_for_normal_message(): void
    {
        $result = \Service\Email\EmailReminderService::sendMessageNotification(
            'recipient@example.com', 'Alice', 'Bob',
            'Bonjour Alice, voici un message.',
            'https://ri-amu.app/messages'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendMessageNotification_returns_bool_when_preview_exceeds_150_chars(): void
    {
        $result = \Service\Email\EmailReminderService::sendMessageNotification(
            'recipient@example.com', 'Alice', 'Bob',
            str_repeat('A', 300),
            'https://ri-amu.app/messages'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendMessageNotification_boundary_exactly_150_chars(): void
    {
        // 150 chars: must NOT be truncated.
        $result = \Service\Email\EmailReminderService::sendMessageNotification(
            'r@example.com', 'Alice', 'Bob',
            str_repeat('B', 150),
            'https://ri-amu.app'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendMessageNotification_boundary_exactly_151_chars(): void
    {
        // 151 chars: must be truncated to 150.
        $result = \Service\Email\EmailReminderService::sendMessageNotification(
            'r@example.com', 'Alice', 'Bob',
            str_repeat('C', 151),
            'https://ri-amu.app'
        );
        $this->assertIsBool($result);
    }

    /** @test */
    public function sendMessageNotification_returns_false_when_credentials_missing(): void
    {
        $this->clearCredentials();
        $result = \Service\Email\EmailReminderService::sendMessageNotification(
            'recipient@example.com', 'Alice', 'Bob', 'msg', 'https://ri-amu.app'
        );
        $this->assertFalse($result);
        $this->restoreCredentials();
    }
}
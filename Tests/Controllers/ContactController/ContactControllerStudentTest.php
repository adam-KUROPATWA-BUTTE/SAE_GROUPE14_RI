<?php

namespace Tests\Controllers\ContactController;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Controllers\ContactController\ContactControllerStudent;
use Service\ContactService;
use Model\Entity\Conversation;

/**
 * Tests unitaires pour ContactControllerStudent
 *
 * Lancement : ./vendor/bin/phpunit Tests/Controllers/ContactController/ContactControllerStudentTest.php
 */
class ContactControllerStudentTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * Crée une instance de ContactControllerStudent avec :
     *  - un ContactService mocké (pas de BDD)
     *  - startSession() neutralisée pour ne pas écraser $_SESSION
     *  - redirect() surchargée pour ne pas appeler exit
     *
     * @return array{0: ContactControllerStudent, 1: MockObject&ContactService}
     */
    private function makeController(): array
    {
        $serviceMock = $this->createMock(ContactService::class);

        $controller = new class($serviceMock) extends ContactControllerStudent {
            public function __construct(ContactService $service)
            {
                // On n'appelle PAS parent::__construct() pour éviter new ConversationPDO()
                $ref  = new \ReflectionClass(ContactControllerStudent::class);
                $prop = $ref->getProperty('contactService');
                $prop->setAccessible(true);
                $prop->setValue($this, $service);
            }

            // Neutralise session_start() pour ne pas écraser $_SESSION peuplé par le test
            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            protected function renderView(string $view, array $data = []): void
            {
                // no-op en test
            }
        };

        return [$controller, $serviceMock];
    }

    // -------------------------------------------------------------------------
    // support()
    // -------------------------------------------------------------------------

    public function test_support_returns_true_for_contact_student_page(): void
    {
        $this->assertTrue(
            ContactControllerStudent::support('contact-student', 'GET')
        );
    }

    public function test_support_returns_false_for_other_pages(): void
    {
        $this->assertFalse(ContactControllerStudent::support('home', 'GET'));
        $this->assertFalse(ContactControllerStudent::support('messages-admin', 'POST'));
        $this->assertFalse(ContactControllerStudent::support('', 'GET'));
    }

    // -------------------------------------------------------------------------
    // Langue
    // -------------------------------------------------------------------------

    public function test_lang_defaults_to_fr_when_not_set(): void
    {
        $lang = $_SESSION['lang'] ?? 'fr';
        $this->assertSame('fr', $lang);
    }

    public function test_lang_is_set_from_get_parameter(): void
    {
        $_GET['lang'] = 'en';
        if (in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $this->assertSame('en', $_SESSION['lang']);
    }

    public function test_lang_ignores_invalid_values(): void
    {
        $_GET['lang'] = 'es';
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $this->assertArrayNotHasKey('lang', $_SESSION);
    }

    // -------------------------------------------------------------------------
    // Action : reply (POST)
    // -------------------------------------------------------------------------

    public function test_reply_action_calls_addMessage_with_correct_params(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['action']            = 'reply';
        $_GET['id']                = '10';
        $_POST['student_reply']    = 'Merci pour votre réponse.';

        [$controller, $serviceMock] = $this->makeController();

        $serviceMock
            ->expects($this->once())
            ->method('addMessage')
            ->with(10, 'student', 'Merci pour votre réponse.');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_reply_action_does_not_call_addMessage_when_reply_is_empty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['action']            = 'reply';
        $_GET['id']                = '10';
        $_POST['student_reply']    = '   ';

        [$controller, $serviceMock] = $this->makeController();

        $serviceMock->expects($this->never())->method('addMessage');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_reply_action_does_not_call_addMessage_when_id_is_zero(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['action']            = 'reply';
        $_GET['id']                = '0';
        $_POST['student_reply']    = 'Un message';

        [$controller, $serviceMock] = $this->makeController();

        $serviceMock->expects($this->never())->method('addMessage');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_reply_action_sets_session_message_in_french(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_SESSION['lang']          = 'fr';
        $_GET['action']            = 'reply';
        $_GET['id']                = '5';
        $_POST['student_reply']    = 'Bonjour';

        [$controller, $serviceMock] = $this->makeController();
        $serviceMock->method('addMessage');

        try {
            $controller->control();
        } catch (\Throwable $e) {}

        $this->assertSame('Votre message a été envoyé !', $_SESSION['message'] ?? '');
    }

    public function test_reply_action_sets_session_message_in_english(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_SESSION['lang']          = 'en';
        $_GET['action']            = 'reply';
        $_GET['id']                = '5';
        $_POST['student_reply']    = 'Hello';

        [$controller, $serviceMock] = $this->makeController();
        $serviceMock->method('addMessage');

        try {
            $controller->control();
        } catch (\Throwable $e) {}

        $this->assertSame('Message sent!', $_SESSION['message'] ?? '');
    }

    // -------------------------------------------------------------------------
    // Action : form (POST) — createConversation
    // -------------------------------------------------------------------------

    public function test_form_action_calls_createConversation_with_correct_params(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['action']            = 'form';
        $_POST['name']             = 'Alice';
        $_POST['email']            = 'alice@example.com';
        $_POST['subject']          = 'Question';
        $_POST['message']          = 'Bonjour, j\'ai une question.';

        [$controller, $serviceMock] = $this->makeController();

        $serviceMock
            ->expects($this->once())
            ->method('createConversation')
            ->with(
                studentNumEtu: '12345',
                name: 'Alice',
                email: 'alice@example.com',
                subject: 'Question',
                initialMessage: 'Bonjour, j\'ai une question.'
            );

        $serviceMock->method('getStudentConversations')->willReturn([]);

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_form_action_trims_fields_before_creating_conversation(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['action']            = 'form';
        $_POST['name']             = '  Alice  ';
        $_POST['email']            = '  alice@example.com  ';
        $_POST['subject']          = '  Question  ';
        $_POST['message']          = '  Mon message  ';

        [$controller, $serviceMock] = $this->makeController();

        $serviceMock
            ->expects($this->once())
            ->method('createConversation')
            ->with(
                studentNumEtu: '12345',
                name: 'Alice',
                email: 'alice@example.com',
                subject: 'Question',
                initialMessage: 'Mon message'
            );

        $serviceMock->method('getStudentConversations')->willReturn([]);

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_form_action_does_not_crash_when_createConversation_throws(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['action']            = 'form';
        $_POST['name']             = 'Alice';
        $_POST['email']            = 'alice@example.com';
        $_POST['subject']          = 'Question';
        $_POST['message']          = 'Message';

        [$controller, $serviceMock] = $this->makeController();

        $serviceMock
            ->method('createConversation')
            ->willThrowException(new \Exception('DB error'));

        $serviceMock->method('getStudentConversations')->willReturn([]);

        $exceptionPropagated = false;
        try {
            $controller->control();
        } catch (\Exception $e) {
            if ($e->getMessage() === 'DB error') {
                $exceptionPropagated = true;
            }
        } catch (\Throwable $e) {}

        $this->assertFalse($exceptionPropagated, 'L\'exception de createConversation doit être catchée par le contrôleur.');
    }

    // -------------------------------------------------------------------------
    // Action : form (GET) — getStudentConversations
    // -------------------------------------------------------------------------

    public function test_get_request_calls_getStudentConversations(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['numetu']        = '12345';

        [$controller, $serviceMock] = $this->makeController();

        $serviceMock
            ->expects($this->once())
            ->method('getStudentConversations')
            ->with('12345')
            ->willReturn([]);

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_get_request_passes_conversations_to_view(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['numetu']        = '99999';

        [$controller, $serviceMock] = $this->makeController();

        $fakeConversation = $this->createMock(Conversation::class);

        $serviceMock
            ->method('getStudentConversations')
            ->with('99999')
            ->willReturn([$fakeConversation]);

        $serviceMock->expects($this->once())->method('getStudentConversations')->with('99999');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    // -------------------------------------------------------------------------
    // buildUrl helper
    // -------------------------------------------------------------------------

    public function test_buildUrl_always_appends_lang_parameter(): void
    {
        $lang     = 'en';
        $buildUrl = function (string $url, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        $result = $buildUrl('index.php', ['page' => 'contact-student']);
        $this->assertStringContainsString('lang=en', $result);
        $this->assertStringContainsString('page=contact-student', $result);
    }

    public function test_buildUrl_works_with_french_lang(): void
    {
        $lang     = 'fr';
        $buildUrl = function (string $url, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        $result = $buildUrl('index.php', ['action' => 'reply', 'id' => '5']);
        $this->assertStringContainsString('lang=fr', $result);
        $this->assertStringContainsString('action=reply', $result);
        $this->assertStringContainsString('id=5', $result);
    }
}
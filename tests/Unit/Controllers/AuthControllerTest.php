<?php

namespace Tests\Unit\Controllers;

require_once __DIR__ . '/../../../Autoloader.php';
require_once __DIR__ . '/../../Fixtures/Stubs/Model/ModelUserAdminStub.php';
require_once __DIR__ . '/../../Fixtures/Stubs/Model/ModelUserStudentStub.php';
require_once __DIR__ . '/../../Fixtures/Stubs/Controller/ControllerSiteAuthNamespaceFunctions.php';

use Controllers\site\AuthController;
use Model\User\UserAdmin as UserAdminStub;
use Model\User\UserStudent as UserStudentStub;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['__captured_headers'] = [];

        UserAdminStub::reset();
        UserStudentStub::reset();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    public function testSupportReturnsTrueForKnownPages(): void
    {
        $pages = ['login', 'logout', 'forgot-password', 'reset-password'];
        foreach ($pages as $page) {
            $this->assertTrue(AuthController::support($page, 'GET'));
        }
    }

    public function testSupportReturnsFalseForUnknownPage(): void
    {
        $this->assertFalse(AuthController::support('unknown', 'GET'));
    }

    public function testLoginFailsWithEmptyFields(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['identifier'] = '';
        $_POST['password'] = '';

        $bufferStarted = false;
        try {
            ob_start();
            $bufferStarted = true;
            AuthController::login();
        } catch (\RuntimeException $e) {
            // expected redirect via header stub
        } finally {
            if ($bufferStarted && ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        $this->assertSame('Please fill in all fields', $_SESSION['error'] ?? null);
    }
}

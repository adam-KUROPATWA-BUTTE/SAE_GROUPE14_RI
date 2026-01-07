<?php

require_once __DIR__ . '/../../../Autoloader.php';
require_once __DIR__ . '/../../Fixtures/Stubs/Controller/ControllerAuthGuardStub.php';

use PHPUnit\Framework\TestCase;
use Controllers\Auth_Guard;

class AuthGuardTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testIsAdminAndIsStudent(): void
    {
        $_SESSION['user_role'] = 'admin';
        $this->assertTrue(Auth_Guard::isAdmin());
        $this->assertFalse(Auth_Guard::isStudent());

        $_SESSION['user_role'] = 'etudiant';
        $this->assertTrue(Auth_Guard::isStudent());
        $this->assertFalse(Auth_Guard::isAdmin());
    }

    public function testRequireRoleDoesNotThrowWhenCorrectRole(): void
    {
        $_SESSION['user_role'] = 'admin';
        $this->assertNull(Auth_Guard::requireRole('admin'));

        $_SESSION['user_role'] = 'etudiant';
        $this->assertNull(Auth_Guard::requireRole('etudiant'));
    }

    public function testRequireRoleFailsWhenWrongRole(): void
    {
        $_SESSION['user_role'] = 'etudiant';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Redirected');

        Auth_Guard::requireRole('admin');
    }
}

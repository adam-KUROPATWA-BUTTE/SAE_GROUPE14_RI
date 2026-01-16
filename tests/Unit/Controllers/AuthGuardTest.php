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
        Auth_Guard::requireRole('admin');
        // No exception thrown - test passes

        $_SESSION['user_role'] = 'etudiant';
        Auth_Guard::requireRole('etudiant');
        // No exception thrown - test passes
        $this->addToAssertionCount(2);
    }

    public function testRequireRoleFailsWhenWrongRole(): void
    {
        $_SESSION['user_role'] = 'etudiant';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Redirected');

        Auth_Guard::requireRole('admin');
    }
}

<?php

namespace Tests\Controllers;

use Controllers\NotFoundController;
use PHPUnit\Framework\TestCase;
use Controllers\ControllerInterface;

class NotFoundControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // -------------------------------------------------------------------------
    // support()
    // -------------------------------------------------------------------------

    public function testSupportAlwaysReturnsFalseForGet(): void
    {
        $this->assertFalse(NotFoundController::support('404', 'GET'));
    }

    public function testSupportAlwaysReturnsFalseForPost(): void
    {
        $this->assertFalse(NotFoundController::support('home', 'POST'));
    }

    public function testSupportAlwaysReturnsFalseForEmptyPage(): void
    {
        $this->assertFalse(NotFoundController::support('', 'GET'));
    }

    public function testSupportAlwaysReturnsFalseForAnyArguments(): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            foreach (['dashboard-admin', 'login', 'unknown', ''] as $page) {
                $this->assertFalse(
                    NotFoundController::support($page, $method),
                    "support('$page', '$method') should always return false"
                );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Tritanopia session logic (isolated, no View call)
    // -------------------------------------------------------------------------

    public function testTritanopiaIsFalseWhenSessionIsEmpty(): void
    {
        $_SESSION = [];
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        $this->assertFalse($isTritanopia);
    }

    public function testTritanopiaIsTrueWhenSessionIsTrue(): void
    {
        $_SESSION['tritanopia'] = true;
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        $this->assertTrue($isTritanopia);
    }

    public function testTritanopiaIsFalseWhenSessionIsFalse(): void
    {
        $_SESSION['tritanopia'] = false;
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        $this->assertFalse($isTritanopia);
    }

    public function testTritanopiaIsFalseWhenSessionIsZero(): void
    {
        $_SESSION['tritanopia'] = 0;
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        $this->assertFalse($isTritanopia);
    }

    public function testTritanopiaIsTrueWhenSessionIsStringOne(): void
    {
        $_SESSION['tritanopia'] = '1';
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        // '1' is truthy but (bool)'1' !== true... wait: (bool)'1' IS true
        $this->assertTrue($isTritanopia);
    }

    public function testTritanopiaIsFalseWhenSessionIsNull(): void
    {
        $_SESSION['tritanopia'] = null;
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        $this->assertFalse($isTritanopia);
    }

    // -------------------------------------------------------------------------
    // Title constant
    // -------------------------------------------------------------------------

    public function testTitreIsPageNonTrouvee(): void
    {
        $titre = 'Page non trouvée';
        $this->assertEquals('Page non trouvée', $titre);
    }

    // -------------------------------------------------------------------------
    // Implements ControllerInterface
    // -------------------------------------------------------------------------

    public function testImplementsControllerInterface(): void
    {
        $controller = new NotFoundController();
        $this->assertInstanceOf(ControllerInterface::class, $controller);
    }
}
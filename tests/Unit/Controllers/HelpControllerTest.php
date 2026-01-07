<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/public/module/site/Controllers/ControllerInterface.php';
require_once dirname(__DIR__, 3) . '/public/module/site/Controllers/HelpController.php';

use Controllers\site\HelpController;

/**
 * Test unitaire de HelpController
 */
class HelpControllerTest extends TestCase
{
    public function testSupportReturnsTrueForHelpPages(): void
    {
        $this->assertTrue(HelpController::support('help', 'GET'));
        $this->assertTrue(HelpController::support('/help', 'GET'));
    }

    public function testSupportReturnsFalseForOtherPages(): void
    {
        $this->assertFalse(HelpController::support('login', 'GET'));
        $this->assertFalse(HelpController::support('dashboard', 'GET'));
    }

}

<?php

require_once __DIR__ . '/../../../Autoloader.php';
require_once __DIR__ . '/../../Fixtures/Stubs/View/ViewHomePageStub.php';
require_once __DIR__ . '/../../Fixtures/Stubs/Database/DatabaseStub.php';
require_once __DIR__ . '/../../../public/module/site/Controllers/ControllerInterface.php';
require_once __DIR__ . '/../../../public/module/site/Controllers/IndexController.php';

use Controllers\site\IndexController;
use PHPUnit\Framework\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_GET = [];
    }

    public function testSupportReturnsTrueForHome(): void
    {
        /** @phpstan-ignore class.notFound */
        $this->assertTrue(IndexController::support('home', 'GET'));
    }

    public function testSupportReturnsFalseForOtherPages(): void
    {
        /** @phpstan-ignore class.notFound */
        $this->assertFalse(IndexController::support('login', 'GET'));
    }

    public function testControlOutputsSomething(): void
    {
        /** @phpstan-ignore class.notFound */
        $controller = new IndexController();

        ob_start();
        /** @phpstan-ignore class.notFound */
        $controller->control();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertEquals('HOME PAGE', $output);
    }
}

<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../Autoloader.php';
require_once __DIR__ . '/../../Fixtures/Stubs/Model/ModelFolderAdminDashboardStub.php';
require_once __DIR__ . '/../../Fixtures/Stubs/View/ViewDashboardPageAdminStub.php';
require_once __DIR__ . '/../../Fixtures/Stubs/Controller/ControllerSiteAuthNamespaceFunctions.php';
require_once __DIR__ . '/../../../public/module/site/Controllers/ControllerInterface.php';
require_once __DIR__ . '/../../../public/module/site/Controllers/DashboardController.php';

use Controllers\site\DashboardController;
use View\Dashboard\DashboardPageAdmin as DashboardPageAdminStub;
use Model\Folder\FolderAdmin as FolderAdminStub;

class DashboardControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_GET = [];
        DashboardPageAdminStub::$lastArgs = null;
        DashboardPageAdminStub::$renderCalled = false;
        /** @phpstan-ignore staticProperty.notFound */
        FolderAdminStub::$getDossiersIncompletsCalled = false;
    }

    public function testShowAdminDashboardRedirectsWhenNotAdmin(): void
    {
        $_SESSION['user_role'] = 'etudiant';
        $_GET['page'] = 'dashboard-admin';

        $controller = new DashboardController();

        $bufferStarted = false;
        try {
            ob_start();
            $bufferStarted = true;
            $controller->control();
            $this->fail('Expected redirect via header stub');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Location: /login', $e->getMessage());
        } finally {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
    }

    public function testShowAdminDashboardRendersWhenAdmin(): void
    {
        $_SESSION['user_role'] = 'admin';
        $_GET['page'] = 'dashboard-admin';

        $controller = new DashboardController();

        ob_start();
        $controller->control();
        $output = ob_get_clean();

        /** @phpstan-ignore staticProperty.notFound */
        $this->assertTrue(FolderAdminStub::$getDossiersIncompletsCalled);
        $this->assertTrue(DashboardPageAdminStub::$renderCalled);
        $this->assertSame('DASHBOARD', $output);
    }
}

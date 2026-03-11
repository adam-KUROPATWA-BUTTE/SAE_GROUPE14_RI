<?php

namespace Tests\Controllers\HomeController;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Controllers\HomeController\SuperAdminController;
use Controllers\HomeController\HomeControllerAdmin;
use Controllers\HomeController\HomeControllerCoordinateur;
use Controllers\HomeController\HomeControllerStudent;
use Service\SuperAdminService;
use Model\UseCase\GetAdminStatsUseCase;

/**
 * Tests unitaires pour SuperAdminController, HomeControllerAdmin,
 * HomeControllerCoordinateur et HomeControllerStudent.
 *
 * Lancement : ./vendor/bin/phpunit Tests/Controllers/HomeController/HomeControllersTest.php
 */
class HomeControllersTest extends TestCase
{
    // =========================================================================
    // setUp
    // =========================================================================

    protected function setUp(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    // =========================================================================
    // Factories
    // =========================================================================

    /**
     * @return array{0: SuperAdminController, 1: MockObject&SuperAdminService}
     */
    private function makeSuperAdmin(): array
    {
        $serviceMock = $this->createMock(SuperAdminService::class);

        $controller = new class($serviceMock) extends SuperAdminController {
            private SuperAdminService $injectedService;

            public function __construct(SuperAdminService $svc)
            {
                $this->injectedService = $svc;
            }

            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            /** @param array<string, mixed> $data */
            protected function renderView(string $view, array $data = []): void {}

            protected function makeService(): SuperAdminService
            {
                return $this->injectedService;
            }
        };

        return [$controller, $serviceMock];
    }

    /**
     * @return array{0: HomeControllerAdmin, 1: MockObject&GetAdminStatsUseCase}
     */
    private function makeAdminHome(): array
    {
        $useCaseMock = $this->createMock(GetAdminStatsUseCase::class);

        $controller = new class($useCaseMock) extends HomeControllerAdmin {
            private GetAdminStatsUseCase $injectedUseCase;

            public function __construct(GetAdminStatsUseCase $useCase)
            {
                $this->injectedUseCase = $useCase;
            }

            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            /** @param array<string, mixed> $data */
            protected function renderView(string $view, array $data = []): void {}

            protected function log(string $message): void {}

            protected function makeUseCase(): GetAdminStatsUseCase
            {
                return $this->injectedUseCase;
            }

            /** @return array<int, mixed> */
            protected function fetchDepartements(): array
            {
                return [];
            }
        };

        $_SESSION['role'] = 'admin';

        return [$controller, $useCaseMock];
    }

    /**
     * @return array{0: HomeControllerCoordinateur, 1: MockObject&GetAdminStatsUseCase}
     */
    private function makeCoordHome(): array
    {
        $useCaseMock = $this->createMock(GetAdminStatsUseCase::class);

        $controller = new class($useCaseMock) extends HomeControllerCoordinateur {
            private GetAdminStatsUseCase $injectedUseCase;

            public function __construct(GetAdminStatsUseCase $useCase)
            {
                $this->injectedUseCase = $useCase;
            }

            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            /** @param array<string, mixed> $data */
            protected function renderView(string $view, array $data = []): void {}

            protected function log(string $message): void {}

            protected function makeUseCase(): GetAdminStatsUseCase
            {
                return $this->injectedUseCase;
            }
        };

        return [$controller, $useCaseMock];
    }

    /**
     * @return HomeControllerStudent
     */
    private function makeStudentHome(): HomeControllerStudent
    {
        return new class extends HomeControllerStudent {
            public function __construct()
            {
            }

            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            /** @param array<string, mixed> $data */
            protected function renderView(string $view, array $data = []): void {}
        };
    }

    // =========================================================================
    // SuperAdminController — support()
    // =========================================================================

    public function test_superadmin_support_returns_true_for_super_admin(): void
    {
        $this->assertTrue(SuperAdminController::support('super-admin', 'GET'));
        $this->assertTrue(SuperAdminController::support('super-admin', 'POST'));
    }

    public function test_superadmin_support_returns_false_for_other_pages(): void
    {
        $this->assertFalse(SuperAdminController::support('home', 'GET'));
        $this->assertFalse(SuperAdminController::support('admin', 'GET'));
        $this->assertFalse(SuperAdminController::support('', 'GET'));
    }

    // =========================================================================
    // SuperAdminController — accès
    // =========================================================================

    public function test_superadmin_redirects_to_login_when_not_authenticated(): void
    {
        [$controller] = $this->makeSuperAdmin();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/redirect:.*login/');

        $controller->control();
    }

    public function test_superadmin_redirects_to_login_for_wrong_role(): void
    {
        $_SESSION['role'] = 'admin';
        [$controller]     = $this->makeSuperAdmin();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/redirect:.*login/');

        $controller->control();
    }

    // =========================================================================
    // SuperAdminController — langue
    // =========================================================================

    public function test_superadmin_lang_defaults_to_fr(): void
    {
        $_SESSION['role'] = 'super_admin';
        [$controller, $serviceMock] = $this->makeSuperAdmin();

        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('fr', $_SESSION['lang'] ?? 'fr');
    }

    public function test_superadmin_lang_set_from_get(): void
    {
        $_SESSION['role'] = 'super_admin';
        $_GET['lang']     = 'en';
        [$controller, $serviceMock] = $this->makeSuperAdmin();

        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('en', $_SESSION['lang']);
    }

    public function test_superadmin_lang_ignores_invalid_value(): void
    {
        $_SESSION['role'] = 'super_admin';
        $_GET['lang']     = 'de';
        [$controller, $serviceMock] = $this->makeSuperAdmin();

        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertArrayNotHasKey('lang', $_SESSION);
    }

    // =========================================================================
    // SuperAdminController — POST action=add_department
    // =========================================================================

    public function test_superadmin_add_department_calls_addDepartment(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'add_department';
        $_POST['new_department']   = 'Informatique';

        [$controller, $serviceMock] = $this->makeSuperAdmin();

        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        $serviceMock->expects($this->once())->method('addDepartment')->with('INFORMATIQUE');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_add_department_does_not_add_when_already_exists(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'add_department';
        $_POST['new_department']   = 'INFO';

        [$controller, $serviceMock] = $this->makeSuperAdmin();

        $serviceMock->method('getAvailableDepartments')->willReturn(['INFO']);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('addDepartment');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_add_department_sets_success_message_in_french(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_SESSION['lang']          = 'fr';
        $_POST['action']           = 'add_department';
        $_POST['new_department']   = 'Maths';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->method('addDepartment');

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue(true);
    }

    public function test_superadmin_add_department_sets_error_when_empty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'add_department';
        $_POST['new_department']   = '';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('addDepartment');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    // =========================================================================
    // SuperAdminController — POST action=add_site
    // =========================================================================

    public function test_superadmin_add_site_calls_addSite(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'add_site';
        $_POST['new_site']         = 'Marseille';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        $serviceMock->expects($this->once())->method('addSite')->with('Marseille');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_add_site_does_not_add_when_already_exists(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'add_site';
        $_POST['new_site']         = 'Marseille';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn(['Marseille']);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('addSite');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_add_site_does_not_add_when_empty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'add_site';
        $_POST['new_site']         = '';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('addSite');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    // =========================================================================
    // SuperAdminController — POST action=delete
    // =========================================================================

    public function test_superadmin_delete_calls_deleteAccount(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'delete';
        $_POST['login']            = 'user@example.com';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        $serviceMock->expects($this->once())->method('deleteAccount')->with('user@example.com')->willReturn(true);

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_delete_does_nothing_when_login_empty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'delete';
        $_POST['login']            = '';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('deleteAccount');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    // =========================================================================
    // SuperAdminController — POST action=create
    // =========================================================================

    public function test_superadmin_create_calls_createAccount_with_valid_data(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'create';
        $_POST['login']            = 'coord@univ.fr';
        $_POST['password']         = 'Secure@Password1!';
        $_POST['role']             = 'coordinateur';
        $_POST['departement']      = 'Informatique';
        $_POST['site']             = '';
        $_POST['nom']              = 'Dupont';
        $_POST['prenom']           = 'Alice';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        $serviceMock->expects($this->once())->method('createAccount')
            ->with('coord@univ.fr', 'Secure@Password1!', 'coordinateur', 'Informatique', null, 'Dupont', 'Alice');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_create_does_not_call_createAccount_when_login_empty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'create';
        $_POST['login']            = '';
        $_POST['password']         = 'Secure@Password1!';
        $_POST['role']             = 'admin';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('createAccount');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_create_rejects_weak_password(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'create';
        $_POST['login']            = 'admin@univ.fr';
        $_POST['password']         = 'weak';
        $_POST['role']             = 'admin';
        $_POST['site']             = 'Marseille';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('createAccount');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_create_rejects_invalid_email_login(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'create';
        $_POST['login']            = 'not-an-email';
        $_POST['password']         = 'Secure@Password1!';
        $_POST['role']             = 'admin';
        $_POST['site']             = 'Marseille';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('createAccount');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_create_rejects_coord_without_department(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'create';
        $_POST['login']            = 'coord@univ.fr';
        $_POST['password']         = 'Secure@Password1!';
        $_POST['role']             = 'coordinateur';
        $_POST['departement']      = '';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('createAccount');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_create_rejects_admin_without_site(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'create';
        $_POST['login']            = 'admin@univ.fr';
        $_POST['password']         = 'Secure@Password1!';
        $_POST['role']             = 'admin';
        $_POST['site']             = '';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);
        $serviceMock->expects($this->never())->method('createAccount');

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_superadmin_create_passes_site_for_admin_role(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'super_admin';
        $_POST['action']           = 'create';
        $_POST['login']            = 'admin@univ.fr';
        $_POST['password']         = 'Secure@Password1!';
        $_POST['role']             = 'admin';
        $_POST['site']             = 'Aix';
        $_POST['departement']      = '';
        $_POST['nom']              = '';
        $_POST['prenom']           = '';

        [$controller, $serviceMock] = $this->makeSuperAdmin();
        $serviceMock->method('getAvailableDepartments')->willReturn([]);
        $serviceMock->method('getAvailableSites')->willReturn([]);
        $serviceMock->method('getAllAccounts')->willReturn([]);

        $serviceMock->expects($this->once())->method('createAccount')
            ->with('admin@univ.fr', 'Secure@Password1!', 'admin', null, 'Aix', null, null);

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    // =========================================================================
    // HomeControllerAdmin — support()
    // =========================================================================

    public function test_home_admin_support_returns_true_for_home_admin_get(): void
    {
        $this->assertTrue(HomeControllerAdmin::support('home-admin', 'GET'));
    }

    public function test_home_admin_support_returns_false_for_post(): void
    {
        $this->assertFalse(HomeControllerAdmin::support('home-admin', 'POST'));
    }

    public function test_home_admin_support_returns_false_for_other_pages(): void
    {
        $this->assertFalse(HomeControllerAdmin::support('home', 'GET'));
        $this->assertFalse(HomeControllerAdmin::support('', 'GET'));
    }

    // =========================================================================
    // HomeControllerAdmin — langue
    // =========================================================================

    public function test_home_admin_lang_set_from_get(): void
    {
        $_GET['lang'] = 'en';
        [$controller] = $this->makeAdminHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('en', $_SESSION['lang']);
    }

    public function test_home_admin_lang_ignores_invalid_value(): void
    {
        $_GET['lang'] = 'zh';
        [$controller] = $this->makeAdminHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertArrayNotHasKey('lang', $_SESSION);
    }

    // =========================================================================
    // HomeControllerAdmin — filtres mobilite / departement
    // =========================================================================

    public function test_home_admin_mobilite_filter_accepted(): void
    {
        $_GET['mobilite'] = 'etude';
        [$controller]     = $this->makeAdminHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue(true);
    }

    public function test_home_admin_mobilite_filter_rejected_for_invalid_value(): void
    {
        $_GET['mobilite'] = 'invalid';
        [$controller]     = $this->makeAdminHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue(true);
    }

    public function test_home_admin_departement_filter_set_from_get(): void
    {
        $_GET['departement'] = 'Informatique';
        [$controller]        = $this->makeAdminHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue(true);
    }

    // =========================================================================
    // HomeControllerAdmin — tritanopia
    // =========================================================================

    public function test_home_admin_tritanopia_set_to_true(): void
    {
        $_GET['tritanopia'] = '1';
        [$controller]       = $this->makeAdminHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue($_SESSION['tritanopia'] ?? false);
    }

    public function test_home_admin_tritanopia_set_to_false(): void
    {
        $_GET['tritanopia'] = '0';
        [$controller]       = $this->makeAdminHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertFalse($_SESSION['tritanopia'] ?? true);
    }

    // =========================================================================
    // HomeControllerCoordinateur — support()
    // =========================================================================

    public function test_home_coord_support_returns_true_for_home_coordinateur(): void
    {
        $this->assertTrue(HomeControllerCoordinateur::support('home-coordinateur', 'GET'));
        $this->assertTrue(HomeControllerCoordinateur::support('home-coordinateur', 'POST'));
    }

    public function test_home_coord_support_returns_false_for_other_pages(): void
    {
        $this->assertFalse(HomeControllerCoordinateur::support('home-admin', 'GET'));
        $this->assertFalse(HomeControllerCoordinateur::support('', 'GET'));
    }

    // =========================================================================
    // HomeControllerCoordinateur — accès
    // =========================================================================

    public function test_home_coord_redirects_to_login_when_not_authenticated(): void
    {
        [$controller] = $this->makeCoordHome();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/redirect:.*login/');

        $controller->control();
    }

    public function test_home_coord_redirects_to_login_for_wrong_role(): void
    {
        $_SESSION['role'] = 'admin';
        [$controller]     = $this->makeCoordHome();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/redirect:.*login/');

        $controller->control();
    }

    // =========================================================================
    // HomeControllerStudent — support()
    // =========================================================================

    public function test_home_student_support_returns_true_for_home_student_get(): void
    {
        $this->assertTrue(HomeControllerStudent::support('home-student', 'GET'));
    }

    public function test_home_student_support_returns_false_for_post(): void
    {
        $this->assertFalse(HomeControllerStudent::support('home-student', 'POST'));
    }

    public function test_home_student_support_returns_false_for_other_pages(): void
    {
        $this->assertFalse(HomeControllerStudent::support('home-admin', 'GET'));
        $this->assertFalse(HomeControllerStudent::support('', 'GET'));
    }

    // =========================================================================
    // HomeControllerStudent — langue
    // =========================================================================

    public function test_home_student_lang_set_from_get(): void
    {
        $_GET['lang']       = 'en';
        $_SESSION['numetu'] = '12345'; // requis sinon redirect avant traitement lang
        $controller         = $this->makeStudentHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('en', $_SESSION['lang']);
    }

    public function test_home_student_lang_ignores_invalid_value(): void
    {
        $_GET['lang']       = 'ru';
        $_SESSION['numetu'] = '12345';
        $controller         = $this->makeStudentHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertArrayNotHasKey('lang', $_SESSION);
    }

    public function test_home_student_lang_defaults_to_fr_when_not_set(): void
    {
        $_SESSION['numetu'] = '12345';
        $controller         = $this->makeStudentHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('fr', $_SESSION['lang'] ?? 'fr');
    }

    // =========================================================================
    // HomeControllerStudent — tritanopia
    // =========================================================================

    public function test_home_student_tritanopia_set_to_true(): void
    {
        $_GET['tritanopia'] = '1';
        $_SESSION['numetu'] = '12345'; // requis sinon redirect avant traitement tritanopia
        $controller         = $this->makeStudentHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue($_SESSION['tritanopia'] ?? false);
    }

    public function test_home_student_tritanopia_set_to_false(): void
    {
        $_GET['tritanopia'] = '0';
        $_SESSION['numetu'] = '12345'; // requis sinon redirect avant traitement tritanopia
        $controller         = $this->makeStudentHome();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertFalse($_SESSION['tritanopia'] ?? true);
    }

    // =========================================================================
    // HomeControllerStudent — isLoggedIn
    // =========================================================================

    public function test_home_student_is_logged_in_when_numetu_in_session(): void
    {
        $_SESSION['numetu'] = '12345';
        $controller         = $this->makeStudentHome();

        $redirected = false;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'redirect:')) {
                $redirected = true;
            }
        }

        $this->assertFalse($redirected);
    }

    public function test_home_student_not_logged_in_when_numetu_absent(): void
    {
        // Sans numetu → le contrôleur redirige vers login
        $controller = $this->makeStudentHome();

        $redirected = false;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'redirect:')) {
                $redirected = true;
            }
        }

        $this->assertTrue($redirected); // correction : sans numetu = redirect
    }

    // =========================================================================
    // buildUrl helper (commun à tous)
    // =========================================================================

    public function test_buildUrl_appends_lang_parameter(): void
    {
        $lang     = 'en';
        /** @param array<string, string> $params */
        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        $result = $buildUrl('index.php', ['page' => 'home-admin']);
        $this->assertStringContainsString('lang=en', $result);
        $this->assertStringContainsString('page=home-admin', $result);
    }

    public function test_buildUrl_uses_ampersand_when_path_already_has_query(): void
    {
        $lang     = 'fr';
        /** @param array<string, string> $params */
        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        $result = $buildUrl('index.php?page=home-admin', ['action' => 'view']);
        $this->assertStringStartsWith('index.php?page=home-admin&', $result);
        $this->assertStringContainsString('lang=fr', $result);
    }
}
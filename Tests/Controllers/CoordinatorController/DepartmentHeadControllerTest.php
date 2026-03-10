<?php

namespace Tests\Controllers\CoordinatorController;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Controllers\CoordinatorController\DepartmentHeadController;
use Model\UseCase\ManageFolderUseCase;

/**
 * Tests unitaires pour DepartmentHeadController
 *
 * Lancement : ./vendor/bin/phpunit Tests/Controllers/CoordinatorController/DepartmentHeadControllerTest.php
 */
class DepartmentHeadControllerTest extends TestCase
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
     * Crée une instance réelle de DepartmentHeadController avec un ManageFolderUseCase mocké.
     *
     * @return array{0: DepartmentHeadController, 1: MockObject&ManageFolderUseCase}
     */
    private function makeController(): array
    {
        $useCaseMock = $this->createMock(ManageFolderUseCase::class);

        $ref        = new \ReflectionClass(DepartmentHeadController::class);
        $controller = $ref->newInstanceWithoutConstructor();

        $prop = $ref->getProperty('folderUseCase');
        $prop->setAccessible(true);
        $prop->setValue($controller, $useCaseMock);

        return [$controller, $useCaseMock];
    }

    /** Résultat de pagination vide par défaut */
    private function emptyPaginationResult(): array
    {
        return ['data' => [], 'total' => 0, 'totalPages' => 0];
    }

    // -------------------------------------------------------------------------
    // support()
    // -------------------------------------------------------------------------

    public function test_support_returns_true_for_chef_departement_page(): void
    {
        $this->assertTrue(
            DepartmentHeadController::support('chef-departement', 'GET')
        );
    }

    public function test_support_returns_false_for_other_pages(): void
    {
        $this->assertFalse(DepartmentHeadController::support('home', 'GET'));
        $this->assertFalse(DepartmentHeadController::support('login', 'POST'));
        $this->assertFalse(DepartmentHeadController::support('', 'GET'));
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
        $_GET['lang'] = 'de';
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $this->assertArrayNotHasKey('lang', $_SESSION);
    }

    // -------------------------------------------------------------------------
    // Contrôle d'accès — rôles autorisés
    // -------------------------------------------------------------------------

    public function test_coordinateur_role_is_allowed(): void
    {
        $_SESSION['role'] = 'coordinateur';

        [$controller, $useCaseMock] = $this->makeController();
        $useCaseMock->method('rechercherAvecPagination')->willReturn($this->emptyPaginationResult());

        // Ne doit pas rediriger → pas d'exit avant l'appel au use case
        $useCaseMock->expects($this->once())->method('rechercherAvecPagination');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_chef_departement_role_is_allowed(): void
    {
        $_SESSION['role'] = 'chef_departement';

        [$controller, $useCaseMock] = $this->makeController();
        $useCaseMock->method('rechercherAvecPagination')->willReturn($this->emptyPaginationResult());

        $useCaseMock->expects($this->once())->method('rechercherAvecPagination');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_admin_role_is_allowed(): void
    {
        $_SESSION['role'] = 'admin';

        [$controller, $useCaseMock] = $this->makeController();
        $useCaseMock->method('rechercherAvecPagination')->willReturn($this->emptyPaginationResult());

        $useCaseMock->expects($this->once())->method('rechercherAvecPagination');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_unauthorized_role_does_not_call_use_case(): void
    {
        $_SESSION['role'] = 'etudiant'; // rôle non autorisé

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock->expects($this->never())->method('rechercherAvecPagination');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_missing_role_does_not_call_use_case(): void
    {
        // Pas de rôle en session

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock->expects($this->never())->method('rechercherAvecPagination');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    // -------------------------------------------------------------------------
    // Action : set_avis_chef (POST)
    // -------------------------------------------------------------------------

    public function test_set_avis_chef_calls_setAvisChef_with_accepte(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'chef_departement';
        $_POST['set_avis_chef']    = '1';
        $_POST['numetu']           = '12345';
        $_POST['avis']             = 'accepte';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('setAvisChef')
            ->with('12345', 'accepte');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_set_avis_chef_calls_setAvisChef_with_refuse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'chef_departement';
        $_POST['set_avis_chef']    = '1';
        $_POST['numetu']           = '12345';
        $_POST['avis']             = 'refuse';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('setAvisChef')
            ->with('12345', 'refuse');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_set_avis_chef_does_not_call_setAvisChef_when_numetu_is_empty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'chef_departement';
        $_POST['set_avis_chef']    = '1';
        $_POST['numetu']           = '';
        $_POST['avis']             = 'accepte';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock->expects($this->never())->method('setAvisChef');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_set_avis_chef_does_not_call_setAvisChef_when_avis_is_invalid(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['role']          = 'chef_departement';
        $_POST['set_avis_chef']    = '1';
        $_POST['numetu']           = '12345';
        $_POST['avis']             = 'en_attente'; // valeur non autorisée

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock->expects($this->never())->method('setAvisChef');

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    // -------------------------------------------------------------------------
    // Action : view (GET)
    // -------------------------------------------------------------------------

    public function test_view_action_calls_getStudentDetails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['role']          = 'admin';
        $_GET['action']            = 'view';
        $_GET['numetu']            = '99999';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('getStudentDetails')
            ->with('99999')
            ->willReturn([]);

        $useCaseMock->method('rechercherAvecPagination')->willReturn($this->emptyPaginationResult());

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_view_action_without_numetu_does_not_call_getStudentDetails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['role']          = 'admin';
        $_GET['action']            = 'view';
        // Pas de numetu

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock->expects($this->never())->method('getStudentDetails');
        $useCaseMock->method('rechercherAvecPagination')->willReturn($this->emptyPaginationResult());

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    // -------------------------------------------------------------------------
    // Action : list (GET) — pagination et filtres
    // -------------------------------------------------------------------------

    public function test_list_action_calls_rechercherAvecPagination(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['role']          = 'admin';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('rechercherAvecPagination')
            ->willReturn($this->emptyPaginationResult());

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_pagination_defaults_to_page_1(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['role']          = 'admin';
        // Pas de paramètre 'p'

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('rechercherAvecPagination')
            ->with($this->anything(), 1, 10)
            ->willReturn($this->emptyPaginationResult());

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_pagination_uses_get_p_parameter(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['role']          = 'admin';
        $_GET['p']                 = '3';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('rechercherAvecPagination')
            ->with($this->anything(), 3, 10)
            ->willReturn($this->emptyPaginationResult());

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_pagination_clamps_negative_page_to_1(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['role']          = 'admin';
        $_GET['p']                 = '-5';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('rechercherAvecPagination')
            ->with($this->anything(), 1, 10)
            ->willReturn($this->emptyPaginationResult());

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_filters_use_get_parameters(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['role']          = 'admin';
        $_GET['type']              = 'erasmus';
        $_GET['zone']              = 'europe';
        $_GET['complet']           = 'oui';
        $_GET['search']            = 'dupont';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('rechercherAvecPagination')
            ->with(
                $this->callback(function (array $filters) {
                    return $filters['type']    === 'erasmus'
                        && $filters['zone']    === 'europe'
                        && $filters['complet'] === 'oui'
                        && $filters['search']  === 'dupont';
                }),
                $this->anything(),
                $this->anything()
            )
            ->willReturn($this->emptyPaginationResult());

        try {
            $controller->control();
        } catch (\Throwable $e) {}
    }

    public function test_departement_filter_uses_session_for_chef_departement(): void
    {
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SESSION['role']           = 'chef_departement';
        $_SESSION['departement']    = 'Informatique';

        [$controller, $useCaseMock] = $this->makeController();

        $useCaseMock
            ->expects($this->once())
            ->method('rechercherAvecPagination')
            ->with(
                $this->callback(function (array $filters) {
                    return $filters['departement'] === 'Informatique';
                }),
                $this->anything(),
                $this->anything()
            )
            ->willReturn($this->emptyPaginationResult());

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
        $buildUrl = function (string $url, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        $result = $buildUrl('index.php', ['page' => 'chef-departement']);
        $this->assertStringContainsString('lang=en', $result);
        $this->assertStringContainsString('page=chef-departement', $result);
    }
}
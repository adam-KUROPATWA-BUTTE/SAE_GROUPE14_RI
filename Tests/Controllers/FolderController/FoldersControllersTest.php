<?php

namespace Tests\Controllers\FolderController;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Controllers\FolderController\FoldersControllerAdmin;
use Controllers\FolderController\FoldersControllerStudent;
use Model\UseCase\ManageFolderUseCase;

/**
 * Tests unitaires pour FoldersControllerAdmin et FoldersControllerStudent
 *
 * Lancement : ./vendor/bin/phpunit Tests/Controllers/FolderController/FoldersControllersTest.php
 */
class FoldersControllersTest extends TestCase
{
    // =========================================================================
    // Helpers
    // =========================================================================

    protected function setUp(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_FILES   = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /** @return array{data: array<int, mixed>, total: int, totalPages: int} */
    private function emptySearchResult(): array
    {
        return ['data' => [], 'total' => 0, 'totalPages' => 1];
    }

    /**
     * @return array{0: FoldersControllerAdmin, 1: MockObject&ManageFolderUseCase}
     */
    private function makeAdminController(): array
    {
        $useCaseMock = $this->createMock(ManageFolderUseCase::class);

        $controller = new class($useCaseMock) extends FoldersControllerAdmin {
            public function __construct(ManageFolderUseCase $useCase)
            {
                $ref  = new \ReflectionClass(FoldersControllerAdmin::class);
                $prop = $ref->getProperty('folderUseCase');
                $prop->setAccessible(true);
                $prop->setValue($this, $useCase);
            }

            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            /** @param array<string, mixed> $data */
            protected function renderView(string $view, array $data = []): void {}

            /** @param array<string, mixed> $data */
            protected function jsonResponse(array $data): never
            {
                throw new \RuntimeException('json:' . json_encode($data));
            }

            protected function log(string $message): void {}
        };

        return [$controller, $useCaseMock];
    }

    /**
     * @return array{0: FoldersControllerStudent, 1: MockObject&ManageFolderUseCase}
     */
    private function makeStudentController(): array
    {
        $useCaseMock = $this->createMock(ManageFolderUseCase::class);

        $controller = new class($useCaseMock) extends FoldersControllerStudent {
            public function __construct(ManageFolderUseCase $useCase)
            {
                $ref  = new \ReflectionClass(FoldersControllerStudent::class);
                $prop = $ref->getProperty('folderUseCase');
                $prop->setAccessible(true);
                $prop->setValue($this, $useCase);
            }

            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            /** @param array<string, mixed> $data */
            protected function renderView(string $view, array $data = []): void {}
        };

        return [$controller, $useCaseMock];
    }

    // =========================================================================
    // FoldersControllerAdmin — support()
    // =========================================================================

    public function test_admin_support_returns_true_for_folders(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('folders', 'GET'));
    }

    public function test_admin_support_returns_true_for_folders_admin(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('folders-admin', 'GET'));
    }

    public function test_admin_support_returns_true_for_save_student(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('save_student', 'POST'));
    }

    public function test_admin_support_returns_true_for_toggle_complete(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('toggle_complete', 'GET'));
    }

    public function test_admin_support_returns_true_for_update_student(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('update_student', 'POST'));
    }

    public function test_admin_support_returns_true_for_import_folders(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('import_folders', 'POST'));
    }

    public function test_admin_support_returns_true_for_update_document_status(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('update_document_status', 'POST'));
    }

    public function test_admin_support_returns_true_for_update_global_status(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('update_global_status', 'POST'));
    }

    public function test_admin_support_returns_true_for_valider_documents(): void
    {
        $this->assertTrue(FoldersControllerAdmin::support('valider_documents', 'POST'));
    }

    public function test_admin_support_returns_false_for_unknown_page(): void
    {
        $this->assertFalse(FoldersControllerAdmin::support('home', 'GET'));
        $this->assertFalse(FoldersControllerAdmin::support('', 'GET'));
        $this->assertFalse(FoldersControllerAdmin::support('login', 'POST'));
    }

    // =========================================================================
    // FoldersControllerAdmin — GET list (default)
    // =========================================================================

    public function test_admin_list_calls_searchWithoutPagination(): void
    {
        $_GET['page'] = 'folders-admin';
        [$controller, $useCaseMock] = $this->makeAdminController();

        $useCaseMock->expects($this->once())->method('searchWithoutPagination')->willReturn($this->emptySearchResult());

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_admin_list_passes_filters_from_get(): void
    {
        $_GET['page']   = 'folders-admin';
        $_GET['type']   = 'entrant';
        $_GET['zone']   = 'europe';
        $_GET['search'] = 'Dupont';

        [$controller, $useCaseMock] = $this->makeAdminController();

        $capturedFilters = null;
        $useCaseMock
            ->method('searchWithoutPagination')
            ->willReturnCallback(function (array $filters) use (&$capturedFilters) {
                $capturedFilters = $filters;
                return $this->emptySearchResult();
            });

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('entrant', $capturedFilters['type']   ?? null);
        $this->assertSame('europe',  $capturedFilters['zone']   ?? null);
        $this->assertSame('Dupont',  $capturedFilters['search'] ?? null);
    }

    // =========================================================================
    // FoldersControllerAdmin — GET action=view
    // =========================================================================

    public function test_admin_view_action_calls_getStudentDetails(): void
    {
        $_GET['page']   = 'folders-admin';
        $_GET['action'] = 'view';
        $_GET['numetu'] = 'ETU001';

        [$controller, $useCaseMock] = $this->makeAdminController();

        $useCaseMock->expects($this->once())->method('getStudentDetails')->with('ETU001')->willReturn(['NumEtu' => 'ETU001']);
        $useCaseMock->method('searchWithoutPagination')->willReturn($this->emptySearchResult());

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_admin_view_action_does_not_call_getStudentDetails_when_numetu_missing(): void
    {
        $_GET['page']   = 'folders-admin';
        $_GET['action'] = 'view';

        [$controller, $useCaseMock] = $this->makeAdminController();

        $useCaseMock->expects($this->never())->method('getStudentDetails');
        $useCaseMock->method('searchWithoutPagination')->willReturn($this->emptySearchResult());

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    // =========================================================================
    // FoldersControllerAdmin — toggle_complete
    // =========================================================================

    public function test_admin_toggle_complete_calls_toggleCompleteStatus(): void
    {
        $_GET['page']   = 'toggle_complete';
        $_GET['numetu'] = 'ETU042';

        [$controller, $useCaseMock] = $this->makeAdminController();

        $useCaseMock->expects($this->once())->method('toggleCompleteStatus')->with('ETU042')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_admin_toggle_complete_does_nothing_when_numetu_missing(): void
    {
        $_GET['page'] = 'toggle_complete';

        [$controller, $useCaseMock] = $this->makeAdminController();

        $useCaseMock->expects($this->never())->method('toggleCompleteStatus');
        $useCaseMock->method('searchWithoutPagination')->willReturn($this->emptySearchResult());

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    public function test_admin_toggle_complete_sets_success_message_in_french(): void
    {
        $_GET['page']   = 'toggle_complete';
        $_GET['numetu'] = 'ETU001';
        $_GET['lang']   = 'fr';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('toggleCompleteStatus')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Statut du dossier mis à jour.', $_SESSION['message'] ?? '');
    }

    public function test_admin_toggle_complete_sets_error_message_when_failed(): void
    {
        $_GET['page']   = 'toggle_complete';
        $_GET['numetu'] = 'ETU001';
        $_GET['lang']   = 'fr';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('toggleCompleteStatus')->willReturn(false);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Erreur lors de la mise à jour.', $_SESSION['message'] ?? '');
    }

    public function test_admin_toggle_complete_sets_success_message_in_english(): void
    {
        $_GET['page']   = 'toggle_complete';
        $_GET['numetu'] = 'ETU001';
        $_GET['lang']   = 'en';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('toggleCompleteStatus')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Folder status updated.', $_SESSION['message'] ?? '');
    }

    public function test_admin_toggle_complete_redirects_after_toggle(): void
    {
        $_GET['page']   = 'toggle_complete';
        $_GET['numetu'] = 'ETU001';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('toggleCompleteStatus')->willReturn(true);

        $redirectUrl = null;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            $redirectUrl = $e->getMessage();
        }

        $this->assertNotNull($redirectUrl);
        $this->assertStringContainsString('folders-admin', $redirectUrl);
        $this->assertStringContainsString('ETU001', $redirectUrl);
    }

    // =========================================================================
    // FoldersControllerAdmin — POST update_global_status
    // =========================================================================

    public function test_admin_update_global_status_calls_setFolderStatus(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'update_global_status';
        $_POST['numetu']           = 'ETU010';
        $_POST['status']           = 'instruction';

        [$controller, $useCaseMock] = $this->makeAdminController();

        $useCaseMock->expects($this->once())->method('setFolderStatus')->with('ETU010', 'instruction')->willReturn(true);
        $useCaseMock->method('getStudentDetails')->willReturn(['status' => 'depot']);

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_admin_update_global_status_returns_json_success(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'update_global_status';
        $_POST['numetu']           = 'ETU010';
        $_POST['status']           = 'instruction';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getStudentDetails')->willReturn(['status' => 'depot']);
        $useCaseMock->method('setFolderStatus')->willReturn(true);

        $jsonMsg = null;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            $jsonMsg = $e->getMessage();
        }

        $this->assertNotNull($jsonMsg);
        $this->assertStringContainsString('json:', $jsonMsg);
        $this->assertStringContainsString('"success"', $jsonMsg);
    }

    public function test_admin_update_global_status_returns_json_error_when_numetu_empty(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'update_global_status';
        $_POST['numetu']           = '';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->expects($this->never())->method('setFolderStatus');

        $jsonMsg = null;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            $jsonMsg = $e->getMessage();
        }

        $this->assertStringContainsString('false', $jsonMsg ?? '');
    }

    // =========================================================================
    // FoldersControllerAdmin — POST save_student
    // =========================================================================

    public function test_admin_save_student_calls_creerDossier_with_valid_data(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'save_student';
        $_POST['numetu']           = 'ETU099';
        $_POST['nom']              = 'Martin';
        $_POST['prenom']           = 'Alice';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getByNumetu')->willReturn(null);
        $useCaseMock->expects($this->once())->method('creerDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_admin_save_student_redirects_when_numetu_already_exists(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'save_student';
        $_POST['numetu']           = 'ETU099';
        $_POST['nom']              = 'Martin';
        $_POST['prenom']           = 'Alice';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getByNumetu')->willReturn(['NumEtu' => 'ETU099']);
        $useCaseMock->expects($this->never())->method('creerDossier');

        $redirectUrl = null;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            $redirectUrl = $e->getMessage();
        }

        $this->assertNotNull($redirectUrl);
        $this->assertStringContainsString('folders-admin', $redirectUrl);
    }

    public function test_admin_save_student_redirects_when_numetu_is_missing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'save_student';
        $_POST['numetu']           = '';
        $_POST['nom']              = 'Martin';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->expects($this->never())->method('creerDossier');

        $redirectUrl = null;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            $redirectUrl = $e->getMessage();
        }

        $this->assertNotNull($redirectUrl);
        $this->assertStringContainsString('create', $redirectUrl);
    }

    public function test_admin_save_student_sets_success_message_in_french(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'save_student';
        $_GET['lang']              = 'fr';
        $_POST['numetu']           = 'ETU099';
        $_POST['nom']              = 'Martin';
        $_POST['prenom']           = 'Alice';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getByNumetu')->willReturn(null);
        $useCaseMock->method('creerDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Dossier créé avec succès', $_SESSION['message'] ?? '');
    }

    public function test_admin_save_student_sets_success_message_in_english(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'save_student';
        $_GET['lang']              = 'en';
        $_POST['numetu']           = 'ETU099';
        $_POST['nom']              = 'Martin';
        $_POST['prenom']           = 'Alice';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getByNumetu')->willReturn(null);
        $useCaseMock->method('creerDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Folder created successfully', $_SESSION['message'] ?? '');
    }

    // =========================================================================
    // FoldersControllerAdmin — POST update_student
    // =========================================================================

    public function test_admin_update_student_calls_updateDossier(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'update_student';
        $_POST['numetu']           = 'ETU010';
        $_POST['nom']              = 'Durand';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getStudentDetails')->willReturn(['Nom' => 'OldName']);
        $useCaseMock->expects($this->once())->method('updateDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_admin_update_student_sets_success_message_in_french(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'update_student';
        $_GET['lang']              = 'fr';
        $_POST['numetu']           = 'ETU010';
        $_POST['nom']              = 'Durand';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getStudentDetails')->willReturn(['Nom' => 'OldName']);
        $useCaseMock->method('updateDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Dossier mis à jour', $_SESSION['message'] ?? '');
    }

    public function test_admin_update_student_sets_error_message_when_failed(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'update_student';
        $_GET['lang']              = 'fr';
        $_POST['numetu']           = 'ETU010';
        $_POST['nom']              = 'Durand';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->method('getStudentDetails')->willReturn(['Nom' => 'OldName']);
        $useCaseMock->method('updateDossier')->willReturn(false);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Erreur lors de la mise à jour', $_SESSION['message'] ?? '');
    }

    // =========================================================================
    // FoldersControllerAdmin — POST import_folders
    // =========================================================================

    public function test_admin_import_folders_sets_error_when_no_file_uploaded(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'import_folders';
        $_GET['lang']              = 'fr';

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->expects($this->never())->method('importFoldersFromCSV');

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertStringContainsString('Erreur', $_SESSION['message'] ?? '');
    }

    public function test_admin_import_folders_sets_error_for_unsupported_extension(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['page']              = 'import_folders';
        $_GET['lang']              = 'fr';

        $_FILES['excel_file'] = [
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => '/tmp/test.txt',
            'name'     => 'data.txt',
        ];

        [$controller, $useCaseMock] = $this->makeAdminController();
        $useCaseMock->expects($this->never())->method('importFoldersFromCSV');

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertStringContainsString('Format non supporté', $_SESSION['message'] ?? '');
    }

    // =========================================================================
    // FoldersControllerStudent — support()
    // =========================================================================

    public function test_student_support_returns_true_for_folders_student(): void
    {
        $this->assertTrue(FoldersControllerStudent::support('folders-student', 'GET'));
    }

    public function test_student_support_returns_true_for_update_my_folder(): void
    {
        $this->assertTrue(FoldersControllerStudent::support('update_my_folder', 'POST'));
    }

    public function test_student_support_returns_true_for_create_folder(): void
    {
        $this->assertTrue(FoldersControllerStudent::support('create_folder', 'POST'));
    }

    public function test_student_support_returns_false_for_other_pages(): void
    {
        $this->assertFalse(FoldersControllerStudent::support('folders-admin', 'GET'));
        $this->assertFalse(FoldersControllerStudent::support('', 'GET'));
    }

    // =========================================================================
    // FoldersControllerStudent — authentification
    // =========================================================================

    public function test_student_redirects_to_login_when_numetu_missing(): void
    {
        [$controller] = $this->makeStudentController();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/redirect:.*login/');

        $controller->control();
    }

    // =========================================================================
    // FoldersControllerStudent — GET displayFolderPage
    // =========================================================================

    public function test_student_get_calls_getStudentDetails_with_numetu(): void
    {
        $_SESSION['numetu'] = '12345';

        [$controller, $useCaseMock] = $this->makeStudentController();

        $useCaseMock->expects($this->once())->method('getStudentDetails')->with('12345')->willReturn([]);

        try { $controller->control(); } catch (\Throwable $e) {}
    }

    // =========================================================================
    // FoldersControllerStudent — POST create_folder
    // =========================================================================

    public function test_student_create_folder_calls_creerDossier_with_valid_data(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';
        $_POST['nom']              = 'Dupont';
        $_POST['prenom']           = 'Marie';
        $_POST['email_perso']      = 'marie@example.com';
        $_POST['telephone']        = '0600000000';
        $_POST['type']             = 'entrant';
        $_POST['zone']             = 'europe';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(null);
        $useCaseMock->expects($this->once())->method('creerDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_student_create_folder_redirects_when_folder_already_exists(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(['NumEtu' => '12345']);
        $useCaseMock->expects($this->never())->method('creerDossier');

        $redirectUrl = null;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            $redirectUrl = $e->getMessage();
        }

        $this->assertNotNull($redirectUrl);
        $this->assertStringContainsString('folders-student', $redirectUrl);
    }

    public function test_student_create_folder_sets_already_exists_message_in_french(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';
        $_GET['lang']              = 'fr';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(['NumEtu' => '12345']);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Vous avez déjà déposé un dossier.', $_SESSION['message'] ?? '');
    }

    public function test_student_create_folder_sets_already_exists_message_in_english(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';
        $_GET['lang']              = 'en';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(['NumEtu' => '12345']);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('You have already submitted an application.', $_SESSION['message'] ?? '');
    }

    public function test_student_create_folder_does_not_create_when_fields_missing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(null);
        $useCaseMock->expects($this->never())->method('creerDossier');

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_student_create_folder_does_not_create_when_email_invalid(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';
        $_POST['nom']              = 'Dupont';
        $_POST['prenom']           = 'Marie';
        $_POST['email_perso']      = 'not-an-email';
        $_POST['telephone']        = '0600000000';
        $_POST['type']             = 'entrant';
        $_POST['zone']             = 'europe';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(null);
        $useCaseMock->expects($this->never())->method('creerDossier');

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_student_create_folder_sets_success_message_in_french(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';
        $_GET['lang']              = 'fr';
        $_POST['nom']              = 'Dupont';
        $_POST['prenom']           = 'Marie';
        $_POST['email_perso']      = 'marie@example.com';
        $_POST['telephone']        = '0600000000';
        $_POST['type']             = 'entrant';
        $_POST['zone']             = 'europe';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(null);
        $useCaseMock->method('creerDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Votre demande a été déposée avec succès.', $_SESSION['message'] ?? '');
    }

    public function test_student_create_folder_sets_success_message_in_english(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';
        $_GET['lang']              = 'en';
        $_POST['nom']              = 'Dupont';
        $_POST['prenom']           = 'Marie';
        $_POST['email_perso']      = 'marie@example.com';
        $_POST['telephone']        = '0600000000';
        $_POST['type']             = 'entrant';
        $_POST['zone']             = 'europe';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(null);
        $useCaseMock->method('creerDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Application submitted successfully.', $_SESSION['message'] ?? '');
    }

    public function test_student_create_folder_sets_error_message_on_failure(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'create_folder';
        $_GET['lang']              = 'fr';
        $_POST['nom']              = 'Dupont';
        $_POST['prenom']           = 'Marie';
        $_POST['email_perso']      = 'marie@example.com';
        $_POST['telephone']        = '0600000000';
        $_POST['type']             = 'entrant';
        $_POST['zone']             = 'europe';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(null);
        $useCaseMock->method('creerDossier')->willReturn(false);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Erreur lors du dépôt de la demande.', $_SESSION['message'] ?? '');
    }

    // =========================================================================
    // FoldersControllerStudent — POST update_my_folder
    // =========================================================================

    public function test_student_update_folder_calls_updateDossier(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'update_my_folder';
        $_POST['email_perso']      = 'marie@example.com';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(['Prenom' => 'Marie', 'Nom' => 'Dupont', 'DateLimite' => null]);
        $useCaseMock->expects($this->once())->method('updateDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}
    }

    public function test_student_update_folder_redirects_when_email_missing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'update_my_folder';
        $_GET['lang']              = 'fr';
        $_POST['email_perso']      = '';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn([]);
        $useCaseMock->expects($this->never())->method('updateDossier');

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame("L'email personnel est requis.", $_SESSION['message'] ?? '');
    }

    public function test_student_update_folder_sets_success_message_in_french(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'update_my_folder';
        $_GET['lang']              = 'fr';
        $_POST['email_perso']      = 'marie@example.com';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(['Prenom' => 'Marie', 'Nom' => 'Dupont', 'DateLimite' => null]);
        $useCaseMock->method('updateDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Dossier mis à jour avec succès.', $_SESSION['message'] ?? '');
    }

    public function test_student_update_folder_sets_success_message_in_english(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'update_my_folder';
        $_GET['lang']              = 'en';
        $_POST['email_perso']      = 'marie@example.com';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(['Prenom' => 'Marie', 'Nom' => 'Dupont', 'DateLimite' => null]);
        $useCaseMock->method('updateDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Folder updated successfully.', $_SESSION['message'] ?? '');
    }

    public function test_student_update_folder_sets_error_message_on_failure(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'update_my_folder';
        $_GET['lang']              = 'fr';
        $_POST['email_perso']      = 'marie@example.com';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn(['Prenom' => 'Marie', 'Nom' => 'Dupont', 'DateLimite' => null]);
        $useCaseMock->method('updateDossier')->willReturn(false);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertSame('Erreur lors de la mise à jour du dossier.', $_SESSION['message'] ?? '');
    }

    public function test_student_update_folder_blocks_file_uploads_when_deadline_passed(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['numetu']        = '12345';
        $_GET['page']              = 'update_my_folder';
        $_GET['lang']              = 'fr';
        $_POST['email_perso']      = 'marie@example.com';

        [$controller, $useCaseMock] = $this->makeStudentController();
        $useCaseMock->method('getStudentDetails')->willReturn([
            'Prenom'     => 'Marie',
            'Nom'        => 'Dupont',
            'DateLimite' => '2000-01-01',
        ]);
        $useCaseMock->expects($this->once())->method('updateDossier')->willReturn(true);

        try { $controller->control(); } catch (\RuntimeException $e) {}

        $this->assertStringContainsString('date limite dépassée', $_SESSION['message'] ?? '');
    }
}
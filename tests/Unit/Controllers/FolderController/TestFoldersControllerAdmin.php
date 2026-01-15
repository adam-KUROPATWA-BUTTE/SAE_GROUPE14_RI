<?php

namespace Tests\Unit\Controllers\FolderController;

// Load app autoloader and testing stubs (defined with their own namespaces)
require_once __DIR__ . '/../../../../Autoloader.php';
require_once __DIR__ . '/../../../Fixtures/Stubs/Model/ModelFolderAdminStub.php';
require_once __DIR__ . '/../../../Fixtures/Stubs/View/ViewFoldersPageAdminStub.php';
require_once __DIR__ . '/../../../Fixtures/Stubs/Controller/ControllerNamespaceFunctions.php';

use Controllers\FolderController\FoldersControllerAdmin;
use PHPUnit\Framework\TestCase;
use View\Folder\FoldersPageAdmin as ViewStub;
use Model\Folder\FolderAdmin as ModelStub;

class TestFoldersControllerAdmin extends TestCase
{
	protected function setUp(): void
	{
		$_GET = [];
		$_POST = [];
		$_FILES = [];
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$GLOBALS['__captured_headers'] = [];

		ViewStub::$lastArgs = [];
		ModelStub::$studentDetailsReturn = null;
		ModelStub::$getStudentDetailsCalledWith = null;

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_unset();
			session_destroy();
		}
	}

	public function testSupportRecognizesSupportedPages(): void
	{
		$supported = ['folders', 'save_student', 'folders-admin', 'toggle_complete', 'update_student'];
		foreach ($supported as $page) {
			$this->assertTrue(FoldersControllerAdmin::support($page, 'GET'));
		}
		$this->assertFalse(FoldersControllerAdmin::support('unknown_page', 'GET'));
	}

	public function testControlListDelegatesToViewWithDefaults(): void
	{
		$_GET = [
			// no 'page' provided -> defaults to 'folders'
		];
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$controller = new FoldersControllerAdmin();

		ob_start();
		$controller->control();
		$output = ob_get_clean();

		// Ensure view was constructed (output may include deprecations/noise)
		$this->assertNotEmpty(ViewStub::$lastArgs, 'View did not receive constructor args');
		[$action, $filters, $page, $perPage, $message, $lang, $studentData] = ViewStub::$lastArgs;

		$this->assertSame('list', $action);
		$this->assertSame(1, $page);
		$this->assertSame(10, $perPage);
		$this->assertSame('', $message);
		$this->assertSame('fr', $lang);
		$this->assertNull($studentData);

		$this->assertSame([
			'type' => 'all',
			'zone' => 'all',
			'stage' => 'all',
			'etude' => 'all',
			'search' => '',
			'complet' => 'all',
			'date_debut' => null,
			'date_fin' => null,
			'tri_date' => 'DESC',
		], $filters);
	}

	public function testControlViewFetchesStudentAndPassesToView(): void
	{
		$_GET = [
			'page' => 'folders-admin',
			'action' => 'view',
			'numetu' => '12345',
			'lang' => 'en',
		];

		ModelStub::$studentDetailsReturn = [
			'NumEtu' => '12345',
			'Nom' => 'Doe',
			'Prenom' => 'John',
		];

		$controller = new FoldersControllerAdmin();

		ob_start();
		$controller->control();
		ob_end_clean();

		$this->assertSame('12345', ModelStub::$getStudentDetailsCalledWith);

		$this->assertNotEmpty(ViewStub::$lastArgs);
		$this->assertSame('en', ViewStub::$lastArgs[5]);
		$this->assertSame(ModelStub::$studentDetailsReturn, ViewStub::$lastArgs[6]);
	}

	public function testControlAppliesProvidedFiltersAndLang(): void
	{
		$_GET = [
			'page' => 'folders-admin',
			'action' => 'list',
			'Type' => 'entrant',
			'Zone' => 'europe',
			'search' => 'doe',
			'complet' => '1',
			'date_debut' => '2000-01-01',
			'date_fin' => '2001-01-01',
			'tri_date' => 'ASC',
			'lang' => 'en',
			'p' => '2',
		];

		$controller = new FoldersControllerAdmin();

		ob_start();
		$controller->control();
		ob_end_clean();

		[$action, $filters, $page, $perPage, $message, $lang] = ViewStub::$lastArgs;
		$this->assertSame('list', $action);
		$this->assertSame(2, $page);
		$this->assertSame('en', $lang);
		$this->assertSame([
			'type' => 'entrant',
			'zone' => 'europe',
			'stage' => 'all',
			'etude' => 'all',
			'search' => 'doe',
			'complet' => '1',
			'date_debut' => '2000-01-01',
			'date_fin' => '2001-01-01',
			'tri_date' => 'ASC',
		], $filters);
	}

	public function testControlRejectsSaveWhenMissingNumetu(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_GET['page'] = 'save_student';
		$_POST = ['nom' => 'Dupont', 'prenom' => 'Jean']; // numetu manquant

		$controller = new FoldersControllerAdmin();

		$bufferStarted = false;
		try {
			ob_start();
			$bufferStarted = true;
			$controller->control();
		} catch (\RuntimeException $e) {
			// Expected: header() stub throws to avoid real exit/redirect
		} finally {
			if (ob_get_level() > 0) {
				ob_end_clean();
			}
		}

		$this->assertNotEmpty($_SESSION['message']);
		$this->assertIsString($_SESSION['message']);
		$this->assertStringContainsString('requis', $_SESSION['message']);
	}
    
}


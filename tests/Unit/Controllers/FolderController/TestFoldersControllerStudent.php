<?php

namespace Tests\Unit\Controllers\FolderController;

require_once __DIR__ . '/../../../../Autoloader.php';
require_once __DIR__ . '/../../../Fixtures/Stubs/Model/ModelFolderStudentStub.php';
require_once __DIR__ . '/../../../Fixtures/Stubs/View/ViewFoldersPageStudentStub.php';
require_once __DIR__ . '/../../../Fixtures/Stubs/Controller/ControllerSiteNamespaceFunctions.php';

use Controllers\site\FolderController\FoldersControllerStudent;
use PHPUnit\Framework\TestCase;
use View\Folder\FoldersPageStudent as ViewStub;
use Model\Folder\FolderStudent as ModelStub;

class TestFoldersControllerStudent extends TestCase
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
		session_start();
	}

	public function testSupportRecognizesSupportedPages(): void
	{
		$supported = ['folders-student', 'update_student', 'create_folder'];
		foreach ($supported as $page) {
			$this->assertTrue(FoldersControllerStudent::support($page, 'GET'));
		}
		$this->assertFalse(FoldersControllerStudent::support('unknown', 'GET'));
	}

	public function testControlRendersWithStudentDataAndClearsMessage(): void
	{
		$_SESSION['numetu'] = 'S123';
		$_SESSION['message'] = 'hello';
		$_GET['lang'] = 'en';

		ModelStub::$studentDetailsReturn = ['NumEtu' => 'S123', 'Nom' => 'Doe'];

		$controller = new FoldersControllerStudent();

		ob_start();
		$controller->control();
		$output = ob_get_clean();

		$this->assertNotEmpty(ViewStub::$lastArgs, 'View not constructed');
		[$studentData, $numetu, $action, $message, $lang] = ViewStub::$lastArgs;

		$this->assertSame(ModelStub::$studentDetailsReturn, $studentData);
		$this->assertSame('S123', $numetu);
		$this->assertSame('view', $action);
		$this->assertSame('hello', $message);
		$this->assertSame('en', $lang);

		$this->assertArrayNotHasKey('message', $_SESSION, 'Flash message should be cleared');
	}
}


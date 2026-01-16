<?php

namespace Tests\Unit\Controllers\PartnersController;

require_once __DIR__ . '/../../../Fixtures/Stubs/View/ViewPartnersPageStudentStub.php';
require_once __DIR__ . '/../../../../Autoloader.php';

use Controllers\PartnersController\PartnersControllerStudent;
use PHPUnit\Framework\TestCase;
use View\Partners\PartnersPageStudent as ViewStub;

class TestPartnersControllerStudent extends TestCase
{
	protected function setUp(): void
	{
		$_GET = [];
		$_POST = [];
		$_FILES = [];
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$GLOBALS['__captured_headers'] = [];

		ViewStub::$lastArgs = [];

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_unset();
			session_destroy();
		}
	}

	public function testSupportRecognizesPartnersStudent(): void
	{
		$this->assertTrue(PartnersControllerStudent::support('partners-student', 'GET'));
		$this->assertFalse(PartnersControllerStudent::support('other', 'GET'));
	}

	public function testControlRendersDefaultLangFr(): void
	{
		$controller = new PartnersControllerStudent();

		ob_start();
		$controller->control();
		ob_end_clean();

		$this->assertNotEmpty(ViewStub::$lastArgs, 'View not constructed');
		[$titre, $lang] = ViewStub::$lastArgs;

		$this->assertSame('Universités Partenaires', $titre);
		$this->assertSame('fr', $lang);
	}

	public function testControlRendersEnglishTitleWhenLangEn(): void
	{
		$_GET['lang'] = 'en';

		$controller = new PartnersControllerStudent();

		ob_start();
		$controller->control();
		ob_end_clean();

		[$titre, $lang] = ViewStub::$lastArgs;
		$this->assertSame('Partner Universities', $titre);
		$this->assertSame('en', $lang);
	}
}


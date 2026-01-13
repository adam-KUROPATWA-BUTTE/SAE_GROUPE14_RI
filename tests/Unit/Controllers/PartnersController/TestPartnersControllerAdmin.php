<?php

namespace Tests\Unit\Controllers\PartnersController;

require_once __DIR__ . '/../../../Fixtures/Stubs/View/ViewPartnersPageAdminStub.php';
require_once __DIR__ . '/../../../../Autoloader.php';

use Controllers\PartnersController\PartnersControllerAdmin;
use PHPUnit\Framework\TestCase;
use View\Partners\PartnersPageAdmin as ViewStub;

class TestPartnersControllerAdmin extends TestCase
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

	public function testSupportRecognizesPartnersAdmin(): void
	{
		$this->assertTrue(PartnersControllerAdmin::support('partners-admin', 'GET'));
		$this->assertFalse(PartnersControllerAdmin::support('other', 'GET'));
	}

	public function testControlRendersDefaultLangFr(): void
	{
		$controller = new PartnersControllerAdmin();

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

		$controller = new PartnersControllerAdmin();

		ob_start();
		$controller->control();
		ob_end_clean();

		[$titre, $lang] = ViewStub::$lastArgs;
		$this->assertSame('Partner Universities', $titre);
		$this->assertSame('en', $lang);
	}
}


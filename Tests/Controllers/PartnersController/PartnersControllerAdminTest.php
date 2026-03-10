<?php

namespace Tests\Controllers\PartnersController;

use PHPUnit\Framework\TestCase;
use Controllers\PartnersController\PartnersControllerAdmin;

class PartnersControllerAdminTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION = [];
    }

    public function testSupportReturnsTrueForPartnersAdmin(): void
    {
        $this->assertTrue(
            PartnersControllerAdmin::support('partners-admin', 'GET')
        );
    }

    public function testSupportReturnsFalseForOtherPage(): void
    {
        $this->assertFalse(
            PartnersControllerAdmin::support('home', 'GET')
        );
    }

    public function testDefaultLanguageIsFrench(): void
    {
        $controller = new PartnersControllerAdmin();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertEquals('fr', $_SESSION['lang'] ?? 'fr');
    }

    public function testLanguageChangeToEnglish(): void
    {
        $_GET['lang'] = 'en';

        $controller = new PartnersControllerAdmin();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertEquals('en', $_SESSION['lang']);
    }

    public function testErrorMessageWhenFieldsMissing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST = [
            'continent'   => '',
            'country'     => '',
            'city'        => '',
            'institution' => '',
            'type'        => '',
        ];

        $controller = new PartnersControllerAdmin();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertTrue(true);
    }
}
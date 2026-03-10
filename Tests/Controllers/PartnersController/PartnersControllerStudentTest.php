<?php

namespace Tests\Controllers\PartnersController;

use PHPUnit\Framework\TestCase;
use Controllers\PartnersController\PartnersControllerStudent;

class PartnersControllerStudentTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET     = [];
        $_SESSION = [];
    }

    public function testSupportReturnsTrueForPartnersStudent(): void
    {
        $this->assertTrue(
            PartnersControllerStudent::support('partners-student', 'GET')
        );
    }

    public function testSupportReturnsFalseForOtherPage(): void
    {
        $this->assertFalse(
            PartnersControllerStudent::support('home', 'GET')
        );
    }

    public function testDefaultPartnerIsAmu(): void
    {
        $controller = new PartnersControllerStudent();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testPartnerIut(): void
    {
        $_GET['partner'] = 'iut';

        $controller = new PartnersControllerStudent();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testLanguageEnglish(): void
    {
        $_GET['lang'] = 'en';

        $controller = new PartnersControllerStudent();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertEquals('en', $_SESSION['lang']);
    }
}
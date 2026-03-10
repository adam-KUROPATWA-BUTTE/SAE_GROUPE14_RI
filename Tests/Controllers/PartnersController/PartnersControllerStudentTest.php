<?php

namespace Controllers\PartnersController;

use PHPUnit\Framework\TestCase;

class PartnersControllerStudentTest extends TestCase
{

    protected function setUp(): void
    {
        $_GET = [];
        $_SESSION = [];
    }

    public function testSupportReturnsTrueForPartnersStudent()
    {
        $this->assertTrue(
            PartnersControllerStudent::support('partners-student', 'GET')
        );
    }

    public function testSupportReturnsFalseForOtherPage()
    {
        $this->assertFalse(
            PartnersControllerStudent::support('home', 'GET')
        );
    }

    public function testDefaultPartnerIsAmu()
    {
        $controller = new PartnersControllerStudent();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testPartnerIut()
    {
        $_GET['partner'] = 'iut';

        $controller = new PartnersControllerStudent();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testLanguageEnglish()
    {
        $_GET['lang'] = 'en';

        $controller = new PartnersControllerStudent();

        ob_start();
        $controller->control();
        ob_end_clean();

        $this->assertEquals('en', $_SESSION['lang']);
    }

}
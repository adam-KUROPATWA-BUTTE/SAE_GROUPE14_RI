<?php

namespace Controllers\WebPlanController;

use PHPUnit\Framework\TestCase;

class WebPlanControllerStudentTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testSupportReturnsTrue(): void
    {
        $this->assertTrue(
            WebPlanControllerStudent::support('web_plan-student', 'GET')
        );
    }

    public function testSupportReturnsFalseWrongPage(): void
    {
        $this->assertFalse(
            WebPlanControllerStudent::support('home', 'GET')
        );
    }

    public function testSupportReturnsFalseWrongMethod(): void
    {
        $this->assertFalse(
            WebPlanControllerStudent::support('web_plan-student', 'POST')
        );
    }

    public function testControllerInstanceCreation(): void
    {
        $controller = new WebPlanControllerStudent();

        $this->assertInstanceOf(
            WebPlanControllerStudent::class,
            $controller
        );
    }
}
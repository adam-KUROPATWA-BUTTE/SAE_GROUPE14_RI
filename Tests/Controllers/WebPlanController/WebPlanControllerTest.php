<?php

namespace Tests\Controllers\WebPlanController;

use PHPUnit\Framework\TestCase;
use Controllers\WebPlanController\WebPlanController;

class WebPlanControllerTest extends TestCase
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
            WebPlanController::support('web_plan', 'GET')
        );
    }

    public function testSupportReturnsFalseWrongPage(): void
    {
        $this->assertFalse(
            WebPlanController::support('home', 'GET')
        );
    }

    public function testSupportReturnsFalseWrongMethod(): void
    {
        $this->assertFalse(
            WebPlanController::support('web_plan', 'POST')
        );
    }

    public function testControllerInstanceCreation(): void
    {
        $controller = new WebPlanController();

        $this->assertInstanceOf(
            WebPlanController::class,
            $controller
        );
    }
}
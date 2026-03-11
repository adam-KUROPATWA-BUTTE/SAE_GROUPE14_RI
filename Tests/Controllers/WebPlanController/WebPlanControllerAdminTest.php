<?php

namespace Tests\Controllers\WebPlanController;

use PHPUnit\Framework\TestCase;
use Controllers\WebPlanController\WebPlanControllerAdmin;

class WebPlanControllerAdminTest extends TestCase
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
            WebPlanControllerAdmin::support('web_plan-admin', 'GET')
        );
    }

    public function testSupportReturnsFalseWrongPage(): void
    {
        $this->assertFalse(
            WebPlanControllerAdmin::support('web_plan', 'GET')
        );
    }

    public function testSupportReturnsFalseWrongMethod(): void
    {
        $this->assertFalse(
            WebPlanControllerAdmin::support('web_plan-admin', 'POST')
        );
    }

    public function testControllerInstanceCreation(): void
    {
        $controller = new WebPlanControllerAdmin();

        $this->assertInstanceOf(
            WebPlanControllerAdmin::class,
            $controller
        );
    }
}
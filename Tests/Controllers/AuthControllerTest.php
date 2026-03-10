<?php

namespace Controllers;

use PHPUnit\Framework\TestCase;
class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testSupportLogin(): void
    {
        $this->assertTrue(
            AuthController::support('login', 'GET')
        );
    }

    public function testSupportRegister(): void
    {
        $this->assertTrue(
            AuthController::support('register', 'GET')
        );
    }

    public function testSupportForgotPassword(): void
    {
        $this->assertTrue(
            AuthController::support('forgot_password', 'GET')
        );
    }

    public function testSupportReturnsFalse(): void
    {
        $this->assertFalse(
            AuthController::support('home', 'GET')
        );
    }

}
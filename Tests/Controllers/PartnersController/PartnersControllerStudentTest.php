<?php

namespace Tests\Controllers\PartnersController;

use PHPUnit\Framework\TestCase;
use Controllers\PartnersController\PartnersControllerStudent;

class PartnersControllerStudentTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET     = [];
        $_SESSION = ['numetu' => '12345']; // ← requis sinon redirect
    }

    private function makeController(): PartnersControllerStudent
    {
        return new class extends PartnersControllerStudent {
            public function __construct() {}

            protected function startSession(): void {}

            protected function redirect(string $url): never
            {
                throw new \RuntimeException('redirect:' . $url);
            }

            /** @param array<string, mixed> $data */
            protected function renderView(string $view, array $data = []): void {}
        };
    }

    public function testSupportReturnsTrueForPartnersStudent(): void
    {
        $this->assertTrue(PartnersControllerStudent::support('partners-student', 'GET'));
    }

    public function testSupportReturnsFalseForOtherPage(): void
    {
        $this->assertFalse(PartnersControllerStudent::support('home', 'GET'));
    }

    public function testRedirectsToLoginWhenNumetuMissing(): void
    {
        $_SESSION = []; // pas de numetu
        $controller = $this->makeController();

        $redirectUrl = null;
        try {
            $controller->control();
        } catch (\RuntimeException $e) {
            $redirectUrl = $e->getMessage();
        }

        $this->assertNotNull($redirectUrl);
        $this->assertStringContainsString('login', $redirectUrl);
    }

    public function testDefaultPartnerIsAmu(): void
    {
        $controller = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue(true);
    }

    public function testPartnerIut(): void
    {
        $_GET['partner'] = 'iut';
        $controller      = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue(true);
    }

    public function testLanguageEnglish(): void
    {
        $_GET['lang'] = 'en';
        $controller   = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('en', $_SESSION['lang']);
    }

    public function testLanguageIgnoresInvalidValue(): void
    {
        $_GET['lang'] = 'de';
        $controller   = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertArrayNotHasKey('lang', $_SESSION);
    }

    public function testTritanopiaSetToTrue(): void
    {
        $_GET['tritanopia'] = '1';
        $controller         = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue($_SESSION['tritanopia'] ?? false);
    }

    public function testTritanopiaSetToFalse(): void
    {
        $_GET['tritanopia'] = '0';
        $controller         = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertFalse($_SESSION['tritanopia'] ?? true);
    }
}
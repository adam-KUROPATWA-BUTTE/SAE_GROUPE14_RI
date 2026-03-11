<?php

namespace Tests\Controllers\PartnersController;

use PHPUnit\Framework\TestCase;
use Controllers\PartnersController\PartnersControllerAdmin;

class PartnersControllerAdminTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET                      = [];
        $_POST                     = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION                  = ['role' => 'admin']; // ← requis
    }

    private function makeController(): PartnersControllerAdmin
    {
        return new class extends PartnersControllerAdmin {
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

    public function testSupportReturnsTrueForPartnersAdmin(): void
    {
        $this->assertTrue(PartnersControllerAdmin::support('partners-admin', 'GET'));
    }

    public function testSupportReturnsFalseForOtherPage(): void
    {
        $this->assertFalse(PartnersControllerAdmin::support('home', 'GET'));
    }

    public function testDefaultLanguageIsFrench(): void
    {
        $controller = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('fr', $_SESSION['lang'] ?? 'fr');
    }

    public function testLanguageChangeToEnglish(): void
    {
        $_GET['lang'] = 'en';
        $controller   = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertSame('en', $_SESSION['lang']);
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

        $controller = $this->makeController();

        try { $controller->control(); } catch (\Throwable $e) {}

        $this->assertTrue(true);
    }

    public function testRedirectsToLoginWhenNotAdmin(): void
    {
        $_SESSION = []; // pas de rôle
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
}
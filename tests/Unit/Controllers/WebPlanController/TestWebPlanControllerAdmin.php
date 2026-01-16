<?php

namespace Tests\Unit\Controllers\WebPlanController;

require_once __DIR__ . '/../../../Fixtures/Stubs/ModelWebPlanStub.php';
require_once __DIR__ . '/../../../Fixtures/Stubs/ViewWebPlanPageAdminStub.php';
require_once __DIR__ . '/../../../../Autoloader.php';

use Controllers\WebPlanController\WebPlanControllerAdmin;
use PHPUnit\Framework\TestCase;
use View\WebPlan\WebPlanPageAdmin as ViewStub;
use Model\WebPlan as ModelStub;

/**
 * Tests pour WebPlanControllerAdmin
 * 
 * Ce qu'on teste :
 * 1. support() : vérifie que le contrôleur reconnaît uniquement la page 'web_plan-admin'
 * 2. control() avec langue par défaut (fr) : vérifie que les liens du modèle sont récupérés et passés à la vue
 * 3. control() avec langue anglaise (en) : vérifie que le paramètre lang est bien transmis à la vue
 */
class TestWebPlanControllerAdmin extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['__captured_headers'] = [];

        ViewStub::$lastArgs = [];
        ModelStub::$linksAdminReturn = null;
    }

    /**
     * Test : le contrôleur supporte uniquement la page 'web_plan-admin'
     * Objectif : vérifier le routing correct
     */
    public function testSupportRecognizesWebPlanAdmin(): void
    {
        $this->assertTrue(WebPlanControllerAdmin::support('web_plan-admin', 'GET'));
        $this->assertFalse(WebPlanControllerAdmin::support('other', 'GET'));
    }

    /**
     * Test : le contrôleur récupère les liens et les passe à la vue avec langue par défaut
     * Objectif : vérifier que le flux de données modèle->vue fonctionne correctement
     */
    public function testControlFetchesLinksAndRendersWithDefaultLang(): void
    {
        // Arrange : préparer des liens factices
        $mockLinks = [
            ['title' => 'Accueil', 'url' => '/'],
            ['title' => 'Contact', 'url' => '/contact']
        ];
        ModelStub::$linksAdminReturn = $mockLinks;

        // Act : exécuter le contrôleur
        $controller = new WebPlanControllerAdmin();
        ob_start();
        $controller->control();
        ob_end_clean();

        // Assert : vérifier que la vue a reçu les bons arguments
        $this->assertNotEmpty(ViewStub::$lastArgs, 'View not constructed');
        [$links, $lang] = ViewStub::$lastArgs;

        $this->assertSame($mockLinks, $links, 'Links should be passed from model to view');
        $this->assertSame('fr', $lang, 'Default language should be fr');
    }

    /**
     * Test : le contrôleur passe la langue anglaise à la vue quand lang=en
     * Objectif : vérifier la gestion multi-langue
     */
    public function testControlPassesEnglishLangWhenSpecified(): void
    {
        $_GET['lang'] = 'en';
        ModelStub::$linksAdminReturn = [];

        $controller = new WebPlanControllerAdmin();
        ob_start();
        $controller->control();
        ob_end_clean();

        [$links, $lang] = ViewStub::$lastArgs;
        $this->assertSame('en', $lang, 'Language should be en when specified in GET');
    }
}

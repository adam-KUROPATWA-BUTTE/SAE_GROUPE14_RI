<?php

namespace Tests\Unit\Service\Email;

use PHPUnit\Framework\TestCase;
use Service\Email\EmailReminderService;

class EmailReminderServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_ENV['MAILJET_API_KEY'] = 'test_api_key';
        $_ENV['MAILJET_SECRET_KEY'] = 'test_secret_key';
    }

    /**
     * Test que la méthode buildMessage génère du HTML valide
     */
    public function testBuildMessageReturnsValidHtml(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $dossierId = 123;
        $studentName = 'Jean Dupont';
        $itemsToComplete = ['Pièce d\'identité', 'Certificat de scolarité'];

        $result = $method->invoke(null, $dossierId, $studentName, $itemsToComplete);

        $this->assertIsString($result);
        $this->assertStringContainsString('<!DOCTYPE html>', $result);
        $this->assertStringContainsString('Jean Dupont', $result);
        $this->assertStringContainsString('123', $result);
    }

    /**
     * Test que les caractères dangereux sont échappés (protection XSS)
     */
    public function testBuildMessageSanitizesInput(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $dossierId = 123;
        $studentName = '<script>alert("XSS")</script>Jean';
        $itemsToComplete = ['<b>Item 1</b>', 'Item "2"'];

        $result = $method->invoke(null, $dossierId, $studentName, $itemsToComplete);

        // Vérifier que le script est échappé
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
        
        // Vérifier que les balises HTML dans les items sont échappées
        $this->assertStringContainsString('&lt;b&gt;', $result);
    }

    /**
     * Test que tous les éléments requis sont présents dans le message
     */
    public function testBuildMessageContainsRequiredElements(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $dossierId = 999;
        $studentName = 'Test Student';
        $itemsToComplete = ['Document 1', 'Document 2'];

        $result = $method->invoke(null, $dossierId, $studentName, $itemsToComplete);

        // Vérifier les éléments essentiels
        $this->assertStringContainsString('Test Student', $result);
        $this->assertStringContainsString('999', $result);
        $this->assertStringContainsString('Document 1', $result);
        $this->assertStringContainsString('Document 2', $result);
        $this->assertStringContainsString('https://ri-amu.app', $result);
        $this->assertStringContainsString('Accéder à mon dossier', $result);
    }

    /**
     * Test avec une liste vide de documents
     */
    public function testBuildMessageWithEmptyItemsList(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $dossierId = 111;
        $studentName = 'Student Without Items';
        $itemsToComplete = [];

        $result = $method->invoke(null, $dossierId, $studentName, $itemsToComplete);

        // Vérifier qu'il y a un message par défaut
        $this->assertStringContainsString('compléter les pièces manquantes', $result);
        
        // Vérifier qu'il n'y a pas de liste <ul>
        $this->assertStringNotContainsString('<ul', $result);
    }

    /**
     * Test avec un nom d'étudiant vide
     */
    public function testBuildMessageWithEmptyStudentName(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $dossierId = 222;
        $studentName = '';
        $itemsToComplete = ['Document'];

        $result = $method->invoke(null, $dossierId, $studentName, $itemsToComplete);

        // Vérifier que le message est généré même sans nom
        $this->assertIsString($result);
        $this->assertStringContainsString('<!DOCTYPE html>', $result);
    }

    /**
     * Test que l'URL du dossier est correctement encodée
     */
    public function testBuildMessageEncodesUrlProperly(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $dossierId = '123&test=value';
        $studentName = 'Test';
        $itemsToComplete = [];

        $result = $method->invoke(null, $dossierId, $studentName, $itemsToComplete);

        // Vérifier que les caractères spéciaux dans l'URL sont encodés
        $this->assertStringContainsString('id=123%26test%3Dvalue', $result);
    }

    /**
     * Test que le template contient les bonnes balises meta
     */
    public function testBuildMessageContainsMetaTags(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $result = $method->invoke(null, 1, 'Test', []);

        $this->assertStringContainsString('<meta charset="utf-8">', $result);
        $this->assertStringContainsString('lang="fr"', $result);
    }

    /**
     * Test avec des caractères Unicode
     */
    public function testBuildMessageHandlesUnicodeCharacters(): void
    {
        $reflection = new \ReflectionClass(EmailReminderService::class);
        $method = $reflection->getMethod('buildMessage');
        $method->setAccessible(true);

        $dossierId = 1;
        $studentName = 'François Müller';
        $itemsToComplete = ['Pièce d\'identité', 'Attestation'];

        $result = $method->invoke(null, $dossierId, $studentName, $itemsToComplete);

        // Vérifier que les caractères accentués sont préservés
        $this->assertStringContainsString('François Müller', $result);
        $this->assertStringContainsString('Pièce d', $result);
    }
}
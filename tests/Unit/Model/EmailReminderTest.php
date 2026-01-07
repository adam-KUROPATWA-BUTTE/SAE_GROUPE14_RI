<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Model\EmailReminder;

class EmailReminderTest extends TestCase
{
    private EmailReminder $model;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        \Database::resetInstance();
        $this->model = new RelanceModel();
        $this->pdo = \Database::getInstance()->getConnection();
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        \Database::resetInstance();
        parent::tearDown();
    }

    private function seedTestData(): void
    {
        // Créer des étudiants de test
        $this->pdo->exec("
            INSERT INTO etudiants (id, nom, prenom, email) VALUES
            (1, 'Dupont', 'Jean', 'jean.dupont@example.com'),
            (2, 'Martin', 'Marie', 'marie.martin@example.com'),
            (3, 'Bernard', 'Pierre', 'pierre.bernard@example.com')
        ");

        // Créer des dossiers de test
        $this->pdo->exec("
            INSERT INTO dossiers (id, etudiant_id, email_responsable, iscomplet) VALUES
            (1, 1, 'responsable1@example.com', 0),
            (2, 2, NULL, 0),
            (3, 3, 'responsable3@example.com', 1)
        ");
    }

    public function testGetIncompleteDossiersReturnsOnlyIncomplete(): void
    {
        $result = $this->model->getIncompleteDossiers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        foreach ($result as $dossier) {
            $this->assertArrayHasKey('dossier_id', $dossier);
            $this->assertArrayHasKey('etudiant_id', $dossier);
            $this->assertArrayHasKey('email_responsable', $dossier);
            $this->assertArrayHasKey('email_etudiant', $dossier);
            $this->assertArrayHasKey('nom', $dossier);
            $this->assertArrayHasKey('prenom', $dossier);
        }
    }

    public function testGetIncompleteDossiersStructure(): void
    {
        $result = $this->model->getIncompleteDossiers();

        $this->assertNotEmpty($result);
        $firstDossier = $result[0];

        $this->assertEquals(1, $firstDossier['dossier_id']);
        $this->assertEquals(1, $firstDossier['etudiant_id']);
        $this->assertEquals('responsable1@example.com', $firstDossier['email_responsable']);
        $this->assertEquals('jean.dupont@example.com', $firstDossier['email_etudiant']);
        $this->assertEquals('Dupont', $firstDossier['nom']);
        $this->assertEquals('Jean', $firstDossier['prenom']);
    }

    public function testInsertRelanceWithAllParameters(): void
    {
        $dossierId = 1;
        $message = 'Relance automatique envoyée';
        $envoyePar = 42;

        $result = $this->model->insertRelance($dossierId, $message, $envoyePar);

        $this->assertTrue($result);

        // Vérifier que la relance a été insérée
        $stmt = $this->pdo->prepare("SELECT * FROM relances WHERE dossier_id = ?");
        $stmt->execute([$dossierId]);
        $relance = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($relance);
        $this->assertEquals($dossierId, $relance['dossier_id']);
        $this->assertEquals($message, $relance['message']);
        $this->assertEquals($envoyePar, $relance['envoye_par']);
    }

    public function testInsertRelanceWithNullEnvoyePar(): void
    {
        $dossierId = 2;
        $message = 'Relance automatique par cron';

        $result = $this->model->insertRelance($dossierId, $message, null);

        $this->assertTrue($result);

        $stmt = $this->pdo->prepare("SELECT * FROM relances WHERE dossier_id = ?");
        $stmt->execute([$dossierId]);
        $relance = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($relance);
        $this->assertNull($relance['envoye_par']);
    }

    public function testLastRelanceWithinDaysReturnsTrueWhenRecentRelance(): void
    {
        // Insérer une relance récente
        $dossierId = 1;
        $this->model->insertRelance($dossierId, 'Test relance', null);

        $result = $this->model->lastRelanceWithinDays($dossierId, 7);

        $this->assertTrue($result);
    }

    public function testLastRelanceWithinDaysReturnsFalseWhenNoRelance(): void
    {
        $dossierId = 999; // Dossier sans relance

        $result = $this->model->lastRelanceWithinDays($dossierId, 7);

        $this->assertFalse($result);
    }

    public function testLastRelanceWithinDaysReturnsFalseWhenOldRelance(): void
    {
        $dossierId = 1;
        
        // Insérer une vieille relance (simuler en modifiant manuellement)
        $this->pdo->exec("
            INSERT INTO relances (dossier_id, message, date_relance) 
            VALUES ($dossierId, 'Vieille relance', datetime('now', '-10 days'))
        ");

        $result = $this->model->lastRelanceWithinDays($dossierId, 7);

        $this->assertFalse($result);
    }

    public function testMultipleRelancesForSameDossier(): void
    {
        $dossierId = 1;

        $result1 = $this->model->insertRelance($dossierId, 'Première relance', null);
        $result2 = $this->model->insertRelance($dossierId, 'Deuxième relance', 1);

        $this->assertTrue($result1);
        $this->assertTrue($result2);

        // Vérifier qu'il y a bien 2 relances
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM relances WHERE dossier_id = ?");
        $stmt->execute([$dossierId]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(2, $count);
    }

    public function testGetIncompleteDossiersWithNoData(): void
    {
        // Supprimer toutes les données
        $this->pdo->exec("DELETE FROM dossiers");
        $this->pdo->exec("DELETE FROM etudiants");

        $result = $this->model->getIncompleteDossiers();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
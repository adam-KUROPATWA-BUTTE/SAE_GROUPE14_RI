<?php

namespace Tests\Model\UseCase;

use Model\Repository\DossierRepositoryInterface;
use Model\UseCase\ManageFolderUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ManageFolderUseCaseTest extends TestCase
{
    /** @var DossierRepositoryInterface&MockObject */
    private DossierRepositoryInterface $repoMock;

    private ManageFolderUseCase $useCase;

    protected function setUp(): void
    {
        $this->repoMock = $this->createMock(DossierRepositoryInterface::class);
        $this->useCase  = new ManageFolderUseCase($this->repoMock);
    }

    private function baseDossier(array $overrides = []): array
    {
        return array_merge([
            'NumEtu'               => '12345678',
            'Nom'                  => 'Dupont',
            'Prenom'               => 'Alice',
            'PiecesJustificatives' => '{}',
            'StatutDocuments'      => '{}',
            'DateLimite'           => null,
            'CommentaireAdmin'     => null,
            'status'               => 'depot',
        ], $overrides);
    }

    // =========================================================
    // getAllFolders()
    // =========================================================

    public function testGetAllFoldersDelegatesToRepo(): void
    {
        $rows = [$this->baseDossier()];
        $this->repoMock->expects($this->once())->method('findAll')->willReturn($rows);

        $result = $this->useCase->getAllFolders();
        $this->assertSame($rows, $result);
    }

    // =========================================================
    // getStudentDetails() / getByNumetu()
    // =========================================================

    public function testGetStudentDetailsReturnsNullWhenNotFound(): void
    {
        $this->repoMock->method('findByNumEtu')->willReturn(null);

        $this->assertNull($this->useCase->getStudentDetails('99999999'));
    }

    public function testGetStudentDetailsReturnsParsedPieces(): void
    {
        $dossier = $this->baseDossier([
            'PiecesJustificatives' => json_encode(['photo' => 'file.jpg']),
        ]);
        $this->repoMock->method('findByNumEtu')->willReturn($dossier);

        $result = $this->useCase->getStudentDetails('12345678');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('pieces', $result);
        $this->assertArrayHasKey('photo', $result['pieces']);
        $this->assertSame('file.jpg', $result['pieces']['photo']['file']);
        $this->assertSame('pending', $result['pieces']['photo']['status']);
    }

    public function testGetStudentDetailsReturnsParsedStatuts(): void
    {
        $dossier = $this->baseDossier([
            'StatutDocuments' => json_encode(['photo' => 'valide']),
        ]);
        $this->repoMock->method('findByNumEtu')->willReturn($dossier);

        $result = $this->useCase->getStudentDetails('12345678');

        $this->assertArrayHasKey('statuts', $result);
        $this->assertSame('valide', $result['statuts']['photo']);
    }

    public function testGetStudentDetailsHandlesEmptyPiecesJson(): void
    {
        $dossier = $this->baseDossier(['PiecesJustificatives' => '']);
        $this->repoMock->method('findByNumEtu')->willReturn($dossier);

        $result = $this->useCase->getStudentDetails('12345678');

        $this->assertSame([], $result['pieces']);
    }

    public function testGetByNuMetuDelegatesToGetStudentDetails(): void
    {
        $dossier = $this->baseDossier();
        $this->repoMock->method('findByNumEtu')->willReturn($dossier);

        $result = $this->useCase->getByNumetu('12345678');

        $this->assertIsArray($result);
        $this->assertSame('12345678', $result['NumEtu']);
    }

    // =========================================================
    // toggleCompleteStatus()
    // =========================================================

    public function testToggleCompleteStatusDelegatesToRepo(): void
    {
        $this->repoMock->expects($this->once())
            ->method('toggleCompleteStatus')
            ->with('12345678')
            ->willReturn(true);

        $this->assertTrue($this->useCase->toggleCompleteStatus('12345678'));
    }

    // =========================================================
    // setFolderStatus()
    // =========================================================

    public function testSetFolderStatusDelegatesToRepo(): void
    {
        $this->repoMock->expects($this->once())
            ->method('setStatus')
            ->with('12345678', 'accepte')
            ->willReturn(true);

        $this->assertTrue($this->useCase->setFolderStatus('12345678', 'accepte'));
    }

    // =========================================================
    // cycleFolderStatus()
    // =========================================================

    public function testCycleFolderStatusDelegatesToRepo(): void
    {
        $this->repoMock->expects($this->once())
            ->method('cycleStatus')
            ->with('12345678')
            ->willReturn(true);

        $this->assertTrue($this->useCase->cycleFolderStatus('12345678'));
    }


    // =========================================================
    // rechercherAvecPagination()
    // =========================================================

    public function testRechercherAvecPaginationDelegatesToRepo(): void
    {
        $expected = ['data' => [], 'total' => 0, 'totalPages' => 1];

        $this->repoMock->expects($this->once())
            ->method('searchWithPagination')
            ->with(['type' => 'sortant'], 2, 10)
            ->willReturn($expected);

        $result = $this->useCase->rechercherAvecPagination(['type' => 'sortant'], 2, 10);
        $this->assertSame($expected, $result);
    }

    // =========================================================
    // searchWithoutPagination()
    // =========================================================

    public function testSearchWithoutPaginationPassesPerPageZero(): void
    {
        $this->repoMock->expects($this->once())
            ->method('searchWithPagination')
            ->with([], 1, 0)
            ->willReturn(['data' => [], 'total' => 0, 'totalPages' => 1]);

        $this->useCase->searchWithoutPagination([]);
    }

    // =========================================================
    // creerDossier()
    // =========================================================

    public function testCreerDossierDelegatesToRepoCreate(): void
    {
        $this->repoMock->expects($this->once())
            ->method('create')
            ->willReturn(true);

        $data = ['numetu' => '12345678', 'nom' => 'Dupont', 'prenom' => 'Alice', 'naissance' => '2000-01-15'];
        $this->assertTrue($this->useCase->creerDossier($data));
    }

    public function testCreerDossierConvertsEmptyStringsToNull(): void
    {
        $captured = null;
        $this->repoMock->method('create')->willReturnCallback(function (array $data) use (&$captured) {
            $captured = $data;
            return true;
        });

        $this->useCase->creerDossier(['numetu' => '12345678', 'nom' => '', 'prenom' => 'Alice']);

        $this->assertNull($captured['Nom']);
    }

    public function testCreerDossierSetsDefaultStatusDepot(): void
    {
        $captured = null;
        $this->repoMock->method('create')->willReturnCallback(function (array $data) use (&$captured) {
            $captured = $data;
            return true;
        });

        $this->useCase->creerDossier(['numetu' => '12345678']);

        $this->assertSame('depot', $captured['status']);
    }

    public function testCreerDossierIgnoresInvalidDate(): void
    {
        $captured = null;
        $this->repoMock->method('create')->willReturnCallback(function (array $data) use (&$captured) {
            $captured = $data;
            return true;
        });

        $this->useCase->creerDossier(['numetu' => '12345678', 'naissance' => 'not-a-date']);

        $this->assertNull($captured['DateNaissance']);
    }

    public function testCreerDossierEncodesPhotoAsPiecesJson(): void
    {
        $captured = null;
        $this->repoMock->method('create')->willReturnCallback(function (array $data) use (&$captured) {
            $captured = $data;
            return true;
        });

        $this->useCase->creerDossier(['numetu' => '12345678', 'photo' => 'binarydata']);

        $pieces = json_decode($captured['PiecesJustificatives'], true);
        $this->assertArrayHasKey('photo', $pieces);
        $this->assertSame(base64_encode('binarydata'), $pieces['photo']['file']);
    }

    // =========================================================
    // updateDossier()
    // =========================================================

    public function testUpdateDossierReturnsFalseWhenNumetuMissing(): void
    {
        $this->assertFalse($this->useCase->updateDossier([]));
    }

    public function testUpdateDossierReturnsFalseWhenDossierNotFound(): void
    {
        $this->repoMock->method('findByNumEtu')->willReturn(null);

        $this->assertFalse($this->useCase->updateDossier(['numetu' => '12345678']));
    }

    public function testUpdateDossierDelegatesToRepoUpdate(): void
    {
        $this->repoMock->method('findByNumEtu')->willReturn($this->baseDossier());
        $this->repoMock->expects($this->once())->method('update')->willReturn(true);

        $result = $this->useCase->updateDossier(['numetu' => '12345678', 'nom' => 'Martin']);
        $this->assertTrue($result);
    }

    public function testUpdateDossierMergesNewPhotoIntoPieces(): void
    {
        $existing = $this->baseDossier([
            'PiecesJustificatives' => json_encode(['cv' => 'old_cv.pdf']),
        ]);
        $this->repoMock->method('findByNumEtu')->willReturn($existing);

        $captured = null;
        $this->repoMock->method('update')->willReturnCallback(function (string $num, array $data) use (&$captured) {
            $captured = $data;
            return true;
        });

        $this->useCase->updateDossier(['numetu' => '12345678', 'photo' => 'newphoto']);

        $pieces = json_decode($captured[':PiecesJustificatives'], true);
        $this->assertArrayHasKey('photo', $pieces);
        $this->assertArrayHasKey('cv', $pieces);
    }

    // =========================================================
    // analyserDocuments()
    // =========================================================

    public function testAnalyserDocumentsDelegatesToRepo(): void
    {
        $expected = ['manquants' => ['photo'], 'presents' => ['cv'], 'statuts' => []];
        $this->repoMock->expects($this->once())
            ->method('analyserDocuments')
            ->with('12345678')
            ->willReturn($expected);

        $this->assertSame($expected, $this->useCase->analyserDocuments('12345678'));
    }

    // =========================================================
    // enregistrerValidation()
    // =========================================================

    public function testEnregistrerValidationDelegatesToRepo(): void
    {
        $this->repoMock->expects($this->once())
            ->method('enregistrerValidation')
            ->with('12345678', ['photo' => 'valide'], '2025-06-01', 'OK')
            ->willReturn(true);

        $this->assertTrue(
            $this->useCase->enregistrerValidation('12345678', ['photo' => 'valide'], '2025-06-01', 'OK')
        );
    }

    // =========================================================
    // updateDocumentStatus()
    // =========================================================

    public function testUpdateDocumentStatusReturnsFalseWhenDossierNotFound(): void
    {
        $this->repoMock->method('findByNumEtu')->willReturn(null);

        $this->assertFalse($this->useCase->updateDocumentStatus('12345678', 'photo', 'valide', ''));
    }

    public function testUpdateDocumentStatusReturnsTrueOnSuccess(): void
    {
        $this->repoMock->method('findByNumEtu')->willReturn($this->baseDossier());
        $this->repoMock->method('update')->willReturn(true);
        $this->repoMock->method('enregistrerValidation')->willReturn(true);

        $result = $this->useCase->updateDocumentStatus('12345678', 'photo', 'valide', 'OK');
        $this->assertTrue($result);
    }

    public function testUpdateDocumentStatusReturnsFalseWhenUpdateFails(): void
    {
        $this->repoMock->method('findByNumEtu')->willReturn($this->baseDossier());
        $this->repoMock->method('update')->willReturn(false);
        $this->repoMock->method('enregistrerValidation')->willReturn(true);

        $this->assertFalse($this->useCase->updateDocumentStatus('12345678', 'photo', 'valide', ''));
    }
}
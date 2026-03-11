<?php

namespace Tests\Model\Persistence;

use Model\Entity\FolderStats;
use Model\Entity\GenderStats;
use Model\Persistence\FolderRepositoryPDO;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests unitaires pour FolderRepositoryPDO.
 *
 * Stratégie : newInstanceWithoutConstructor() + injection ReflectionProperty
 * sur $db pour ne jamais toucher le singleton Database.
 */
class FolderRepositoryPDOTest extends TestCase
{
    /** @var PDO&MockObject */
    private PDO $pdoMock;

    private FolderRepositoryPDO $repo;

    protected function setUp(): void
    {
        $this->repo = (new \ReflectionClass(FolderRepositoryPDO::class))
            ->newInstanceWithoutConstructor();

        $this->pdoMock = $this->createMock(PDO::class);

        $prop = new \ReflectionProperty(FolderRepositoryPDO::class, 'db');
        $prop->setAccessible(true);
        $prop->setValue($this->repo, $this->pdoMock);
    }


    private function buildFetchAllStmt(array $rows): PDOStatement
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($rows);
        return $stmt;
    }

    public function testGetGlobalStatsReturnsZerosOnPDOException(): void
    {
        $this->pdoMock->method('query')->willThrowException(new \PDOException('fail'));

        $stats = $this->repo->getGlobalStats();
        $this->assertSame(0, $stats->getTotal());
        $this->assertSame(0, $stats->getCompleted());
    }



    public function testGetGenderStatsReturnsZerosOnPDOException(): void
    {
        $this->pdoMock->method('query')->willThrowException(new \PDOException('fail'));

        $stats = $this->repo->getGenderStats();
        $this->assertSame(0, $stats->getMale());
        $this->assertSame(0, $stats->getFemale());
    }

    // =========================================================
    // getTopCountries()
    // =========================================================

    public function testGetTopCountriesReturnsArray(): void
    {
        $rows = [['name' => 'Allemagne', 'count' => 8], ['name' => 'Espagne', 'count' => 5]];
        $stmt = $this->buildFetchAllStmt($rows);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $result = $this->repo->getTopCountries(5);
        $this->assertCount(2, $result);
        $this->assertSame('Allemagne', $result[0]['name']);
    }

    public function testGetTopCountriesReturnsEmptyArrayOnException(): void
    {
        $this->pdoMock->method('prepare')->willThrowException(new \PDOException('fail'));

        $this->assertSame([], $this->repo->getTopCountries(5));
    }

    // =========================================================
    // getDepartmentStats()
    // =========================================================

    public function testGetDepartmentStatsReturnsArray(): void
    {
        $rows = [['name' => 'INFO', 'count' => 12]];
        $stmt = $this->buildFetchAllStmt($rows);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $result = $this->repo->getDepartmentStats(10);
        $this->assertCount(1, $result);
        $this->assertSame('INFO', $result[0]['name']);
    }

    public function testGetDepartmentStatsReturnsEmptyArrayOnException(): void
    {
        $this->pdoMock->method('prepare')->willThrowException(new \PDOException('fail'));

        $this->assertSame([], $this->repo->getDepartmentStats(10));
    }


    public function testGetIncomingOutgoingStatsReturnsZerosOnException(): void
    {
        $this->pdoMock->method('query')->willThrowException(new \PDOException('fail'));

        $result = $this->repo->getIncomingOutgoingStats();
        $this->assertSame(['incoming' => 0, 'outgoing' => 0], $result);
    }

    public function testGetContinentStatsReturnsEmptyOnException(): void
    {
        $this->pdoMock->method('query')->willThrowException(new \PDOException('fail'));

        $this->assertSame([], $this->repo->getContinentStats());
    }


    public function testGetEuropeVsNonEuropeStatsReturnsZerosOnException(): void
    {
        $this->pdoMock->method('query')->willThrowException(new \PDOException('fail'));

        $result = $this->repo->getEuropeVsNonEuropeStats();
        $this->assertSame(['europe_countries' => 0, 'non_europe_countries' => 0], $result);
    }

    // =========================================================
    // getZoneStats()
    // =========================================================

    public function testGetZoneStatsReturnsArray(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn([['name' => 'europe', 'count' => '40']]);
        $this->pdoMock->method('query')->willReturn($stmt);

        $result = $this->repo->getZoneStats();
        $this->assertCount(1, $result);
        $this->assertSame(40, $result[0]['count']);
    }

    // =========================================================
    // inferContinent()
    // =========================================================

    public function testInferContinentReturnsEuropeForEuropeZone(): void
    {
        $result = $this->repo->inferContinent('Allemagne', 'Europe');
        $this->assertSame('Europe', $result);
    }

    public function testInferContinentReturnsAsieForJapon(): void
    {
        $result = $this->repo->inferContinent('Japon', 'hors_europe');
        $this->assertSame('Asie', $result);
    }

    public function testInferContinentReturnsNullForUnknownCountry(): void
    {
        $result = $this->repo->inferContinent('PaysInconnu', 'hors_europe');
        $this->assertNull($result);
    }

    public function testInferContinentReturnsAmeriqueForCanada(): void
    {
        $this->assertSame('Amérique', $this->repo->inferContinent('Canada', 'hors_europe'));
    }

    // =========================================================
    // findAll()
    // =========================================================

    public function testFindAllReturnsArray(): void
    {
        $row = ['NumEtu' => '123', 'Nom' => 'Dupont', 'Prenom' => 'Jean'];
        $stmt = $this->buildFetchAllStmt([$row]);
        $this->pdoMock->method('query')->willReturn($stmt);

        $result = $this->repo->findAll();
        $this->assertCount(1, $result);
        $this->assertSame('Dupont', $result[0]['Nom']);
    }

    public function testFindAllReturnsEmptyArrayOnException(): void
    {
        $this->pdoMock->method('query')->willThrowException(new \PDOException('fail'));

        $this->assertSame([], $this->repo->findAll());
    }

    public function testFindByNumEtuReturnsNullOnException(): void
    {
        $this->pdoMock->method('prepare')->willThrowException(new \PDOException('fail'));

        $this->assertNull($this->repo->findByNumEtu('12345678'));
    }

    // =========================================================
    // create()
    // =========================================================

    public function testCreateReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $data = ['NumEtu' => '12345678', 'Nom' => 'Dupont', 'Prenom' => 'Alice', 'status' => 'depot'];
        $this->assertTrue($this->repo->create($data));
    }

    public function testCreateReturnsFalseOnException(): void
    {
        $this->pdoMock->method('prepare')->willThrowException(new \PDOException('fail'));

        $this->assertFalse($this->repo->create(['NumEtu' => '123']));
    }

    // =========================================================
    // setStatus()
    // =========================================================

    public function testSetStatusReturnsTrueForValidStatus(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':status' => 'accepte', ':numetu' => '12345678'])
            ->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->setStatus('12345678', 'accepte'));
    }

    public function testSetStatusReturnsFalseForInvalidStatus(): void
    {
        $this->assertFalse($this->repo->setStatus('12345678', 'invalide'));
    }

    public function testSetStatusAllowedValues(): void
    {
        foreach (['depot', 'instruction', 'accepte', 'refuse'] as $status) {
            $stmt = $this->createMock(PDOStatement::class);
            $stmt->method('execute')->willReturn(true);
            $this->pdoMock->method('prepare')->willReturn($stmt);

            $this->assertTrue($this->repo->setStatus('12345678', $status));
        }
    }

    // =========================================================
    // setAvisChef()
    // =========================================================

    public function testSetAvisChefReturnsTrueForAccepte(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':avis' => 'accepte', ':numetu' => '12345678'])
            ->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->setAvisChef('12345678', 'accepte'));
    }

    public function testSetAvisChefReturnsTrueForNull(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->setAvisChef('12345678', null));
    }

    public function testSetAvisChefReturnsFalseForInvalidValue(): void
    {
        $this->assertFalse($this->repo->setAvisChef('12345678', 'invalide'));
    }


    // =========================================================
    // getAllDepartements()
    // =========================================================

    public function testGetAllDepartementsReturnsStringArray(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturnOnConsecutiveCalls(
            ['CodeDepartement' => 'INFO'],
            ['CodeDepartement' => 'MATH'],
            false
        );
        $this->pdoMock->method('query')->willReturn($stmt);

        $result = $this->repo->getAllDepartements();
        $this->assertSame(['INFO', 'MATH'], $result);
    }

    public function testGetAllDepartementsReturnsEmptyArrayWhenQueryFails(): void
    {
        $this->pdoMock->method('query')->willReturn(false);

        $this->assertSame([], $this->repo->getAllDepartements());
    }

    // =========================================================
    // enregistrerValidation()
    // =========================================================

    public function testEnregistrerValidationReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $result = $this->repo->enregistrerValidation(
            '12345678',
            ['photo' => 'valide'],
            '2025-06-01',
            'OK'
        );
        $this->assertTrue($result);
    }

    public function testEnregistrerValidationReturnsFalseForInvalidDate(): void
    {
        $result = $this->repo->enregistrerValidation('12345678', [], 'not-a-date');
        $this->assertFalse($result);
    }

    public function testEnregistrerValidationAcceptsNullDateAndComment(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->enregistrerValidation('12345678', []));
    }

    public function testSearchWithPaginationReturnsEmptyOnException(): void
    {
        $this->pdoMock->method('prepare')->willThrowException(new \PDOException('fail'));

        $result = $this->repo->searchWithPagination([], 1, 10);
        $this->assertSame(['data' => [], 'total' => 0, 'totalPages' => 0], $result);
    }
}
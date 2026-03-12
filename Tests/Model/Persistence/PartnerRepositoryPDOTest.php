<?php

namespace Tests\Model\Persistence;

use Model\Entity\Partner;
use Model\Persistence\PartnerRepositoryPDO;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PartnerRepositoryPDOTest extends TestCase
{
    private PartnerRepositoryPDO $repo;

    protected function setUp(): void
    {
        $this->repo = (new \ReflectionClass(PartnerRepositoryPDO::class))
            ->newInstanceWithoutConstructor();
    }

    private function makePartner(
        string $continent   = 'Europe',
        string $country     = 'Allemagne',
        string $city        = 'Berlin',
        string $institution = 'TU Berlin',
        string $type        = 'université'
    ): Partner {
        $p = $this->createMock(Partner::class);
        $p->method('getContinent')->willReturn($continent);
        $p->method('getCountry')->willReturn($country);
        $p->method('getCity')->willReturn($city);
        $p->method('getInstitution')->willReturn($institution);
        $p->method('getType')->willReturn($type);
        return $p;
    }

    private function injectPdo(PDO $pdo): void
    {
        $dbMock = $this->createMock(\Database::class);
        $dbMock->method('getConnection')->willReturn($pdo);

        $ref  = new \ReflectionClass(\Database::class);
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, $dbMock);
    }

    public function testAddPartnerExecutesInsertWithCorrectBindings(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->injectPdo($pdoMock);

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                'continent'  => 'Europe',
                'pays'       => 'Allemagne',
                'ville'      => 'Berlin',
                'universite' => 'TU Berlin',
                'type'       => 'université',
            ])
            ->willReturn(true);

        $this->repo->addPartner($this->makePartner());
    }

    public function testAddPartnerCallsPrepareWithInsertQuery(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->injectPdo($pdoMock);
        $stmtMock->method('execute')->willReturn(true);

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO Partenaires'))
            ->willReturn($stmtMock);

        $this->repo->addPartner($this->makePartner());
    }

    public function testAddPartnerPassesAllPartnerFieldsToStatement(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->injectPdo($pdoMock);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                'continent'  => 'Asie',
                'pays'       => 'Japon',
                'ville'      => 'Tokyo',
                'universite' => 'Université de Tokyo',
                'type'       => 'université',
            ])
            ->willReturn(true);

        $this->repo->addPartner(
            $this->makePartner('Asie', 'Japon', 'Tokyo', 'Université de Tokyo', 'université')
        );
    }

    public function testAddPartnerThrowsPDOExceptionOnFailure(): void
    {
        $pdoMock = $this->createMock(PDO::class);

        $this->injectPdo($pdoMock);

        $pdoMock->method('prepare')
            ->willThrowException(new \PDOException('DB error'));

        $this->expectException(\PDOException::class);
        $this->repo->addPartner($this->makePartner());
    }
}
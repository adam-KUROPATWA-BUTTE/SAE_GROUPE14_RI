<?php

namespace Tests\Model\Persistence;

use Model\Persistence\RelanceRepositoryPDO;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RelanceRepositoryPDOTest extends TestCase
{
    /** @var PDO&MockObject */
    private PDO $pdoMock;

    private RelanceRepositoryPDO $repo;

    protected function setUp(): void
    {
        $this->repo = (new \ReflectionClass(RelanceRepositoryPDO::class))
            ->newInstanceWithoutConstructor();

        $this->pdoMock = $this->createMock(PDO::class);

        $prop = new \ReflectionProperty(RelanceRepositoryPDO::class, 'pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->repo, $this->pdoMock);
    }

    // =========================================================
    // wasRecentlySent()
    // =========================================================

    public function testWasRecentlySentReturnsTrueWhenRowFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $calls = [];
        $stmt->method('bindValue')->willReturnCallback(function () use (&$calls) {
            $calls[] = func_get_args();
            return true;
        });
        $stmt->method('execute');
        $stmt->method('fetchColumn')->willReturn('1');

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $result = $this->repo->wasRecentlySent('12345678', 7);

        $this->assertTrue($result);
        $this->assertSame(':id',   $calls[0][0]);
        $this->assertSame('12345678', $calls[0][1]);
        $this->assertSame(':days', $calls[1][0]);
        $this->assertSame(7,       $calls[1][1]);
        $this->assertSame(PDO::PARAM_INT, $calls[1][2]);
    }

    public function testWasRecentlySentReturnsFalseWhenNoRowFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('bindValue');
        $stmt->method('execute');
        $stmt->method('fetchColumn')->willReturn(false);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertFalse($this->repo->wasRecentlySent('12345678', 30));
    }

    public function testWasRecentlySentBindsCorrectNumEtu(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $calls = [];
        $stmt->method('bindValue')->willReturnCallback(function () use (&$calls) {
            $calls[] = func_get_args();
            return true;
        });
        $stmt->method('execute');
        $stmt->method('fetchColumn')->willReturn(false);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->repo->wasRecentlySent('99999999', 14);

        $this->assertSame(':id',      $calls[0][0]);
        $this->assertSame('99999999', $calls[0][1]);
        $this->assertSame(':days',    $calls[1][0]);
        $this->assertSame(14,         $calls[1][1]);
    }

    public function testWasRecentlySentUsesSelectQuery(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('bindValue');
        $stmt->method('execute');
        $stmt->method('fetchColumn')->willReturn(false);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT'))
            ->willReturn($stmt);

        $this->repo->wasRecentlySent('12345678', 7);
    }

    // =========================================================
    // save()
    // =========================================================

    public function testSaveExecutesInsertWithCorrectBindings(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                'id'      => '12345678',
                'message' => 'Merci de compléter votre dossier.',
            ])
            ->willReturn(true);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->repo->save('12345678', 'Merci de compléter votre dossier.');
    }

    public function testSaveCallsPrepareWithInsertQuery(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO relances'))
            ->willReturn($stmt);

        $this->repo->save('12345678', 'Rappel');
    }

    public function testSaveThrowsOnPDOException(): void
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new \PDOException('DB error'));

        $this->expectException(\PDOException::class);
        $this->repo->save('12345678', 'Rappel');
    }
}
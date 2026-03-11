<?php

namespace Tests\Model\UseCase;

use Model\Entity\Partner;
use Model\Repository\PartnerRepositoryInterface;
use Model\UseCase\AddPartnerUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AddPartnerUseCaseTest extends TestCase
{
    /** @var PartnerRepositoryInterface&MockObject */
    private PartnerRepositoryInterface $repositoryMock;

    private AddPartnerUseCase $useCase;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(PartnerRepositoryInterface::class);
        $this->useCase = new AddPartnerUseCase($this->repositoryMock);
    }

    public function testExecuteCallsAddPartnerOnRepository(): void
    {
        $partner = $this->createMock(Partner::class);

        $this->repositoryMock
            ->expects($this->once())
            ->method('addPartner')
            ->with($this->identicalTo($partner));

        $this->useCase->execute($partner);
    }

    public function testExecutePassesExactPartnerInstance(): void
    {
        $partner = $this->createMock(Partner::class);
        $otherPartner = $this->createMock(Partner::class);

        $this->repositoryMock
            ->expects($this->once())
            ->method('addPartner')
            ->with($this->identicalTo($partner));

        $this->useCase->execute($partner);

        // $otherPartner n'a jamais été passé
        $this->assertNotSame($otherPartner, $partner);
    }

    public function testExecutePropagatesPDOException(): void
    {
        $partner = $this->createMock(Partner::class);

        $this->repositoryMock
            ->method('addPartner')
            ->willThrowException(new \PDOException('DB error'));

        $this->expectException(\PDOException::class);
        $this->useCase->execute($partner);
    }

    public function testExecuteCallsAddPartnerExactlyOnce(): void
    {
        $partner = $this->createMock(Partner::class);

        $this->repositoryMock
            ->expects($this->once())
            ->method('addPartner');

        $this->useCase->execute($partner);
    }
}
<?php

namespace Service;

use Model\Repository\FolderRepositoryInterface;
use Model\Repository\RelanceRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronReminderServiceTest extends TestCase
{
    /** @var FolderRepositoryInterface&MockObject */
    private FolderRepositoryInterface $folderRepoMock;

    /** @var RelanceRepositoryInterface&MockObject */
    private RelanceRepositoryInterface $relanceRepoMock;

    private CronReminderService $service;

    protected function setUp(): void
    {
        $this->folderRepoMock  = $this->createMock(FolderRepositoryInterface::class);
        $this->relanceRepoMock = $this->createMock(RelanceRepositoryInterface::class);

        $this->service = new CronReminderService(
            $this->folderRepoMock,
            $this->relanceRepoMock
        );
    }

    public function testDryRunDoesNotSendRelance(): void
    {
        $folders = [
            [
                'NumEtu'          => '12345',
                'EmailAMU'        => 'test@amu.fr',
                'EmailPersonnel'  => '',
                'Prenom'          => 'John',
                'Nom'             => 'Doe',
            ],
        ];

        $this->folderRepoMock
            ->method('findIncompleteFolders')
            ->willReturn($folders);

        $this->relanceRepoMock
            ->method('wasRecentlySent')
            ->willReturn(false);

        $this->relanceRepoMock
            ->expects($this->never())
            ->method('save');

        $this->service->run(true, 7);

        $this->assertTrue(true);
    }

    public function testSkipIfNoEmail(): void
    {
        $folders = [
            [
                'NumEtu'          => '12345',
                'EmailAMU'        => '',
                'EmailPersonnel'  => '',
                'Prenom'          => 'John',
                'Nom'             => 'Doe',
            ],
        ];

        $this->folderRepoMock
            ->method('findIncompleteFolders')
            ->willReturn($folders);

        $this->relanceRepoMock
            ->expects($this->never())
            ->method('wasRecentlySent');

        $this->service->run(false, 7);

        $this->assertTrue(true);
    }

    public function testSkipIfRecentlySent(): void
    {
        $folders = [
            [
                'NumEtu'          => '12345',
                'EmailAMU'        => 'test@amu.fr',
                'EmailPersonnel'  => '',
                'Prenom'          => 'John',
                'Nom'             => 'Doe',
            ],
        ];

        $this->folderRepoMock
            ->method('findIncompleteFolders')
            ->willReturn($folders);

        $this->relanceRepoMock
            ->method('wasRecentlySent')
            ->willReturn(true);

        $this->relanceRepoMock
            ->expects($this->never())
            ->method('save');

        $this->service->run(false, 7);

        $this->assertTrue(true);
    }
}
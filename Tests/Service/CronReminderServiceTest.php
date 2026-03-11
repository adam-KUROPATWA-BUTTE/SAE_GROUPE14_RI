<?php

namespace Service;

use Model\Repository\FolderRepositoryInterface;
use Model\Repository\RelanceRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CronReminderServiceTest extends TestCase
{
    private $folderRepoMock;
    private $relanceRepoMock;
    private $service;

    protected function setUp(): void
    {
        $this->folderRepoMock = $this->createMock(FolderRepositoryInterface::class);
        $this->relanceRepoMock = $this->createMock(RelanceRepositoryInterface::class);

        $this->service = new CronReminderService(
            $this->folderRepoMock,
            $this->relanceRepoMock
        );
    }

    public function testDryRunDoesNotSendRelance()
    {
        $folders = [
            [
                'NumEtu' => '12345',
                'EmailAMU' => 'test@amu.fr',
                'EmailPersonnel' => '',
                'Prenom' => 'John',
                'Nom' => 'Doe'
            ]
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

        $this->assertTrue(true); // dry-run exécuté sans erreur
    }

    public function testSkipIfNoEmail()
    {
        $folders = [
            [
                'NumEtu' => '12345',
                'EmailAMU' => '',
                'EmailPersonnel' => '',
                'Prenom' => 'John',
                'Nom' => 'Doe'
            ]
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

    public function testSkipIfRecentlySent()
    {
        $folders = [
            [
                'NumEtu' => '12345',
                'EmailAMU' => 'test@amu.fr',
                'EmailPersonnel' => '',
                'Prenom' => 'John',
                'Nom' => 'Doe'
            ]
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
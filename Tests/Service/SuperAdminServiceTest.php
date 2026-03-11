<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Service\SuperAdminService;
use Model\Persistence\UserRepositoryPDO;

class SuperAdminServiceTest extends TestCase
{
    private $userRepoMock;
    private $service;

    protected function setUp(): void
    {
        $this->userRepoMock = $this->createMock(UserRepositoryPDO::class);

        $this->service = new SuperAdminService($this->userRepoMock);
    }

    public function testGetAvailableDepartments(): void
    {
        $expected = ['INFO', 'MATH'];

        $this->userRepoMock
            ->expects($this->once())
            ->method('getDistinctDepartments')
            ->willReturn($expected);

        $result = $this->service->getAvailableDepartments();

        $this->assertEquals($expected, $result);
    }

    public function testAddDepartment(): void
    {
        $this->userRepoMock
            ->expects($this->once())
            ->method('addCustomDepartment')
            ->with('INFO');

        $this->service->addDepartment('INFO');
    }

    public function testGetAvailableSites(): void
    {
        $expected = ['Aix', 'Marseille'];

        $this->userRepoMock
            ->expects($this->once())
            ->method('getDistinctSites')
            ->willReturn($expected);

        $result = $this->service->getAvailableSites();

        $this->assertEquals($expected, $result);
    }

    public function testAddSite(): void
    {
        $this->userRepoMock
            ->expects($this->once())
            ->method('addCustomSite')
            ->with('Aix');

        $this->service->addSite('Aix');
    }

    public function testGetAllAccounts(): void
    {
        $accounts = [
            [
                'login' => 'admin@test.com',
                'role' => 'admin',
                'departement' => 'INFO',
                'site' => 'Aix',
                'nom' => 'Doe',
                'prenom' => 'John',
                'created_at' => '2025-01-01'
            ]
        ];

        $this->userRepoMock
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($accounts);

        $result = $this->service->getAllAccounts();

        $this->assertEquals($accounts, $result);
    }

    public function testCreateAccountSuccess(): void
    {
        $this->userRepoMock
            ->method('findByLogin')
            ->willReturn(null);

        $this->userRepoMock
            ->expects($this->once())
            ->method('create')
            ->with(
                'test@test.com',
                $this->callback(fn($hash) => password_verify('password123', $hash)),
                'admin',
                'INFO',
                'Aix',
                'Doe',
                'John'
            );

        $this->service->createAccount(
            'test@test.com',
            'password123',
            'admin',
            'INFO',
            'Aix',
            'Doe',
            'John'
        );

        $this->assertTrue(true);
    }

    public function testCreateAccountAlreadyExists(): void
    {
        $this->userRepoMock
            ->method('findByLogin')
            ->willReturn(['login' => 'test@test.com']);

        $this->expectException(\RuntimeException::class);

        $this->service->createAccount(
            'test@test.com',
            'password123',
            'admin'
        );
    }

    public function testDeleteAccount(): void
    {
        $this->userRepoMock
            ->expects($this->once())
            ->method('deleteByLogin')
            ->with('test@test.com')
            ->willReturn(true);

        $result = $this->service->deleteAccount('test@test.com');

        $this->assertTrue($result);
    }
}
<?php

namespace Tests\Model\UseCase;

use Model\Repository\UserRepositoryInterface;
use Model\UseCase\LoginUserUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LoginUserUseCaseTest extends TestCase
{
    /** @var UserRepositoryInterface&MockObject */
    private UserRepositoryInterface $adminRepo;

    /** @var UserRepositoryInterface&MockObject */
    private UserRepositoryInterface $studentRepo;

    private LoginUserUseCase $useCase;

    protected function setUp(): void
    {
        $this->adminRepo   = $this->createMock(UserRepositoryInterface::class);
        $this->studentRepo = $this->createMock(UserRepositoryInterface::class);
        $this->useCase     = new LoginUserUseCase($this->adminRepo, $this->studentRepo);
    }

    // =========================================================
    // Routage email → adminRepo
    // =========================================================

    public function testExecuteWithEmailUsesAdminRepo(): void
    {
        $this->adminRepo->expects($this->once())
            ->method('login')
            ->with('admin@test.com', 'secret')
            ->willReturn(['success' => true, 'role' => 'admin']);

        $this->studentRepo->expects($this->never())->method('login');

        $result = $this->useCase->execute('admin@test.com', 'secret');

        $this->assertTrue($result['success']);
        $this->assertSame('admin', $result['role']);
    }

    public function testExecuteWithEmailReturnsFailureWhenAdminRepoFails(): void
    {
        $this->adminRepo->method('login')->willReturn(['success' => false]);

        $result = $this->useCase->execute('admin@test.com', 'wrongpassword');

        $this->assertFalse($result['success']);
    }

    // =========================================================
    // Routage numéro étudiant → studentRepo
    // =========================================================

    public function testExecuteWithStudentNumberUsesStudentRepo(): void
    {
        $this->studentRepo->expects($this->once())
            ->method('login')
            ->with('12345678', 'secret')
            ->willReturn(['success' => true, 'role' => 'student']);

        $this->adminRepo->expects($this->never())->method('login');

        $result = $this->useCase->execute('12345678', 'secret');

        $this->assertTrue($result['success']);
        $this->assertSame('student', $result['role']);
    }

    public function testExecuteWithStudentNumberReturnsFailureWhenStudentRepoFails(): void
    {
        $this->studentRepo->method('login')->willReturn(['success' => false]);

        $result = $this->useCase->execute('12345678', 'wrongpassword');

        $this->assertFalse($result['success']);
    }

    // =========================================================
    // Validation email
    // =========================================================

    public function testExecuteWithInvalidEmailUsesStudentRepo(): void
    {
        // "not-an-email" n'est pas une adresse valide → studentRepo
        $this->studentRepo->expects($this->once())
            ->method('login')
            ->willReturn(['success' => false]);

        $this->adminRepo->expects($this->never())->method('login');

        $this->useCase->execute('not-an-email', 'pass');
    }

    public function testExecuteWithValidEmailFormatUsesAdminRepo(): void
    {
        $this->adminRepo->expects($this->once())
            ->method('login')
            ->willReturn(['success' => true, 'role' => 'chef_departement']);

        $this->studentRepo->expects($this->never())->method('login');

        $result = $this->useCase->execute('chef@univ.fr', 'pass');
        $this->assertTrue($result['success']);
    }

    // =========================================================
    // Transmission du résultat brut du repo
    // =========================================================

    public function testExecuteReturnsRawArrayFromAdminRepo(): void
    {
        $expected = ['success' => true, 'role' => 'admin', 'departement' => 'INFO', 'force_change_password' => false];
        $this->adminRepo->method('login')->willReturn($expected);

        $result = $this->useCase->execute('admin@test.com', 'pass');

        $this->assertSame($expected, $result);
    }

    public function testExecuteReturnsRawArrayFromStudentRepo(): void
    {
        $expected = ['success' => true, 'role' => 'student', 'numetu' => '12345678'];
        $this->studentRepo->method('login')->willReturn($expected);

        $result = $this->useCase->execute('12345678', 'pass');

        $this->assertSame($expected, $result);
    }
}
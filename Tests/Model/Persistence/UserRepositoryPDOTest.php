<?php

namespace Tests\Model\Persistence;

use Model\Entity\User;
use Model\Persistence\UserRepositoryPDO;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UserRepositoryPDOTest extends TestCase
{
    /** @var PDO&MockObject */
    private PDO $pdoMock;

    private UserRepositoryPDO $repo;

    protected function setUp(): void
    {
        $this->repo = (new \ReflectionClass(UserRepositoryPDO::class))
            ->newInstanceWithoutConstructor();

        $this->pdoMock = $this->createMock(PDO::class);

        $prop = new \ReflectionProperty(UserRepositoryPDO::class, 'pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->repo, $this->pdoMock);
    }

    private function buildFetchStmt(mixed $returnValue): PDOStatement
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($returnValue);
        return $stmt;
    }

    private function buildFetchAllStmt(array $rows): PDOStatement
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($rows);
        return $stmt;
    }

    private function adminRow(): array
    {
        return ['id' => 1, 'email' => 'admin@test.com', 'password' => 'hashed', 'role' => 'admin', 'numetu' => null, 'departement' => 'INFO'];
    }

    private function studentRow(): array
    {
        return ['id' => 2, 'email' => 'etu@test.com', 'password' => 'hashed', 'role' => 'student', 'numetu' => '12345678', 'departement' => null];
    }

    // =========================================================
    // findByEmail()
    // =========================================================

    public function testFindByEmailReturnsAdminWhenFoundInAdminsTable(): void
    {
        $stmtAdmin = $this->buildFetchStmt($this->adminRow());

        $this->pdoMock->method('prepare')->willReturn($stmtAdmin);

        $user = $this->repo->findByEmail('admin@test.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('admin@test.com', $user->getEmail());
    }

    public function testFindByEmailReturnsStudentWhenNotInAdminsTable(): void
    {
        $stmtAdmin   = $this->buildFetchStmt(false);
        $stmtStudent = $this->buildFetchStmt($this->studentRow());

        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtAdmin, $stmtStudent);

        $user = $this->repo->findByEmail('etu@test.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('student', $user->getRole());
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $stmt = $this->buildFetchStmt(false);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertNull($this->repo->findByEmail('nobody@test.com'));
    }

    // =========================================================
    // findByStudentNumber()
    // =========================================================

    public function testFindByStudentNumberReturnsUserWhenFound(): void
    {
        $stmt = $this->buildFetchStmt($this->studentRow());
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $user = $this->repo->findByStudentNumber('12345678');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('student', $user->getRole());
        $this->assertSame('12345678', $user->getNumetu());
    }

    public function testFindByStudentNumberReturnsNullWhenNotFound(): void
    {
        $stmt = $this->buildFetchStmt(false);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertNull($this->repo->findByStudentNumber('00000000'));
    }

    // =========================================================
    // save()
    // =========================================================

    public function testSaveAdminInsertsIntoAdminsTable(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'admin@test.com', 'password' => 'hashed', 'role' => 'admin'])
            ->willReturn(true);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $user = new User(null, 'admin@test.com', null, 'hashed', 'admin');
        $this->assertTrue($this->repo->save($user));
    }

    public function testSaveStudentInsertsIntoEtudiantsTable(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'etu@test.com', 'password' => 'hashed', 'numetu' => '12345678'])
            ->willReturn(true);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $user = new User(null, 'etu@test.com', '12345678', 'hashed', 'student');
        $this->assertTrue($this->repo->save($user));
    }

    public function testSaveReturnsFalseOnException(): void
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new \PDOException('fail'));

        $user = new User(null, 'x@test.com', null, 'hashed', 'admin');
        $this->assertFalse($this->repo->save($user));
    }

    // =========================================================
    // updatePassword()
    // =========================================================

    public function testUpdatePasswordReturnsTrueWhenAdminUpdated(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(1);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->updatePassword('admin@test.com', 'newhash'));
    }

    public function testUpdatePasswordFallsBackToEtudiantsWhenAdminNotFound(): void
    {
        $stmtAdmin   = $this->createMock(PDOStatement::class);
        $stmtStudent = $this->createMock(PDOStatement::class);

        $stmtAdmin->method('execute')->willReturn(true);
        $stmtAdmin->method('rowCount')->willReturn(0);
        $stmtStudent->method('execute')->willReturn(true);

        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtAdmin, $stmtStudent);

        $result = $this->repo->updatePassword('etu@test.com', 'newhash');
        $this->assertTrue($result);
    }

    public function testUpdatePasswordReturnsFalseOnException(): void
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new \PDOException('fail'));

        $this->assertFalse($this->repo->updatePassword('x@test.com', 'hash'));
    }

    // =========================================================
    // updatePasswordAndUnlock()
    // =========================================================

    public function testUpdatePasswordAndUnlockReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->updatePasswordAndUnlock('admin@test.com', 'newhash'));
    }

    public function testUpdatePasswordAndUnlockReturnsFalseOnException(): void
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new \PDOException('fail'));

        $this->assertFalse($this->repo->updatePasswordAndUnlock('x@test.com', 'hash'));
    }

    // =========================================================
    // findByLogin()
    // =========================================================

    public function testFindByLoginReturnsArrayWhenFound(): void
    {
        $stmt = $this->buildFetchStmt($this->adminRow());
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $result = $this->repo->findByLogin('admin@test.com');
        $this->assertIsArray($result);
        $this->assertSame('admin@test.com', $result['email']);
    }

    public function testFindByLoginReturnsNullWhenNotFound(): void
    {
        $stmt = $this->buildFetchStmt(false);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertNull($this->repo->findByLogin('nobody@test.com'));
    }

    public function testFindByLoginReturnsNullOnException(): void
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new \PDOException('fail'));

        $this->assertNull($this->repo->findByLogin('x@test.com'));
    }

    // =========================================================
    // loginExists()
    // =========================================================

    public function testLoginExistsReturnsTrueWhenFound(): void
    {
        $stmt = $this->buildFetchStmt($this->adminRow());
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->loginExists('admin@test.com'));
    }

    public function testLoginExistsReturnsFalseWhenNotFound(): void
    {
        $stmt = $this->buildFetchStmt(false);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertFalse($this->repo->loginExists('nobody@test.com'));
    }

    // =========================================================
    // create()
    // =========================================================

    public function testCreateReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->create('admin@test.com', 'hashed', 'admin', 'INFO', 'Site A', 'Doe', 'John'));
    }

    public function testCreateReturnsFalseOnException(): void
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new \PDOException('fail'));

        $this->assertFalse($this->repo->create('x@test.com', 'hashed', 'admin'));
    }

    // =========================================================
    // deleteByLogin()
    // =========================================================

    public function testDeleteByLoginReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':email' => 'admin@test.com'])
            ->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->deleteByLogin('admin@test.com'));
    }

    public function testDeleteByLoginReturnsFalseOnException(): void
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new \PDOException('fail'));

        $this->assertFalse($this->repo->deleteByLogin('x@test.com'));
    }

    // =========================================================
    // findAll()
    // =========================================================

    public function testFindAllReturnsMappedArray(): void
    {
        $rows = [[
            'login' => 'admin@test.com', 'role' => 'admin',
            'departement' => 'INFO', 'site' => null,
            'nom' => 'Doe', 'prenom' => 'John', 'created_at' => '2024-01-01',
        ]];
        $stmt = $this->buildFetchAllStmt($rows);
        $this->pdoMock->method('query')->willReturn($stmt);

        $result = $this->repo->findAll();
        $this->assertCount(1, $result);
        $this->assertSame('admin@test.com', $result[0]['login']);
    }

    public function testFindAllReturnsEmptyArrayWhenQueryFails(): void
    {
        $this->pdoMock->method('query')->willReturn(false);

        $this->assertSame([], $this->repo->findAll());
    }

    public function testFindAllReturnsEmptyArrayOnException(): void
    {
        $this->pdoMock->method('query')
            ->willThrowException(new \PDOException('fail'));

        $this->assertSame([], $this->repo->findAll());
    }

    // =========================================================
    // getDistinctDepartments()
    // =========================================================

    public function testGetDistinctDepartmentsReturnsSortedArray(): void
    {
        $stmtDossiers = $this->createMock(PDOStatement::class);
        $stmtDossiers->method('fetchAll')->willReturn(['MATH', 'INFO']);

        $stmtCustom = $this->createMock(PDOStatement::class);
        $stmtCustom->method('fetchAll')->willReturn(['BIO']);

        $this->pdoMock->method('query')
            ->willReturnOnConsecutiveCalls($stmtDossiers, $stmtCustom);

        $result = $this->repo->getDistinctDepartments();
        $this->assertContains('INFO', $result);
        $this->assertContains('MATH', $result);
        $this->assertContains('BIO', $result);
        $this->assertSame($result, array_values(array_unique($result)));
    }

    // =========================================================
    // addCustomDepartment()
    // =========================================================

    public function testAddCustomDepartmentExecutesInsert(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':code' => 'CHIMIE'])
            ->willReturn(true);

        $this->pdoMock->method('exec')->willReturn(0);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->repo->addCustomDepartment('CHIMIE');
    }

    // =========================================================
    // getDistinctSites()
    // =========================================================

    public function testGetDistinctSitesIncludesDefaultSite(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn([]);
        $this->pdoMock->method('query')->willReturn($stmt);

        $result = $this->repo->getDistinctSites();
        $this->assertContains('Site Gaston Berger', $result);
    }

    public function testGetDistinctSitesMergesCustomSites(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn(['Site Nord']);
        $this->pdoMock->method('query')->willReturn($stmt);

        $result = $this->repo->getDistinctSites();
        $this->assertContains('Site Nord', $result);
        $this->assertContains('Site Gaston Berger', $result);
    }

    // =========================================================
    // addCustomSite()
    // =========================================================

    public function testAddCustomSiteExecutesInsert(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':name' => 'Site Sud'])
            ->willReturn(true);

        $this->pdoMock->method('exec')->willReturn(0);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->repo->addCustomSite('Site Sud');
    }

    // =========================================================
    // resetPassword()
    // =========================================================

    public function testResetPasswordReturnsFalseWhenUserNotFound(): void
    {
        $stmt = $this->buildFetchStmt(false);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertFalse($this->repo->resetPassword('nobody@test.com'));
    }

    public function testResetPasswordReturnsTrueWhenUserFound(): void
    {
        // findByEmail → admins hit → returns user
        $stmtFind   = $this->buildFetchStmt($this->adminRow());
        // updatePassword → admins UPDATE (rowCount > 0)
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdate->method('execute')->willReturn(true);
        $stmtUpdate->method('rowCount')->willReturn(1);

        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtFind, $stmtUpdate);

        $this->assertTrue($this->repo->resetPassword('admin@test.com'));
    }
}
<?php

namespace Tests\Model\Persistence;

use Model\Entity\Conversation;
use Model\Persistence\ConversationPDO;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour ConversationPDO.
 *
 * Stratégie : newInstanceWithoutConstructor() + injection ReflectionProperty
 * pour ne jamais toucher le singleton Database.
 */
class ConversationPDOTest extends TestCase
{
    private PDO $pdoMock;
    private ConversationPDO $repo;

    protected function setUp(): void
    {
        $this->repo = (new \ReflectionClass(ConversationPDO::class))
            ->newInstanceWithoutConstructor();

        $this->pdoMock = $this->createMock(PDO::class);

        $prop = new \ReflectionProperty(ConversationPDO::class, 'pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->repo, $this->pdoMock);
    }

    // =========================================================
    // Helpers inline (pas de méthodes privées séparées)
    // =========================================================

    private function makeFetchStmt(array $rows): PDOStatement
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $returns = array_merge($rows, [false]);
        $stmt->method('fetch')->willReturnOnConsecutiveCalls(...$returns);
        return $stmt;
    }

    private function convRow(int $id): array
    {
        return [
            'id'             => $id,
            'student_numetu' => '12345678',
            'name'           => 'Alice',
            'email'          => 'alice@example.com',
            'subject'        => 'Question',
            'status'         => 'open',
            'created_at'     => '2024-01-15 10:00:00',
        ];
    }

    private function msgRow(int $id, int $convId): array
    {
        return [
            'id'              => $id,
            'conversation_id' => $convId,
            'sender_type'     => 'student',
            'content'         => 'Bonjour',
            'is_read'         => 0,
            'created_at'      => '2024-01-15 10:01:00',
        ];
    }

    // =========================================================
    // create()
    // =========================================================

    public function testCreateReturnsInsertedId(): void
    {
        $stmtConv = $this->createMock(PDOStatement::class);
        $stmtMsg  = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtConv, $stmtMsg);
        $this->pdoMock->expects($this->once())->method('lastInsertId')->willReturn('42');
        $this->pdoMock->expects($this->once())->method('commit');

        $stmtConv->expects($this->once())->method('execute')->willReturn(true);
        $stmtMsg->expects($this->once())->method('execute')->willReturn(true);

        $this->assertSame(42, $this->repo->create('12345678', 'Alice', 'alice@example.com', 'Q', 'Msg'));
    }

    public function testCreateRollsBackOnException(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willThrowException(new \Exception('fail'));

        $this->pdoMock->method('beginTransaction');
        $this->pdoMock->method('prepare')->willReturn($stmt);
        $this->pdoMock->expects($this->once())->method('rollBack');

        $this->expectException(\Exception::class);
        $this->repo->create('12345678', 'Alice', 'alice@example.com', 'Q', 'Msg');
    }

    // =========================================================
    // addMessage()
    // =========================================================

    public function testAddMessageReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':conv_id' => 1, ':sender_type' => 'admin', ':content' => 'Rep'])
            ->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->addMessage(1, 'admin', 'Rep'));
    }

    public function testAddMessageReturnsFalseOnFailure(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertFalse($this->repo->addMessage(99, 'student', 'Hello'));
    }

    // =========================================================
    // findById()
    // =========================================================

    public function testFindByIdReturnsConversationWithMessages(): void
    {
        $stmtConv = $this->makeFetchStmt([$this->convRow(1)]);
        $stmtMsg  = $this->makeFetchStmt([$this->msgRow(1, 1)]);

        $this->pdoMock->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtConv, $stmtMsg);

        $conv = $this->repo->findById(1);

        $this->assertInstanceOf(Conversation::class, $conv);
        $this->assertSame(1, $conv->getId());
        $this->assertSame('open', $conv->getStatus());
        $this->assertCount(1, $conv->getMessages());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $stmt = $this->makeFetchStmt([]);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertNull($this->repo->findById(999));
    }

    // =========================================================
    // findByStudentNumEtu()
    // =========================================================

    public function testFindByStudentNumEtuReturnsConversationList(): void
    {
        $stmtConv = $this->makeFetchStmt([$this->convRow(2)]);
        $stmtMsg  = $this->makeFetchStmt([]);

        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtConv, $stmtMsg);

        $list = $this->repo->findByStudentNumEtu('12345678');
        $this->assertCount(1, $list);
        $this->assertInstanceOf(Conversation::class, $list[0]);
    }

    public function testFindByStudentNumEtuReturnsEmptyArray(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->makeFetchStmt([]));

        $this->assertSame([], $this->repo->findByStudentNumEtu('00000000'));
    }

    // =========================================================
    // findAll()
    // =========================================================

    public function testFindAllReturnsAllConversations(): void
    {
        $stmtAll  = $this->makeFetchStmt([$this->convRow(1), $this->convRow(2)]);
        $stmtMsg1 = $this->makeFetchStmt([]);
        $stmtMsg2 = $this->makeFetchStmt([]);

        $this->pdoMock->expects($this->once())->method('query')->willReturn($stmtAll);
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtMsg1, $stmtMsg2);

        $this->assertCount(2, $this->repo->findAll());
    }

    public function testFindAllReturnsEmptyArrayWhenQueryFails(): void
    {
        $this->pdoMock->method('query')->willReturn(false);

        $this->assertSame([], $this->repo->findAll());
    }

    // =========================================================
    // findUnreadByRole()
    // =========================================================

    public function testFindUnreadByRoleAdminFiltersStudentSender(): void
    {
        $stmtConv = $this->makeFetchStmt([$this->convRow(1)]);
        $stmtMsg  = $this->makeFetchStmt([]);

        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtConv, $stmtMsg);

        $stmtConv->expects($this->once())
            ->method('execute')
            ->with([':sender' => 'student']);

        $this->assertCount(1, $this->repo->findUnreadByRole('admin'));
    }

    public function testFindUnreadByRoleStudentFiltersAdminSender(): void
    {
        $stmt = $this->makeFetchStmt([]);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([':sender' => 'admin']);

        $this->assertSame([], $this->repo->findUnreadByRole('student'));
    }

    // =========================================================
    // markAsRead()
    // =========================================================

    public function testMarkAsReadAdminMarksStudentMessages(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':cid' => 5, ':sender' => 'student'])
            ->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->markAsRead(5, 'admin'));
    }

    public function testMarkAsReadStudentMarksAdminMessages(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':cid' => 3, ':sender' => 'admin'])
            ->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->markAsRead(3, 'student'));
    }

    // =========================================================
    // delete()
    // =========================================================

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':id' => 7])
            ->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertTrue($this->repo->delete(7));
    }

    public function testDeleteReturnsFalseOnFailure(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmt);

        $this->assertFalse($this->repo->delete(999));
    }
}
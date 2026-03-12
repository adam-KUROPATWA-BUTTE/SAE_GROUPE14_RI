<?php

namespace Service;

use Model\Entity\Conversation;
use Model\Persistence\ConversationPDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContactServiceTest extends TestCase
{
    /** @var ConversationPDO&MockObject */
    private ConversationPDO $repositoryMock;

    private ContactService $service;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ConversationPDO::class);
        $this->service        = new ContactService($this->repositoryMock);
    }

    public function testCreateConversationSuccess(): void
    {
        $this->repositoryMock
            ->expects($this->once())
            ->method('create')
            ->with('12345', 'John Doe', 'john@test.com', 'Subject', 'Message')
            ->willReturn(1);

        $result = $this->service->createConversation(
            '12345',
            'John Doe',
            'john@test.com',
            'Subject',
            'Message'
        );

        $this->assertEquals(1, $result);
    }

    public function testAddMessageConversationNotFound(): void
    {
        $this->repositoryMock
            ->method('findById')
            ->willReturn(null);

        $result = $this->service->addMessage(1, 'student', 'Hello');

        $this->assertFalse($result);
    }

    public function testAddMessageSuccess(): void
    {
        $conversationMock = $this->createMock(Conversation::class);

        $this->repositoryMock
            ->method('findById')
            ->willReturn($conversationMock);

        $this->repositoryMock
            ->method('addMessage')
            ->willReturn(true);

        $result = $this->service->addMessage(1, 'student', 'Hello');

        $this->assertTrue($result);
    }

    public function testGetStudentConversations(): void
    {
        $expected = [];

        $this->repositoryMock
            ->expects($this->once())
            ->method('findByStudentNumEtu')
            ->with('12345')
            ->willReturn($expected);

        $result = $this->service->getStudentConversations('12345');

        $this->assertEquals($expected, $result);
    }

    public function testGetAllConversations(): void
    {
        $expected = [];

        $this->repositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expected);

        $result = $this->service->getAllConversations();

        $this->assertEquals($expected, $result);
    }

    public function testMarkConversationAsRead(): void
    {
        $this->repositoryMock
            ->expects($this->once())
            ->method('markAsRead')
            ->with(1, 'admin')
            ->willReturn(true);

        $result = $this->service->markConversationAsRead(1, 'admin');

        $this->assertTrue($result);
    }

    public function testDeleteConversation(): void
    {
        $this->repositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $result = $this->service->deleteConversation(1);

        $this->assertTrue($result);
    }
}
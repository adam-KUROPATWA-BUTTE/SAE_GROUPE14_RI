<?php

namespace Model\Entity;

use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    public function testSetStatus(): void
    {
        $conversation = new Conversation(
            "12345",
            "John Doe",
            "john@example.com",
            "Subject test"
        );

        $conversation->setStatus("closed");

        $this->assertEquals("closed", $conversation->getStatus());
    }

    public function testAddAndGetMessages(): void
    {
        $conversation = new Conversation(
            "12345",
            "John Doe",
            "john@example.com",
            "Subject test"
        );

        $message = $this->createMock(Message::class);
        $conversation->addMessage($message);

        $messages = $conversation->getMessages();

        $this->assertCount(1, $messages);
        $this->assertSame($message, $messages[0]);
    }

    public function testGetFirstMessage(): void
    {
        $conversation = new Conversation(
            "12345",
            "John Doe",
            "john@example.com",
            "Subject test"
        );

        $this->assertNull($conversation->getFirstMessage());

        $message1 = $this->createMock(Message::class);
        $message2 = $this->createMock(Message::class);

        $conversation->addMessage($message1);
        $conversation->addMessage($message2);

        $this->assertSame($message1, $conversation->getFirstMessage());
    }

    public function testGetLastMessage(): void
    {
        $conversation = new Conversation(
            "12345",
            "John Doe",
            "john@example.com",
            "Subject test"
        );

        $this->assertNull($conversation->getLastMessage());

        $message1 = $this->createMock(Message::class);
        $message2 = $this->createMock(Message::class);

        $conversation->addMessage($message1);
        $conversation->addMessage($message2);

        $this->assertSame($message2, $conversation->getLastMessage());
    }

    public function testHasUnreadMessagesForAdmin(): void
    {
        $conversation = new Conversation(
            "12345",
            "John Doe",
            "john@example.com",
            "Subject test"
        );

        $message = $this->createMock(Message::class);
        $message->method('isRead')->willReturn(false);
        $message->method('getSenderType')->willReturn('student');

        $conversation->addMessage($message);

        $this->assertTrue($conversation->hasUnreadMessagesFor('admin'));
    }

    public function testHasUnreadMessagesForStudent(): void
    {
        $conversation = new Conversation(
            "12345",
            "John Doe",
            "john@example.com",
            "Subject test"
        );

        $message = $this->createMock(Message::class);
        $message->method('isRead')->willReturn(false);
        $message->method('getSenderType')->willReturn('admin');

        $conversation->addMessage($message);

        $this->assertTrue($conversation->hasUnreadMessagesFor('student'));
    }

    public function testNoUnreadMessages(): void
    {
        $conversation = new Conversation(
            "12345",
            "John Doe",
            "john@example.com",
            "Subject test"
        );

        $message = $this->createMock(Message::class);
        $message->method('isRead')->willReturn(true);
        $message->method('getSenderType')->willReturn('student');

        $conversation->addMessage($message);

        $this->assertFalse($conversation->hasUnreadMessagesFor('admin'));
    }
}
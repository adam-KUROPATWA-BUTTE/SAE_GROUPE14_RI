<?php

namespace Model\Entity;

use PHPUnit\Framework\TestCase;
use Model\Entity\Message;

class MessageTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $date = new \DateTime();

        $message = new Message(
            1,
            "student",
            "Bonjour",
            false,
            10,
            $date
        );

        $this->assertEquals(10, $message->getId());
        $this->assertEquals(1, $message->getConversationId());
        $this->assertEquals("student", $message->getSenderType());
        $this->assertEquals("Bonjour", $message->getContent());
        $this->assertFalse($message->isRead());
        $this->assertSame($date, $message->getCreatedAt());
    }

    public function testDefaultValues()
    {
        $message = new Message(
            2,
            "admin",
            "Message test"
        );

        $this->assertNull($message->getId());
        $this->assertEquals(2, $message->getConversationId());
        $this->assertEquals("admin", $message->getSenderType());
        $this->assertEquals("Message test", $message->getContent());
        $this->assertFalse($message->isRead());
        $this->assertInstanceOf(\DateTime::class, $message->getCreatedAt());    }

    public function testMarkAsRead()
    {
        $message = new Message(
            1,
            "student",
            "Test message"
        );

        $this->assertFalse($message->isRead());

        $message->markAsRead();

        $this->assertTrue($message->isRead());
    }
}

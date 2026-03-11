<?php

namespace Tests\Model\Entity;

use PHPUnit\Framework\TestCase;
use Model\Entity\Folder;

class FolderTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $pieces = [
            'passport' => true,
            'photo' => false
        ];

        $folder = new Folder(
            "12345",
            "Doe",
            "John",
            "john@example.com",
            "0600000000",
            true,
            $pieces
        );

        $this->assertEquals("12345", $folder->getNumEtu());
        $this->assertEquals("Doe", $folder->getNom());
        $this->assertEquals("John", $folder->getPrenom());
        $this->assertEquals("john@example.com", $folder->getEmailPersonnel());
        $this->assertEquals("0600000000", $folder->getTelephone());
        $this->assertTrue($folder->isComplete());
        $this->assertEquals($pieces, $folder->getPieces());
    }

    public function testDefaultValues()
    {
        $folder = new Folder("12345");

        $this->assertEquals("12345", $folder->getNumEtu());
        $this->assertNull($folder->getNom());
        $this->assertNull($folder->getPrenom());
        $this->assertNull($folder->getEmailPersonnel());
        $this->assertNull($folder->getTelephone());
        $this->assertFalse($folder->isComplete());
        $this->assertEmpty($folder->getPieces());
    }

    public function testSetComplete()
    {
        $folder = new Folder("12345");

        $folder->setComplete(true);

        $this->assertTrue($folder->isComplete());
    }

    public function testSetPieces()
    {
        $folder = new Folder("12345");

        $pieces = [
            'cv' => true,
            'motivation_letter' => true
        ];

        $folder->setPieces($pieces);

        $this->assertEquals($pieces, $folder->getPieces());
    }
}
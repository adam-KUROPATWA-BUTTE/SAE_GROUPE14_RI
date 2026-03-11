<?php

namespace Tests\Model\Entity;


use PHPUnit\Framework\TestCase;
use Model\Entity\Folder;

class FolderTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $dossier = new Folder(1, true);

        $this->assertEquals(1, $dossier->getId());
        $this->assertTrue($dossier->isComplete());
    }

    public function testIsCompleteFalse()
    {
        $dossier = new Folder(2, false);

        $this->assertEquals(2, $dossier->getId());
        $this->assertFalse($dossier->isComplete());
    }
}
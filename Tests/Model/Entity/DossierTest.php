<?php

namespace Model\Entity;


use PHPUnit\Framework\TestCase;
use Model\Entity\Dossier;

class DossierTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $dossier = new Dossier(1, true);

        $this->assertEquals(1, $dossier->getId());
        $this->assertTrue($dossier->isComplete());
    }

    public function testIsCompleteFalse()
    {
        $dossier = new Dossier(2, false);

        $this->assertEquals(2, $dossier->getId());
        $this->assertFalse($dossier->isComplete());
    }
}
<?php

namespace Model\Entity;


use PHPUnit\Framework\TestCase;
use Model\Entity\DossierStats;

class DossierStatsTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $stats = new DossierStats(10, 7);

        $this->assertEquals(10, $stats->getTotal());
        $this->assertEquals(7, $stats->getCompleted());
    }

    public function testCompletionPercentage()
    {
        $stats = new DossierStats(10, 5);

        $this->assertEquals(50.0, $stats->getCompletionPercentage());
    }

    public function testCompletionPercentageWithZeroTotal()
    {
        $stats = new DossierStats(0, 0);

        $this->assertEquals(0, $stats->getCompletionPercentage());
    }
}
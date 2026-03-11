<?php

namespace Model\Entity;


use PHPUnit\Framework\TestCase;
use Model\Entity\GenderStats;

class GenderStatsTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $stats = new GenderStats(6, 4);

        $this->assertEquals(6, $stats->getMale());
        $this->assertEquals(4, $stats->getFemale());
    }

    public function testGetTotal()
    {
        $stats = new GenderStats(6, 4);

        $this->assertEquals(10, $stats->getTotal());
    }

    public function testMalePercentage()
    {
        $stats = new GenderStats(6, 4);

        $this->assertEquals(60.0, $stats->getMalePercentage());
    }

    public function testFemalePercentage()
    {
        $stats = new GenderStats(6, 4);

        $this->assertEquals(40.0, $stats->getFemalePercentage());
    }

    public function testPercentagesWithZeroTotal()
    {
        $stats = new GenderStats(0, 0);

        $this->assertEquals(0, $stats->getMalePercentage());
        $this->assertEquals(0, $stats->getFemalePercentage());
    }

    public function testToArray()
    {
        $stats = new GenderStats(3, 7);

        $expected = [
            'male' => 3,
            'female' => 7
        ];

        $this->assertEquals($expected, $stats->toArray());
    }
}
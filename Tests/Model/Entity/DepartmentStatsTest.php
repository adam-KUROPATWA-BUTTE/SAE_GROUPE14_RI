<?php

namespace Tests\Model\Entity;

use PHPUnit\Framework\TestCase;
use Model\Entity\DepartmentStats;

class DepartmentStatsTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $departmentStats = new DepartmentStats("Informatique", 15);

        $this->assertEquals("Informatique", $departmentStats->getName());
        $this->assertEquals(15, $departmentStats->getCount());
    }

    public function testToArray(): void
    {
        $departmentStats = new DepartmentStats("Réseaux", 8);

        $expected = [
            'name' => 'Réseaux',
            'count' => 8
        ];

        $this->assertEquals($expected, $departmentStats->toArray());
    }
}
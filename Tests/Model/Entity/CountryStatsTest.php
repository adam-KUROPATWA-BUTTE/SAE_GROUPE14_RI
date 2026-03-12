<?php

namespace Model\Entity;

use PHPUnit\Framework\TestCase;

class CountryStatsTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $countryStats = new CountryStats("France", 42);

        $this->assertEquals("France", $countryStats->getName());
        $this->assertEquals(42, $countryStats->getCount());
    }

    public function testToArray(): void
    {
        $countryStats = new CountryStats("Espagne", 10);

        $expected = [
            'name'  => 'Espagne',
            'count' => 10,
        ];

        $this->assertEquals($expected, $countryStats->toArray());
    }
}
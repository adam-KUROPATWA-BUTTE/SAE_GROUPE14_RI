<?php

namespace Model\Entity;


use PHPUnit\Framework\TestCase;

class CountryStatsTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $countryStats = new CountryStats("France", 12);

        $this->assertEquals("France", $countryStats->getName());
        $this->assertEquals(12, $countryStats->getCount());
    }

    public function testToArray()
    {
        $countryStats = new CountryStats("Spain", 7);

        $expected = [
            'name' => 'Spain',
            'count' => 7
        ];

        $this->assertEquals($expected, $countryStats->toArray());
    }
}
<?php

namespace Tests\Model\Entity;



use PHPUnit\Framework\TestCase;
use Model\Entity\Partner;

class PartnerTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $partner = new Partner(
            "Europe",
            "France",
            "Paris",
            "Université Paris",
            "University"
        );

        $this->assertEquals("Europe", $partner->getContinent());
        $this->assertEquals("France", $partner->getCountry());
        $this->assertEquals("Paris", $partner->getCity());
        $this->assertEquals("Université Paris", $partner->getInstitution());
        $this->assertEquals("University", $partner->getType());
    }
}
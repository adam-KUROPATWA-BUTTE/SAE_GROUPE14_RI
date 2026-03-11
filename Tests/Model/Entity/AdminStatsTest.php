<?php

namespace Model\Entity;

use PHPUnit\Framework\TestCase;

class AdminStatsTest extends TestCase
{
    public function testGetters()
    {
        $dossierStats = $this->createMock(DossierStats::class);
        $genderStats = $this->createMock(GenderStats::class);

        $country = $this->createMock(CountryStats::class);
        $department = $this->createMock(DepartmentStats::class);

        $topCountries = [$country];
        $departments = [$department];

        $adminStats = new AdminStats(
            $dossierStats,
            $topCountries,
            $genderStats,
            $departments,
            10,
            5,
            [['name' => 'Europe', 'count' => 3]],
            8,
            2
        );

        $this->assertSame($dossierStats, $adminStats->getDossierStats());
        $this->assertSame($topCountries, $adminStats->getTopCountries());
        $this->assertSame($genderStats, $adminStats->getGenderStats());
        $this->assertSame($departments, $adminStats->getDepartments());

        $this->assertEquals(10, $adminStats->getIncomingStudents());
        $this->assertEquals(5, $adminStats->getOutgoingStudents());

        $this->assertEquals([['name' => 'Europe', 'count' => 3]], $adminStats->getZoneStats());

        $this->assertEquals(8, $adminStats->getEuropeCountriesCount());
        $this->assertEquals(2, $adminStats->getNonEuropeCountriesCount());
    }

    public function testToArray()
    {
        $dossierStats = $this->createMock(DossierStats::class);
        $dossierStats->method('getCompleted')->willReturn(7);
        $dossierStats->method('getTotal')->willReturn(10);

        $country = $this->createMock(CountryStats::class);
        $country->method('toArray')->willReturn(['country' => 'France']);

        $genderStats = $this->createMock(GenderStats::class);
        $genderStats->method('toArray')->willReturn(['male' => 5, 'female' => 5]);

        $department = $this->createMock(DepartmentStats::class);
        $department->method('toArray')->willReturn(['department' => 'Info']);

        $adminStats = new AdminStats(
            $dossierStats,
            [$country],
            $genderStats,
            [$department],
            4,
            6,
            [['name' => 'Europe', 'count' => 3]],
            8,
            2
        );

        $result = $adminStats->toArray();

        $this->assertEquals(7, $result['complete_folders']);
        $this->assertEquals(3, $result['incomplete_folders']);
        $this->assertEquals(10, $result['total_folders']);

        $this->assertEquals([['country' => 'France']], $result['top_countries']);
        $this->assertEquals(['male' => 5, 'female' => 5], $result['gender']);
        $this->assertEquals([['department' => 'Info']], $result['departments']);

        $this->assertEquals(4, $result['incoming_students']);
        $this->assertEquals(6, $result['outgoing_students']);

        $this->assertEquals([['name' => 'Europe', 'count' => 3]], $result['top_continents']);

        $this->assertEquals(8, $result['europe_countries_count']);
        $this->assertEquals(2, $result['non_europe_countries_count']);
        $this->assertEquals(10, $result['total_countries_count']);
    }
}
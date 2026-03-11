<?php

namespace Tests\Model\UseCase;

use Model\Entity\AdminStats;
use Model\Entity\DossierStats;
use Model\Entity\GenderStats;
use Model\Repository\DossierRepositoryInterface;
use Model\UseCase\GetAdminStatsUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GetAdminStatsUseCaseTest extends TestCase
{
    /** @var DossierRepositoryInterface&MockObject */
    private DossierRepositoryInterface $repoMock;

    private GetAdminStatsUseCase $useCase;

    protected function setUp(): void
    {
        $this->repoMock = $this->createMock(DossierRepositoryInterface::class);
        $this->useCase  = new GetAdminStatsUseCase($this->repoMock);
    }

    private function setupDefaultRepoMock(?string $mobilite = null, ?string $departement = null): void
    {
        $this->repoMock->method('getDossierStats')
            ->with($mobilite, $departement)
            ->willReturn(new DossierStats(10, 5));

        $this->repoMock->method('getGenderStats')
            ->with($mobilite, $departement)
            ->willReturn(new GenderStats(6, 4));

        $this->repoMock->method('getIncomingOutgoingStats')
            ->with($mobilite, $departement)
            ->willReturn(['incoming' => 3, 'outgoing' => 7]);

        $this->repoMock->method('getContinentStats')
            ->with($mobilite, $departement)
            ->willReturn([['name' => 'Europe', 'count' => 8]]);

        $this->repoMock->method('getEuropeVsNonEuropeStats')
            ->with($mobilite, $departement)
            ->willReturn(['europe_countries' => 5, 'non_europe_countries' => 3]);

        $this->repoMock->method('getTopCountries')
            ->with(5, $mobilite, $departement)
            ->willReturn([['name' => 'Allemagne', 'count' => 4]]);

        $this->repoMock->method('getDepartmentStats')
            ->with(5, $mobilite, $departement)
            ->willReturn([['name' => 'INFO', 'count' => 6]]);
    }

    // =========================================================
    // Retour AdminStats
    // =========================================================

    public function testExecuteReturnsAdminStats(): void
    {
        $this->setupDefaultRepoMock();

        $result = $this->useCase->execute();

        $this->assertInstanceOf(AdminStats::class, $result);
    }

    public function testExecuteWithNoFiltersPassesNullToAllMethods(): void
    {
        $this->setupDefaultRepoMock(null, null);

        $result = $this->useCase->execute();

        $this->assertInstanceOf(AdminStats::class, $result);
    }

    // =========================================================
    // Filtrage mobilité
    // =========================================================

    public function testExecuteWithEtudeFilterPassesMobiliteToRepo(): void
    {
        $this->setupDefaultRepoMock('etude', null);

        $result = $this->useCase->execute('etude');

        $this->assertInstanceOf(AdminStats::class, $result);
    }

    public function testExecuteWithStageFilterPassesMobiliteToRepo(): void
    {
        $this->setupDefaultRepoMock('stage', null);

        $result = $this->useCase->execute('stage');

        $this->assertInstanceOf(AdminStats::class, $result);
    }

    public function testExecuteWithInvalidMobilitePassesNullToRepo(): void
    {
        // mobilite invalide → doit être remplacé par null
        $this->setupDefaultRepoMock(null, null);

        $result = $this->useCase->execute('invalid');

        $this->assertInstanceOf(AdminStats::class, $result);
    }

    // =========================================================
    // Filtrage département
    // =========================================================

    public function testExecuteWithDepartementFilterPassesDepartementToRepo(): void
    {
        $this->setupDefaultRepoMock(null, 'INFO');

        $result = $this->useCase->execute(null, 'INFO');

        $this->assertInstanceOf(AdminStats::class, $result);
    }

    public function testExecuteWithEmptyDepartementPassesNullToRepo(): void
    {
        // chaîne vide → null
        $this->setupDefaultRepoMock(null, null);

        $result = $this->useCase->execute(null, '');

        $this->assertInstanceOf(AdminStats::class, $result);
    }

    // =========================================================
    // Appels au repository
    // =========================================================

    public function testExecuteCallsAllRepositoryMethods(): void
    {
        $this->repoMock->expects($this->once())->method('getDossierStats')
            ->willReturn(new DossierStats(0, 0));
        $this->repoMock->expects($this->once())->method('getGenderStats')
            ->willReturn(new GenderStats(0, 0));
        $this->repoMock->expects($this->once())->method('getIncomingOutgoingStats')
            ->willReturn(['incoming' => 0, 'outgoing' => 0]);
        $this->repoMock->expects($this->once())->method('getContinentStats')
            ->willReturn([]);
        $this->repoMock->expects($this->once())->method('getEuropeVsNonEuropeStats')
            ->willReturn(['europe_countries' => 0, 'non_europe_countries' => 0]);
        $this->repoMock->expects($this->once())->method('getTopCountries')
            ->willReturn([]);
        $this->repoMock->expects($this->once())->method('getDepartmentStats')
            ->willReturn([]);

        $this->useCase->execute();
    }

    public function testExecuteRequestsTop5Countries(): void
    {
        $this->repoMock->method('getDossierStats')->willReturn(new DossierStats(0, 0));
        $this->repoMock->method('getGenderStats')->willReturn(new GenderStats(0, 0));
        $this->repoMock->method('getIncomingOutgoingStats')->willReturn(['incoming' => 0, 'outgoing' => 0]);
        $this->repoMock->method('getContinentStats')->willReturn([]);
        $this->repoMock->method('getEuropeVsNonEuropeStats')->willReturn(['europe_countries' => 0, 'non_europe_countries' => 0]);
        $this->repoMock->method('getDepartmentStats')->willReturn([]);

        $this->repoMock->expects($this->once())
            ->method('getTopCountries')
            ->with(5)
            ->willReturn([]);

        $this->useCase->execute();
    }

    public function testExecuteRequestsTop5Departments(): void
    {
        $this->repoMock->method('getDossierStats')->willReturn(new DossierStats(0, 0));
        $this->repoMock->method('getGenderStats')->willReturn(new GenderStats(0, 0));
        $this->repoMock->method('getIncomingOutgoingStats')->willReturn(['incoming' => 0, 'outgoing' => 0]);
        $this->repoMock->method('getContinentStats')->willReturn([]);
        $this->repoMock->method('getEuropeVsNonEuropeStats')->willReturn(['europe_countries' => 0, 'non_europe_countries' => 0]);
        $this->repoMock->method('getTopCountries')->willReturn([]);

        $this->repoMock->expects($this->once())
            ->method('getDepartmentStats')
            ->with(5)
            ->willReturn([]);

        $this->useCase->execute();
    }

    // =========================================================
    // Mapping CountryStats / DepartmentStats
    // =========================================================

    public function testExecuteMapsTopCountriesToCountryStatsObjects(): void
    {
        $this->repoMock->method('getDossierStats')->willReturn(new DossierStats(0, 0));
        $this->repoMock->method('getGenderStats')->willReturn(new GenderStats(0, 0));
        $this->repoMock->method('getIncomingOutgoingStats')->willReturn(['incoming' => 0, 'outgoing' => 0]);
        $this->repoMock->method('getContinentStats')->willReturn([]);
        $this->repoMock->method('getEuropeVsNonEuropeStats')->willReturn(['europe_countries' => 0, 'non_europe_countries' => 0]);
        $this->repoMock->method('getDepartmentStats')->willReturn([]);
        $this->repoMock->method('getTopCountries')->willReturn([
            ['name' => 'Espagne', 'count' => 3],
            ['name' => 'Japon',   'count' => 2],
        ]);

        $stats = $this->useCase->execute();

        $countries = $stats->getTopCountries();
        $this->assertCount(2, $countries);
        $this->assertSame('Espagne', $countries[0]->getName());
        $this->assertSame(3,         $countries[0]->getCount());
    }

    public function testExecuteMapsDepartmentsToDepartmentStatsObjects(): void
    {
        $this->repoMock->method('getDossierStats')->willReturn(new DossierStats(0, 0));
        $this->repoMock->method('getGenderStats')->willReturn(new GenderStats(0, 0));
        $this->repoMock->method('getIncomingOutgoingStats')->willReturn(['incoming' => 0, 'outgoing' => 0]);
        $this->repoMock->method('getContinentStats')->willReturn([]);
        $this->repoMock->method('getEuropeVsNonEuropeStats')->willReturn(['europe_countries' => 0, 'non_europe_countries' => 0]);
        $this->repoMock->method('getTopCountries')->willReturn([]);
        $this->repoMock->method('getDepartmentStats')->willReturn([
            ['name' => 'MATH', 'count' => 9],
        ]);

        $stats = $this->useCase->execute();

        $depts = $stats->getDepartments();
        $this->assertCount(1, $depts);
        $this->assertSame('MATH', $depts[0]->getName());
        $this->assertSame(9,      $depts[0]->getCount());
    }

    // =========================================================
    // Combinaison des filtres
    // =========================================================

    public function testExecuteWithBothFilters(): void
    {
        $this->setupDefaultRepoMock('stage', 'MATH');

        $result = $this->useCase->execute('stage', 'MATH');

        $this->assertInstanceOf(AdminStats::class, $result);
    }
}
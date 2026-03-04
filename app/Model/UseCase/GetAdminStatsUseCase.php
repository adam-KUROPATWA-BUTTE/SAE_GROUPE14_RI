<?php

namespace Model\UseCase;

use Model\Entity\AdminStats;
use Model\Entity\CountryStats;
use Model\Entity\DepartmentStats;
use Model\Repository\DossierRepositoryInterface;

class GetAdminStatsUseCase
{
    private DossierRepositoryInterface $repository;

    public function __construct(DossierRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string|null $mobilite null = tous | 'etude' | 'stage'
     */
    public function execute(?string $mobilite = null): AdminStats
    {
        $filter = in_array($mobilite, ['etude', 'stage'], true) ? $mobilite : null;

        $dossierStats = $this->repository->getDossierStats($filter);
        $genderStats  = $this->repository->getGenderStats($filter);
        $mobility     = $this->repository->getIncomingOutgoingStats($filter);
        $continents   = $this->repository->getContinentStats($filter);
        $europeStats  = $this->repository->getEuropeVsNonEuropeStats($filter);


        $topCountries = array_map(
            fn($row) => new CountryStats($row['name'], $row['count']),
            $this->repository->getTopCountries(5, $filter)
        );

        $departments = array_map(
            fn($row) => new DepartmentStats($row['name'], $row['count']),
            $this->repository->getDepartmentStats(5, $filter)
        );

        return new AdminStats(
            dossierStats:            $dossierStats,
            topCountries:            $topCountries,
            genderStats:             $genderStats,
            departments:             $departments,
            incomingStudents:        $mobility['incoming'],
            outgoingStudents:        $mobility['outgoing'],
            zoneStats:               $continents,
            europeCountriesCount:    $europeStats['europe_countries'],
            nonEuropeCountriesCount: $europeStats['non_europe_countries'],
        );
    }
}
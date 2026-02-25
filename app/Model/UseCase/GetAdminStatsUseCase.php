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

    public function execute(): AdminStats
    {
        $dossierStats = $this->repository->getDossierStats();
        $genderStats  = $this->repository->getGenderStats();
        $mobility     = $this->repository->getIncomingOutgoingStats();
        $zoneStats    = $this->repository->getContinentStats();
        $europeStats  = $this->repository->getEuropeVsNonEuropeStats();

        // Conversion des tableaux bruts en objets
        $topCountries = array_map(
            fn($row) => new CountryStats(
                is_string($row['name'])    ? $row['name']    : '',
                is_numeric($row['count'])  ? (int) $row['count'] : 0
            ),
            $this->repository->getTopCountries(5)
        );

        $departments = array_map(
            fn($row) => new DepartmentStats(
                is_string($row['name'])    ? $row['name']    : '',
                is_numeric($row['count'])  ? (int) $row['count'] : 0
            ),
            $this->repository->getDepartmentStats(5)
        );

        return new AdminStats(
            dossierStats:            $dossierStats,
            topCountries:            $topCountries,
            genderStats:             $genderStats,
            departments:             $departments,
            incomingStudents:        $mobility['incoming'],
            outgoingStudents:        $mobility['outgoing'],
            zoneStats:               $zoneStats,
            europeCountriesCount:    $europeStats['europe_countries'],
            nonEuropeCountriesCount: $europeStats['non_europe_countries'],
        );
    }
}
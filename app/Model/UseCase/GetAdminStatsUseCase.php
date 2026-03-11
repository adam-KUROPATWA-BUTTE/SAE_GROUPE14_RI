<?php

namespace Model\UseCase;

use Model\Entity\AdminStats;
use Model\Entity\CountryStats;
use Model\Entity\DepartmentStats;
use Model\Repository\FolderRepositoryInterface;

class GetAdminStatsUseCase
{
    private FolderRepositoryInterface $repository;

    public function __construct(FolderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string|null $mobilite null = tous | 'etude' | 'stage'
     * @param string|null $departement Code du département ou null pour tous
     */
    public function execute(?string $mobilite = null, ?string $departement = null): AdminStats
    {
        $mobiliteFilter = in_array($mobilite, ['etude', 'stage'], true) ? $mobilite : null;
        $departementFilter = !empty($departement) ? $departement : null;

        // Passer les deux filtres à toutes les méthodes du repository
        $dossierStats = $this->repository->getDossierStats($mobiliteFilter, $departementFilter);
        $genderStats  = $this->repository->getGenderStats($mobiliteFilter, $departementFilter);
        $mobility     = $this->repository->getIncomingOutgoingStats($mobiliteFilter, $departementFilter);
        $continents   = $this->repository->getContinentStats($mobiliteFilter, $departementFilter);
        $europeStats  = $this->repository->getEuropeVsNonEuropeStats($mobiliteFilter, $departementFilter);

        $topCountries = array_map(
            fn($row) => new CountryStats($row['name'], $row['count']),
            $this->repository->getTopCountries(5, $mobiliteFilter, $departementFilter)
        );

        $departments = array_map(
            fn($row) => new DepartmentStats($row['name'], $row['count']),
            $this->repository->getDepartmentStats(5, $mobiliteFilter, $departementFilter)
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
<?php

namespace Model\UseCase;

use Model\Entity\AdminStats;
use Model\Entity\CountryStats;
use Model\Entity\DepartmentStats;
use Model\Repository\DossierRepositoryInterface;

/**
 * Class GetAdminStatsUseCase
 *
 * Orchestre la récupération de toutes les statistiques admin.
 * Accepte un filtre de mobilité optionnel : null | 'etude' | 'stage'
 */
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
        // Validation stricte ici aussi (défense en profondeur)
        $filter = in_array($mobilite, ['etude', 'stage'], true) ? $mobilite : null;

        $dossierStats = $this->repository->getDossierStats($filter);
        $genderStats  = $this->repository->getGenderStats($filter);
        $mobility     = $this->repository->getIncomingOutgoingStats($filter);
        $continents   = $this->repository->getContinentStats($filter);
        $europeStats  = $this->repository->getEuropeVsNonEuropeStats($filter);

        $topCountries = array_map(
            fn($row) => new CountryStats(
                is_string($row['name'])   ? $row['name']   : '',
                is_numeric($row['count']) ? (int) $row['count'] : 0
            ),
            $this->repository->getTopCountries(5, $filter)
        );

        $departments = array_map(
            fn($row) => new DepartmentStats(
                is_string($row['name'])   ? $row['name']   : '',
                is_numeric($row['count']) ? (int) $row['count'] : 0
            ),
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
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
        $genderStats = $this->repository->getGenderStats();

        $topCountriesData = $this->repository->getTopCountries(5);
        $topCountries = array_map(
            fn($data) => new CountryStats($data['name'], $data['count']),
            $topCountriesData
        );

        $departmentsData = $this->repository->getDepartmentStats(5);
        $departments = array_map(
            fn($data) => new DepartmentStats($data['name'], $data['count']),
            $departmentsData
        );

        return new AdminStats(
            $dossierStats,
            $topCountries,
            $genderStats,
            $departments
        );
    }
}
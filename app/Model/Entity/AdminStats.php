<?php

namespace Model\Entity;

class AdminStats
{
    private DossierStats $dossierStats;
    /** @var array<int, CountryStats> */
    private array $topCountries;
    private GenderStats $genderStats;
    /** @var array<int, DepartmentStats> */
    private array $departments;

    /**
     * @param array<int, CountryStats> $topCountries
     * @param array<int, DepartmentStats> $departments
     */
    public function __construct(
        DossierStats $dossierStats,
        array $topCountries,
        GenderStats $genderStats,
        array $departments
    ) {
        $this->dossierStats = $dossierStats;
        $this->topCountries = $topCountries;
        $this->genderStats = $genderStats;
        $this->departments = $departments;
    }

    public function getDossierStats(): DossierStats
    {
        return $this->dossierStats;
    }

    /** @return array<int, CountryStats> */
    public function getTopCountries(): array
    {
        return $this->topCountries;
    }

    public function getGenderStats(): GenderStats
    {
        return $this->genderStats;
    }

    /** @return array<int, DepartmentStats> */
    public function getDepartments(): array
    {
        return $this->departments;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'complete_folders' => $this->dossierStats->getCompleted(),
            'incomplete_folders' => $this->dossierStats->getTotal() - $this->dossierStats->getCompleted(),
            'total_folders' => $this->dossierStats->getTotal(),
            'top_countries' => array_map(fn($c) => $c->toArray(), $this->topCountries),
            'gender' => $this->genderStats->toArray(),
            'departments' => array_map(fn($d) => $d->toArray(), $this->departments)
        ];
    }
}
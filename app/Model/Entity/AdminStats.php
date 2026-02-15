<?php

namespace Model\Entity;

class AdminStats
{
    private DossierStats $dossierStats;
    private array $topCountries;
    private GenderStats $genderStats;
    private array $departments;

    /**
     * @param DossierStats $dossierStats
     * @param array<int, CountryStats> $topCountries
     * @param GenderStats $genderStats
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

    public function getTopCountries(): array
    {
        return $this->topCountries;
    }

    public function getGenderStats(): GenderStats
    {
        return $this->genderStats;
    }

    public function getDepartments(): array
    {
        return $this->departments;
    }

    /**
     * Convertit en tableau pour la vue
     */
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
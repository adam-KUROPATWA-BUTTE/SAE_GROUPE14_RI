<?php

namespace Model\Entity;

class AdminStats
{
    private FolderStats $dossierStats;
    /** @var array<int, CountryStats> */
    private array $topCountries;
    private GenderStats $genderStats;
    /** @var array<int, DepartmentStats> */
    private array $departments;
    private int $incomingStudents;
    private int $outgoingStudents;
    /** @var array<int, array{name: string, count: int}> */
    private array $zoneStats;
    private int $europeCountriesCount;
    private int $nonEuropeCountriesCount;

    /**
     * @param array<int, CountryStats> $topCountries
     * @param array<int, DepartmentStats> $departments
     * @param array<int, array{name: string, count: int}> $zoneStats
     */
    public function __construct(
        FolderStats $dossierStats,
        array       $topCountries,
        GenderStats $genderStats,
        array       $departments,
        int         $incomingStudents = 0,
        int         $outgoingStudents = 0,
        array       $zoneStats = [],
        int         $europeCountriesCount = 0,
        int         $nonEuropeCountriesCount = 0,
    ) {
        $this->dossierStats            = $dossierStats;
        $this->topCountries            = $topCountries;
        $this->genderStats             = $genderStats;
        $this->departments             = $departments;
        $this->incomingStudents        = $incomingStudents;
        $this->outgoingStudents        = $outgoingStudents;
        $this->zoneStats               = $zoneStats;
        $this->europeCountriesCount    = $europeCountriesCount;
        $this->nonEuropeCountriesCount = $nonEuropeCountriesCount;
    }

    public function getDossierStats(): FolderStats
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

    public function getIncomingStudents(): int
    {
        return $this->incomingStudents;
    }

    public function getOutgoingStudents(): int
    {
        return $this->outgoingStudents;
    }

    /** @return array<int, array{name: string, count: int}> */
    public function getZoneStats(): array
    {
        return $this->zoneStats;
    }

    public function getEuropeCountriesCount(): int
    {
        return $this->europeCountriesCount;
    }

    public function getNonEuropeCountriesCount(): int
    {
        return $this->nonEuropeCountriesCount;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'complete_folders'           => $this->dossierStats->getCompleted(),
            'incomplete_folders'         => $this->dossierStats->getTotal() - $this->dossierStats->getCompleted(),
            'total_folders'              => $this->dossierStats->getTotal(),
            'top_countries'              => array_map(fn($c) => $c->toArray(), $this->topCountries),
            'gender'                     => $this->genderStats->toArray(),
            'departments'                => array_map(fn($d) => $d->toArray(), $this->departments),
            'incoming_students'          => $this->incomingStudents,
            'outgoing_students'          => $this->outgoingStudents,
            'top_continents'             => $this->zoneStats,
            'europe_countries_count'     => $this->europeCountriesCount,
            'non_europe_countries_count' => $this->nonEuropeCountriesCount,
            'total_countries_count'      => $this->europeCountriesCount + $this->nonEuropeCountriesCount,
        ];
    }
}
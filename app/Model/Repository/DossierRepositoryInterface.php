<?php

namespace Model\Repository;

use Model\Entity\DossierStats;
use Model\Entity\GenderStats;

interface DossierRepositoryInterface
{
    public function getDossierStats(): DossierStats;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getTopCountries(int $limit): array;

    public function getGenderStats(): GenderStats;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getDepartmentStats(int $limit): array;
}
<?php

namespace Model\Repository;

use Model\Entity\DossierStats;
use Model\Entity\GenderStats;

/**
 * Interface DossierRepositoryInterface
 */
interface DossierRepositoryInterface
{


    /** @return array<int, array<string, mixed>> */
    public function findAll(): array;

    /** @return array<string, mixed>|null */
    public function findByNumEtu(string $numEtu): ?array;

    /** @param array<string, mixed> $data */
    public function create(array $data): bool;

    /** @param array<string, mixed> $data */
    public function update(string $numEtu, array $data): bool;

    public function toggleCompleteStatus(string $numEtu): bool;


    public function setStatus(string $numEtu, string $status): bool;
    public function cycleStatus(string $numEtu): bool;


    /**
     * @param array<string, mixed> $filters
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int}
     */
    public function searchWithPagination(array $filters, int $page, int $perPage): array;

    /** @param array<int, array<string, mixed>> $dossiers */
    public function upsertMultiple(array $dossiers): int;



    public function getDossierStats(?string $mobilite = null): DossierStats;

    public function getGlobalStats(): DossierStats;

    public function getGenderStats(?string $mobilite = null): GenderStats;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getTopCountries(int $limit, ?string $mobilite = null): array;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getDepartmentStats(int $limit, ?string $mobilite = null): array;

    /**
     * @return array{incoming: int, outgoing: int}
     */
    public function getIncomingOutgoingStats(?string $mobilite = null): array;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getContinentStats(?string $mobilite = null): array;

    /**
     * @return array{europe_countries: int, non_europe_countries: int}
     */
    public function getEuropeVsNonEuropeStats(?string $mobilite = null): array;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getZoneStats(?string $mobilite = null): array;
}
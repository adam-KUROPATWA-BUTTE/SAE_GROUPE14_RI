<?php

namespace Model\Repository;

use Model\Entity\FolderStats;
use Model\Entity\GenderStats;

/**
 * Interface FolderRepositoryInterface
 */
interface FolderRepositoryInterface
{
    // ---------------------------------------------------------------
    // CRUD
    // ---------------------------------------------------------------

    /** @return array<int, array<string, mixed>> */
    public function findAll(): array;

    /** @return array<string, mixed>|null */
    public function findByNumEtu(string $numEtu): ?array;

    /** @param array<string, mixed> $data */
    public function create(array $data): bool;

    /** @param array<string, mixed> $data */
    public function update(string $numEtu, array $data): bool;

    // ---------------------------------------------------------------
    // Status
    // ---------------------------------------------------------------

    /** Toggle via direct flip */
    public function toggleStatus(string $numEtu): bool;

    /** Toggle via PDO select+update — kept for compatibility */
    public function toggleCompleteStatus(string $numEtu): bool;

    public function setStatus(string $numEtu, string $status): bool;

    public function cycleStatus(string $numEtu): bool;

    /**
     * Saves the department head's opinion ('accepte', 'refuse', or null to reset).
     */
    public function setAvisChef(string $numEtu, ?string $avis): bool;

    // ---------------------------------------------------------------
    // Pagination & search
    // ---------------------------------------------------------------

    /**
     * @param array<string, mixed> $filters
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int}
     */
    public function searchWithPagination(array $filters, int $page, int $perPage): array;

    // ---------------------------------------------------------------
    // Import
    // ---------------------------------------------------------------

    /** @param array<int, array<string, mixed>> $dossiers */
    public function upsertMultiple(array $dossiers): int;

    // ---------------------------------------------------------------
    // Statistics
    // ---------------------------------------------------------------

    public function getDossierStats(?string $mobilite = null, ?string $departement = null): FolderStats;

    public function getGlobalStats(): FolderStats;

    public function getGenderStats(?string $mobilite = null, ?string $departement = null): GenderStats;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getTopCountries(int $limit, ?string $mobilite = null, ?string $departement = null): array;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getDepartmentStats(int $limit, ?string $mobilite = null, ?string $departement = null): array;

    /**
     * @return array{incoming: int, outgoing: int}
     */
    public function getIncomingOutgoingStats(?string $mobilite = null, ?string $departement = null): array;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getContinentStats(?string $mobilite = null, ?string $departement = null): array;

    /**
     * @return array{europe_countries: int, non_europe_countries: int}
     */
    public function getEuropeVsNonEuropeStats(?string $mobilite = null, ?string $departement = null): array;

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getZoneStats(?string $mobilite = null): array;

    // ---------------------------------------------------------------
    // Document validation
    // ---------------------------------------------------------------

    /**
     * @return array{manquants: array<int, string>, presents: array<int, string>, statuts: array<string, string>}
     */
    public function analyserDocuments(string $numetu): array;

    /**
     * @param array<string, string> $statutsDocuments
     */
    public function enregistrerValidation(
        string $numetu,
        array $statutsDocuments,
        ?string $dateLimite = null,
        ?string $commentaire = null
    ): bool;

    /**
     * Returns folders where IsComplete = 0 or IS NULL.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findIncompleteFolders(): array;
}
<?php

namespace Model\Repository;

use Model\Entity\DossierStats;
use Model\Entity\GenderStats;

/**
 * Interface DossierRepositoryInterface
 * Defines the contract for data access operations related to student folders (Dossiers).
 */
interface DossierRepositoryInterface
{
<<<<<<< HEAD
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
=======
    /**
     * Retrieves global statistics about the folders.
     *
     * @return DossierStats
     */
    public function getGlobalStats(): DossierStats;

    /**
     * Inserts or updates multiple folders in the database.
     *
     * @param array<int, array<string, mixed>> $dossiers List of folders to process.
     * @return int The number of rows successfully inserted or updated.
     */
    public function upsertMultiple(array $dossiers): int;

    /**
     * Creates a new student folder in the database.
     *
     * @param array<string, mixed> $data The folder data to insert.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool;

    /**
     * Updates an existing student folder in the database.
     *
     * @param string $numEtu The student ID (NumEtu) of the folder to update.
     * @param array<string, mixed> $data The updated folder data.
     * @return bool True on success, false on failure.
     */
    public function update(string $numEtu, array $data): bool;

    /**
     * Retrieves all student folders from the database.
     *
     * @return array<int, array<string, mixed>> A list of all folders.
     */
    public function findAll(): array;

    /**
     * Finds a specific student folder by their student ID.
     *
     * @param string $numEtu The student ID to search for.
     * @return array<string, mixed>|null The folder data, or null if not found.
     */
    public function findByNumEtu(string $numEtu): ?array;

    /**
     * Toggles the completion status (IsComplete) of a specific folder.
     *
     * @param string $numEtu The student ID whose folder status should be toggled.
     * @return bool True on success, false on failure.
     */
    public function toggleStatus(string $numEtu): bool;
    
    /**
     * Searches for folders based on provided filters and paginates the results.
     *
     * @param array<string, mixed> $filters Filtering criteria (e.g., status, zone, type).
     * @param int $page The current page number.
     * @param int $perPage The number of results per page.
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int} The paginated search results.
     */
    public function searchWithPagination(array $filters, int $page, int $perPage): array;
>>>>>>> Separation-models-folders-pour-les-mettres-en-usecase
}
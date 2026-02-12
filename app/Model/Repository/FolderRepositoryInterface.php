<?php

namespace Model\Repository;

interface FolderRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByNumEtu(string $numEtu): ?array;

    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): bool;

    /**
     * @param array<string, mixed> $data
     */
    public function update(array $data): bool;

    public function delete(string $numEtu): bool;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array;
    
    /**
     * @return array<int, array<string, mixed>>
     */
    public function findIncompleteFolders(): array;
}

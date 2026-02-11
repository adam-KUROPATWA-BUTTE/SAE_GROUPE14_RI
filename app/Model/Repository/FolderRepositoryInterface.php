<?php

namespace Model\Repository;

interface FolderRepositoryInterface
{
    public function findByNumEtu(string $numEtu): ?array;
    public function save(array $data): bool;
    public function update(array $data): bool;
    public function delete(string $numEtu): bool;
    public function getAll(): array;
}
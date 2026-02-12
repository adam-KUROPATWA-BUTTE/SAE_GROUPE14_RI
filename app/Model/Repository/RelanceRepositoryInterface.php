<?php

namespace Model\Repository;

interface RelanceRepositoryInterface
{
    public function wasRecentlySent(string $numEtu, int $days): bool;

    public function save(string $numEtu, string $message): void;
}

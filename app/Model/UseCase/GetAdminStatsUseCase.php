<?php

namespace Model\UseCase;

use Model\Repository\DossierRepositoryInterface;

class GetAdminStatsUseCase
{
    private DossierRepositoryInterface $repository;

    public function __construct(DossierRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): float
    {
        $stats = $this->repository->getGlobalStats();
        return $stats->getCompletionPercentage();
    }
}

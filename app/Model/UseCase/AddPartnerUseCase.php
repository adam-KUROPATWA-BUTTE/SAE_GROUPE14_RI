<?php

namespace Model\UseCase;

use Model\Repository\PartnerRepositoryInterface;
use Model\Entity\Partner;
use PDOException;

class AddPartnerUseCase
{
    private PartnerRepositoryInterface $repository;

    public function __construct(PartnerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(Partner $partner): void
    {
        $this->repository->addPartner($partner);
    }
}

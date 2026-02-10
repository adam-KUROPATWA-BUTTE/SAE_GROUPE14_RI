<?php

namespace Model\Repository;

use Model\Entity\Partner;

interface PartnerRepositoryInterface
{
    public function addPartner(Partner $partner): void;
}

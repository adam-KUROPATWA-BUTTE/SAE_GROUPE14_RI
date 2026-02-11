<?php

namespace Model\Repository;

use Model\Entity\DossierStats;

interface DossierRepositoryInterface
{
    public function getGlobalStats(): DossierStats;
}

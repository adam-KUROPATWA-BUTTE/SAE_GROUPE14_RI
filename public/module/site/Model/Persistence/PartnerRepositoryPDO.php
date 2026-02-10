<?php

namespace Model\Persistence;

use Model\Repository\PartnerRepositoryInterface;
use Model\Entity\Partner;
use Database;
use PDOException;

class PartnerRepositoryPDO implements PartnerRepositoryInterface
{
    public function addPartner(Partner $partner): void
    {
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO Partenaires (continent, pays, ville, universite_institution)
            VALUES (:continent, :pays, :ville, :universite)
        ");

        $stmt->execute([
            'continent'  => $partner->getContinent(),
            'pays'       => $partner->getCountry(),
            'ville'      => $partner->getCity(),
            'universite' => $partner->getInstitution()
        ]);
    }
}

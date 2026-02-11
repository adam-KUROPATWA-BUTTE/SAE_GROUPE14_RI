<?php

namespace Model\Persistence;

use Model\Repository\DossierRepositoryInterface;
use Model\Entity\DossierStats;
use Database;
use PDO;

class DossierRepositoryPDO implements DossierRepositoryInterface
{
    public function getGlobalStats(): DossierStats
    {
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->query(
            "SELECT COUNT(*) AS total, SUM(IsComplete) AS completed FROM dossiers"
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int)($row['total'] ?? 0);
        $completed = (int)($row['completed'] ?? 0);

        return new DossierStats($total, $completed);
    }
}

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

        if ($stmt === false) {
            return new DossierStats(0, 0);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false || !is_array($row)) {
            return new DossierStats(0, 0);
        }

        $totalVal = $row['total'] ?? 0;
        $completedVal = $row['completed'] ?? 0;

        $total = is_numeric($totalVal) ? (int)$totalVal : 0;
        $completed = is_numeric($completedVal) ? (int)$completedVal : 0;

        return new DossierStats($total, $completed);
    }
}
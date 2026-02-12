<?php

namespace Model\Persistence;

use Model\Repository\RelanceRepositoryInterface;
use Database;
use PDO;

class RelanceRepositoryPDO implements RelanceRepositoryInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function wasRecentlySent(string $numEtu, int $days): bool
    {
        $sql = "
            SELECT 1 FROM relances 
            WHERE dossier_id = :id 
            AND date_relance >= (NOW() - INTERVAL :days DAY)
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $numEtu);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (bool)$stmt->fetchColumn();
    }

    public function save(string $numEtu, string $message): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO relances (dossier_id, message, envoye_par)
            VALUES (:id, :message, NULL)
        ");

        $stmt->execute([
            'id' => $numEtu,
            'message' => $message
        ]);
    }
}

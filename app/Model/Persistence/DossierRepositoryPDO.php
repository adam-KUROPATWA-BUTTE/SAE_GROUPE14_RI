<?php

namespace Model\Persistence;

use Model\Repository\DossierRepositoryInterface;
use Model\Entity\DossierStats;
use Database;
use PDO;

/**
 * Class DossierRepositoryPDO
 * PDO implementation of the DossierRepositoryInterface.
 */
class DossierRepositoryPDO implements DossierRepositoryInterface
{
    /**
     * @return DossierStats
     */
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

    /**
     * @param array<int, array<string, mixed>> $dossiers
     * @return int
     */
    public function upsertMultiple(array $dossiers): int
    {
        $pdo = Database::getInstance()->getConnection();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO dossiers (NumEtu, Nom, Prenom, EmailPersonnel, Telephone, Type, Zone, IsComplete, PiecesJustificatives) 
                VALUES (:num, :nom, :prenom, :email, :tel, :type, :zone, 0, '{}')
                ON DUPLICATE KEY UPDATE 
                    Nom = VALUES(Nom), 
                    Prenom = VALUES(Prenom),
                    EmailPersonnel = VALUES(EmailPersonnel),
                    Telephone = VALUES(Telephone),
                    Type = VALUES(Type),
                    Zone = VALUES(Zone)
            ");

            $insertedRows = 0;

            foreach ($dossiers as $dossier) {
                $stmt->execute([
                    ':num'    => $dossier['NumEtu'],
                    ':nom'    => $dossier['Nom'],
                    ':prenom' => $dossier['Prenom'],
                    ':email'  => $dossier['EmailPersonnel'],
                    ':tel'    => $dossier['Telephone'],
                    ':type'   => $dossier['Type'],
                    ':zone'   => $dossier['Zone']
                ]);
                $insertedRows++;
            }
            
            $pdo->commit();
            return $insertedRows;
            
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Import Error (PDO): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO dossiers (
                    NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                    EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone,
                    IsComplete, PiecesJustificatives
                ) VALUES (
                    :NumEtu, :Nom, :Prenom, :DateNaissance, :Sexe, :Adresse, :CodePostal, :Ville,
                    :EmailPersonnel, :EmailAMU, :Telephone, :CodeDepartement, :Type, :Zone,
                    0, :PiecesJustificatives
                )
            ");

            return $stmt->execute($data);
        } catch (\PDOException $e) {
            error_log("PDO Error during folder creation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param string $numEtu
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(string $numEtu, array $data): bool
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare("
                UPDATE dossiers
                SET 
                    Nom = COALESCE(:Nom, Nom), Prenom = COALESCE(:Prenom, Prenom), 
                    DateNaissance = COALESCE(:DateNaissance, DateNaissance), Sexe = COALESCE(:Sexe, Sexe), 
                    Adresse = COALESCE(:Adresse, Adresse), CodePostal = COALESCE(:CodePostal, CodePostal), 
                    Ville = COALESCE(:Ville, Ville), EmailPersonnel = COALESCE(:EmailPersonnel, EmailPersonnel), 
                    EmailAMU = COALESCE(:EmailAMU, EmailAMU), Telephone = COALESCE(:Telephone, Telephone),
                    CodeDepartement = COALESCE(:CodeDepartement, CodeDepartement), Type = COALESCE(:Type, Type), 
                    Zone = COALESCE(:Zone, Zone), PiecesJustificatives = :PiecesJustificatives
                WHERE NumEtu = :NumEtuUpdate
            ");

            $data[':NumEtuUpdate'] = $numEtu;

            return $stmt->execute($data);
        } catch (\PDOException $e) {
            error_log("PDO Error during folder update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->query("
                SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives
                FROM dossiers
                ORDER BY Nom, Prenom
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\PDOException $e) {
            error_log("Error fetching all folders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param string $numEtu
     * @return array<string, mixed>|null
     */
    public function findByNumEtu(string $numEtu): ?array
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                       EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone,
                       IsComplete, PiecesJustificatives
                FROM dossiers 
                WHERE NumEtu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numEtu]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return is_array($result) ? $result : null;
        } catch (\PDOException $e) {
            error_log("Error fetching student details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $numEtu
     * @return bool
     */
    public function toggleStatus(string $numEtu): bool
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare("SELECT IsComplete FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numEtu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($current)) return false;

            $newStatus = (($current['IsComplete'] ?? 0) == 1) ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE dossiers SET IsComplete = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $newStatus, ':numetu' => $numEtu]);
        } catch (\PDOException $e) {
            error_log("Error toggling folder status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @param int $page
     * @param int $perPage
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int}
     */
    public function searchWithPagination(array $filters, int $page, int $perPage): array
    {
        $pdo = Database::getInstance()->getConnection();
        $params = [];
        $whereConditions = " WHERE 1=1";

        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            $whereConditions .= ($filters['complet'] == '1') ? " AND IsComplete = 1" : " AND (IsComplete = 0 OR IsComplete IS NULL)";
        }
        if (!empty($filters['type']) && is_string($filters['type']) && $filters['type'] !== 'all') {
            $whereConditions .= " AND LOWER(Type) = LOWER(:type)";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['zone']) && is_string($filters['zone']) && $filters['zone'] !== 'all') {
            $whereConditions .= " AND LOWER(Zone) = LOWER(:zone)";
            $params[':zone'] = $filters['zone'];
        }
        if (!empty($filters['search']) && is_string($filters['search'])) {
            $whereConditions .= " AND (LOWER(Nom) LIKE LOWER(:search) 
                                OR LOWER(Prenom) LIKE LOWER(:search) 
                                OR LOWER(NumEtu) LIKE LOWER(:search) 
                                OR LOWER(EmailPersonnel) LIKE LOWER(:search))";
            
            $params[':search'] = '%' . trim($filters['search']) . '%';        
        }

        $totalCount = 0;
        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM dossiers" . $whereConditions);
            $countStmt->execute($params);
            $row = $countStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) $totalCount = (int)($row['total'] ?? 0);
        } catch (\PDOException $e) { error_log("COUNT ERROR in searchWithPagination: " . $e->getMessage()); }

        $sql = "SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives
                FROM dossiers " . $whereConditions . " ORDER BY Nom ASC, Prenom ASC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $totalCount,
                'totalPages' => ($totalCount > 0) ? (int)ceil($totalCount / $perPage) : 0
            ];
        } catch (\PDOException $e) {
            return ['data' => [], 'total' => 0, 'totalPages' => 0];
        }
    }
}
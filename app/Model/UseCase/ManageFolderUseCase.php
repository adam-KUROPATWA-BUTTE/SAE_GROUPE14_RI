<?php

namespace Model\UseCase;

use Database;
use DateTime;
use PDO;
use PDOException;
use RuntimeException;

class ManageFolderUseCase
{
    private PDO $pdo;

    public function __construct()
    {
        if (!class_exists('\Database')) {
            throw new RuntimeException('Database class not found.');
        }
        $this->pdo = \Database::getInstance()->getConnection();
    }

    /**
     * Récupère tous les dossiers pour le Dashboard Admin.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllFolders(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                    DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                    IsComplete, PiecesJustificatives
                FROM dossiers
                ORDER BY Nom, Prenom
            ");

            if ($stmt === false) {
                return [];
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all folders: " . $e->getMessage());
            return [];
        }
    }
    
    public function getStudentDetails(string $numetu): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                       EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone,
                       IsComplete, PiecesJustificatives
                FROM dossiers 
                WHERE NumEtu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($result)) {
                return null;
            }

            $piecesJson = $result['PiecesJustificatives'] ?? '';
            $result['pieces'] = (is_string($piecesJson) && $piecesJson !== '')
                ? (json_decode($piecesJson, true) ?? [])
                : [];
                
            return $result;
        } catch (PDOException $e) {
            error_log("Error fetching student details: " . $e->getMessage());
            return null;
        }
    }

    public function getByNumetu(string $numetu): ?array
    {
        return $this->getStudentDetails($numetu);
    }

    public function creerDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        // Safe extraction from mixed array $data
        $tmpPhoto      = $data['photo'] ?? null;
        $photoData     = $photoData ?? (is_string($tmpPhoto) ? $tmpPhoto : null);
        $tmpCv         = $data['cv'] ?? null;
        $cvData        = $cvData ?? (is_string($tmpCv) ? $tmpCv : null);
        $tmpConvention = $data['convention'] ?? null;
        $conventionData = $conventionData ?? (is_string($tmpConvention) ? $tmpConvention : null);
        $tmpLettre     = $data['lettre_motivation'] ?? null;
        $lettreData    = $lettreData ?? (is_string($tmpLettre) ? $tmpLettre : null);

        try {
            foreach ($data as $key => $value) {
                if ($value === '') $data[$key] = null;
            }

            if (!empty($data['naissance']) && is_string($data['naissance'])) {
                $date = DateTime::createFromFormat('Y-m-d', $data['naissance']);
                $data['DateNaissance'] = ($date && $date->format('Y-m-d') === $data['naissance']) ? $data['naissance'] : null;
            }

            $pieces = [];
            if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
            if ($conventionData !== null) $pieces['convention'] = base64_encode($conventionData);
            if ($lettreData !== null) $pieces['lettre_motivation'] = base64_encode($lettreData);

            $stmt = $this->pdo->prepare("
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

            return $stmt->execute([
                ':NumEtu' => $data['NumEtu'] ?? null,
                ':Nom' => $data['Nom'] ?? null,
                ':Prenom' => $data['Prenom'] ?? null,
                ':DateNaissance' => $data['DateNaissance'] ?? null,
                ':Sexe' => $data['Sexe'] ?? null,
                ':Adresse' => $data['Adresse'] ?? null,
                ':CodePostal' => $data['CodePostal'] ?? null,
                ':Ville' => $data['Ville'] ?? null,
                ':EmailPersonnel' => $data['EmailPersonnel'] ?? null,
                ':EmailAMU' => $data['EmailAMU'] ?? null,
                ':Telephone' => $data['Telephone'] ?? null,
                ':CodeDepartement' => $data['CodeDepartement'] ?? null,
                ':Type' => $data['Type'] ?? null,
                ':Zone' => $data['Zone'] ?? null,
                ':PiecesJustificatives' => json_encode($pieces)
            ]);
        } catch (PDOException $e) {
            error_log("Error creating folder: " . $e->getMessage());
            return false;
        }
    }

    public function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $tmpPhoto      = $data['photo'] ?? null;
        $photoData     = $photoData ?? (is_string($tmpPhoto) ? $tmpPhoto : null);
        $tmpCv         = $data['cv'] ?? null;
        $cvData        = $cvData ?? (is_string($tmpCv) ? $tmpCv : null);
        $tmpConvention = $data['convention'] ?? null;
        $conventionData = $conventionData ?? (is_string($tmpConvention) ? $tmpConvention : null);
        $tmpLettre     = $data['lettre_motivation'] ?? null;
        $lettreData    = $lettreData ?? (is_string($tmpLettre) ? $tmpLettre : null);

        try {
            $numEtu = strval($data['NumEtu'] ?? '');
            $existing = $this->getStudentDetails($numEtu);
            $oldPieces = $existing['pieces'] ?? [];

            if (!empty($photoData) && is_string($photoData)) $oldPieces['photo'] = base64_encode($photoData);
            if (!empty($cvData) && is_string($cvData)) $oldPieces['cv'] = base64_encode($cvData);
            if (!empty($conventionData) && is_string($conventionData)) $oldPieces['convention'] = base64_encode($conventionData);
            if (!empty($lettreData) && is_string($lettreData)) $oldPieces['lettre_motivation'] = base64_encode($lettreData);

            $stmt = $this->pdo->prepare("
                UPDATE dossiers
                SET 
                    Nom = COALESCE(:Nom, Nom), Prenom = COALESCE(:Prenom, Prenom), DateNaissance = COALESCE(:DateNaissance, DateNaissance), 
                    Sexe = COALESCE(:Sexe, Sexe), Adresse = COALESCE(:Adresse, Adresse), CodePostal = COALESCE(:CodePostal, CodePostal), 
                    Ville = COALESCE(:Ville, Ville), EmailPersonnel = COALESCE(:EmailPersonnel, EmailPersonnel), 
                    EmailAMU = COALESCE(:EmailAMU, EmailAMU), Telephone = COALESCE(:Telephone, Telephone),
                    CodeDepartement = COALESCE(:CodeDepartement, CodeDepartement), Type = COALESCE(:Type, Type), 
                    Zone = COALESCE(:Zone, Zone), PiecesJustificatives = :PiecesJustificatives
                WHERE NumEtu = :NumEtu
            ");

            return $stmt->execute([
                ':NumEtu' => $numEtu,
                ':Nom' => $data['Nom'] ?? null,
                ':Prenom' => $data['Prenom'] ?? null,
                ':DateNaissance' => $data['DateNaissance'] ?? null,
                ':Sexe' => $data['Sexe'] ?? null,
                ':Adresse' => $data['Adresse'] ?? null,
                ':CodePostal' => $data['CodePostal'] ?? null,
                ':Ville' => $data['Ville'] ?? null,
                ':EmailPersonnel' => $data['EmailPersonnel'] ?? null,
                ':EmailAMU' => $data['EmailAMU'] ?? null,
                ':Telephone' => $data['Telephone'] ?? null,
                ':CodeDepartement' => $data['CodeDepartement'] ?? null,
                ':Type' => $data['Type'] ?? null,
                ':Zone' => $data['Zone'] ?? null,
                ':PiecesJustificatives' => json_encode($oldPieces)
            ]);
        } catch (PDOException $e) {
            error_log("Error updating folder: " . $e->getMessage());
            return false;
        }
    }

    public function toggleCompleteStatus(string $numetu): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT IsComplete FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numetu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($current)) return false;

            $newStatus = (($current['IsComplete'] ?? 0) == 1) ? 0 : 1;
            $stmt = $this->pdo->prepare("UPDATE dossiers SET IsComplete = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $newStatus, ':numetu' => $numetu]);
        } catch (PDOException $e) {
            error_log("Error toggling folder status: " . $e->getMessage());
            return false;
        }
    }

    public function rechercherAvecPagination(array $filters, int $page = 1, int $perPage = 10): array
    {
        $params = [];
        $whereConditions = " WHERE 1=1";

        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            $whereConditions .= ($filters['complet'] == '1') ? " AND IsComplete = 1" : " AND (IsComplete = 0 OR IsComplete IS NULL)";
        }
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $whereConditions .= " AND LOWER(Type) = LOWER(:type)";
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['zone']) && $filters['zone'] !== 'all') {
            $whereConditions .= " AND LOWER(Zone) = LOWER(:zone)";
            $params['zone'] = $filters['zone'];
        }
        if (!empty($filters['search'])) {
            $whereConditions .= " AND (Nom LIKE :search OR Prenom LIKE :search OR NumEtu LIKE :search OR EmailPersonnel LIKE :search)";
            $params['search'] = $filters['search'] . '%';
        }

        $totalCount = 0;
        try {
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM dossiers" . $whereConditions);
            $countStmt->execute($params);
            $row = $countStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) $totalCount = (int)($row['total'] ?? 0);
        } catch (PDOException $e) {}

        $sql = "SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives
                FROM dossiers " . $whereConditions . " ORDER BY Nom ASC, Prenom ASC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $totalCount,
                'totalPages' => ($totalCount > 0) ? (int)ceil($totalCount / $perPage) : 0
            ];
        } catch (PDOException $e) {
            return ['data' => [], 'total' => 0, 'totalPages' => 0];
        }
    }
}
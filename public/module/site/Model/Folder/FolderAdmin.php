<?php

// phpcs:disable Generic.Files.LineLength

namespace Model\Folder;

use PDO;
use PDOException;
use DateTime;
use Database;

/**
 * Class FolderAdmin
 *
 * Model responsible for managing student folders in the database.
 * Handles CRUD operations, file storage (encoded in JSON), and advanced search filtering.
 */
class FolderAdmin
{
    /**
     * Get a PDO connection via the Database class.
     *
     * @return PDO The PDO connection instance.
     */
    private static function getConnection(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    /**
     * Retrieve all folders from the database.
     *
     * @return array<int, array<string, mixed>> List of all student folders.
     */
    public static function getAll(): array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
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
            error_log("Error fetching folders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve incomplete folders (where IsComplete is 0 or NULL).
     *
     * @return array<int, array<string, mixed>> List of incomplete folders.
     */
    public static function getDossiersIncomplets(): array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT 
                    NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                    DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                    IsComplete, PiecesJustificatives
                FROM dossiers
                WHERE IsComplete = 0 OR IsComplete IS NULL
                ORDER BY Nom, Prenom
            ");

            if ($stmt === false) {
                return [];
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching incomplete folders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new folder in the database.
     *
     * @param array<string, mixed> $data           Associative array containing student information.
     * @param string|null          $photoData      Binary data (can be passed in $data['photo'] too).
     * @param string|null          $cvData         Binary data (can be passed in $data['cv'] too).
     * @param string|null          $conventionData Binary data.
     * @param string|null          $lettreData     Binary data.
     * @return bool True on success, False on failure.
     */
    public static function creerDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();

        // Safe extraction from mixed array $data
        // We ensure variable is null or string to satisfy PHPStan later
        $tmpPhoto      = $data['photo'] ?? null;
        $photoData     = $photoData ?? (is_string($tmpPhoto) ? $tmpPhoto : null);

        $tmpCv         = $data['cv'] ?? null;
        $cvData        = $cvData ?? (is_string($tmpCv) ? $tmpCv : null);

        $tmpConvention = $data['convention'] ?? null;
        $conventionData = $conventionData ?? (is_string($tmpConvention) ? $tmpConvention : null);

        $tmpLettre     = $data['lettre_motivation'] ?? null;
        $lettreData    = $lettreData ?? (is_string($tmpLettre) ? $tmpLettre : null);

        try {
            // Clean empty strings
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // Ensure DateNaissance is NULL or valid
            if (!empty($data['naissance']) && is_string($data['naissance'])) {
                $date = DateTime::createFromFormat('Y-m-d', $data['naissance']);
                if (!$date || $date->format('Y-m-d') !== $data['naissance']) {
                    $data['naissance'] = null;
                }
            }

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

            // Encode files to JSON - PHPStan safe check
            $pieces = [];
            if ($photoData !== null) {
                $pieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $pieces['cv'] = base64_encode($cvData);
            }
            if ($conventionData !== null) {
                $pieces['convention'] = base64_encode($conventionData);
            }
            if ($lettreData !== null) {
                $pieces['lettre_motivation'] = base64_encode($lettreData);
            }

            $piecesJson = json_encode($pieces);

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
                ':PiecesJustificatives' => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Error creating folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve a folder by personal email.
     *
     * @param string $email The student's personal email.
     * @return array<string, mixed>|null The student data or null if not found.
     */
    public static function getByEmail(string $email): ?array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives
                FROM dossiers 
                WHERE EmailPersonnel = :email 
                LIMIT 1
            ");
            $stmt->execute([':email' => $email]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        } catch (PDOException $e) {
            error_log("Error fetching folder by email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve a folder by student number (NumEtu).
     *
     * @param string $numetu The student ID number.
     * @return array<string, mixed>|null The student data or null if not found.
     */
    public static function getByNumetu(string $numetu): ?array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives
                FROM dossiers 
                WHERE NumEtu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        } catch (PDOException $e) {
            error_log("Error fetching folder by NumEtu: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get full student details including decoded justificative files.
     *
     * @param string $numetu
     * @return array<string, mixed>|null
     */
    public static function getStudentDetails(string $numetu): ?array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                       EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone,
                       IsComplete, PiecesJustificatives
                FROM dossiers 
                WHERE NumEtu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($result)) {
                $piecesJson = $result['PiecesJustificatives'] ?? '';
                $result['pieces'] = (is_string($piecesJson) && $piecesJson !== '')
                    ? (json_decode($piecesJson, true) ?? [])
                    : [];
                return $result;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error fetching student details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing folder.
     *
     * @param array<string, mixed> $data
     * @param string|null $photoData
     * @param string|null $cvData
     * @param string|null $conventionData
     * @param string|null $lettreData
     * @return bool
     */
    public static function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();

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
            $numEtu = strval($data['NumEtu'] ?? '');

            // Retrieve old files
            $existing = self::getByNumetu($numEtu);
            $oldPieces = [];

            if (is_array($existing)) {
                $piecesJson = $existing['PiecesJustificatives'] ?? '';
                if (is_string($piecesJson) && $piecesJson !== '') {
                    $decoded = json_decode($piecesJson, true);
                    if (is_array($decoded)) {
                        $oldPieces = $decoded;
                    }
                }
            }

            // Update only provided files - Check !empty and string type safety
            if (!empty($photoData) && is_string($photoData)) {
                $oldPieces['photo'] = base64_encode($photoData);
            }
            if (!empty($cvData) && is_string($cvData)) {
                $oldPieces['cv'] = base64_encode($cvData);
            }
            if (!empty($conventionData) && is_string($conventionData)) {
                $oldPieces['convention'] = base64_encode($conventionData);
            }
            if (!empty($lettreData) && is_string($lettreData)) {
                $oldPieces['lettre_motivation'] = base64_encode($lettreData);
            }

            $piecesJson = json_encode($oldPieces);

            $stmt = $pdo->prepare("
                UPDATE dossiers
                SET 
                    Nom = :Nom, Prenom = :Prenom, DateNaissance = :DateNaissance, Sexe = :Sexe,
                    Adresse = :Adresse, CodePostal = :CodePostal, Ville = :Ville,
                    EmailPersonnel = :EmailPersonnel, EmailAMU = :EmailAMU, Telephone = :Telephone,
                    CodeDepartement = :CodeDepartement, Type = :Type, Zone = :Zone,
                    PiecesJustificatives = :PiecesJustificatives
                WHERE NumEtu = :NumEtu
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
                ':PiecesJustificatives' => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Error updating folder: " . $e->getMessage());
            return false;
        }
    }

    public static function supprimerDossier(string $numetu): bool
    {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM dossiers WHERE NumEtu = :numetu");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (PDOException $e) {
            error_log("Error deleting folder: " . $e->getMessage());
            return false;
        }
    }

    public static function ajouterRelance(int $dossierId, string $message, int $adminId): bool
    {
        return true;
    }

    public static function valider(string $numetu, ?int $adminId = null): bool
    {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->prepare("UPDATE dossiers SET IsComplete = 1 WHERE NumEtu = :numetu");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (PDOException $e) {
            error_log("Error validating folder: " . $e->getMessage());
            return false;
        }
    }

    public static function toggleCompleteStatus(string $numetu): bool
    {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT IsComplete FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numetu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($current)) {
                return false;
            }

            $val = $current['IsComplete'] ?? 0;
            $newStatus = ($val == 1) ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE dossiers SET IsComplete = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $newStatus, ':numetu' => $numetu]);
        } catch (PDOException $e) {
            error_log("Error toggling folder status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload a photo.
     *
     * @param string $numetu
     * @param array<string, mixed> $file PHP $_FILES array
     * @return bool
     */
    public static function uploadPhoto(string $numetu, array $file): bool
    {
        if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return false;
        }
        $photoData = file_get_contents($file['tmp_name']);
        if ($photoData === false) {
            return false;
        }
        return self::updatePieceJustificative($numetu, 'photo', $photoData);
    }

    /**
     * Upload a CV.
     *
     * @param string $numetu
     * @param array<string, mixed> $file PHP $_FILES array
     * @return bool
     */
    public static function uploadCV(string $numetu, array $file): bool
    {
        if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return false;
        }
        $cvData = file_get_contents($file['tmp_name']);
        if ($cvData === false) {
            return false;
        }
        return self::updatePieceJustificative($numetu, 'cv', $cvData);
    }

    private static function updatePieceJustificative(string $numetu, string $type, string $data): bool
    {
        $pdo = self::getConnection();
        try {
            $existing = self::getByNumetu($numetu);
            $pieces = [];
            if (is_array($existing)) {
                $json = $existing['PiecesJustificatives'] ?? '';
                if (is_string($json) && $json !== '') {
                    $decoded = json_decode($json, true);
                    if (is_array($decoded)) {
                        $pieces = $decoded;
                    }
                }
            }

            $pieces[$type] = base64_encode($data);
            $piecesJson = json_encode($pieces);

            $stmt = $pdo->prepare("UPDATE dossiers SET PiecesJustificatives = :pieces WHERE NumEtu = :numetu");
            return $stmt->execute([':pieces' => $piecesJson, ':numetu' => $numetu]);
        } catch (PDOException $e) {
            error_log("Error updating justificative file ($type): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search folders using advanced filters.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public static function rechercher(array $filters): array
    {
        $pdo = self::getConnection();
        $params = [];
        $sql = "SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives
                FROM dossiers WHERE 1=1";

        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            if ($filters['complet'] == '1') {
                $sql .= " AND IsComplete = 1";
            } else {
                $sql .= " AND (IsComplete = 0 OR IsComplete IS NULL)";
            }
        }
        if (!empty($filters['date_debut'])) {
            $sql .= " AND DateNaissance >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }
        if (!empty($filters['date_fin'])) {
            $sql .= " AND DateNaissance <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $sql .= " AND Type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['zone']) && $filters['zone'] !== 'all') {
            $sql .= " AND Zone = :zone";
            $params[':zone'] = $filters['zone'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (Nom LIKE :search OR Prenom LIKE :search OR NumEtu LIKE :search OR EmailPersonnel LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $triDate = strval($filters['tri_date'] ?? '');
        $orderDirection = (strtoupper($triDate) === 'ASC') ? 'ASC' : 'DESC';
        $sql .= " ORDER BY DateNaissance $orderDirection, Nom ASC";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching folders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search folders with SQL-based pagination.
     *
     * @param array<string, mixed> $filters
     * @param int $page
     * @param int $perPage
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int}
     */
    public static function rechercherAvecPagination(array $filters, int $page = 1, int $perPage = 10): array
    {
        $pdo = self::getConnection();
        $params = [];
        $whereConditions = " WHERE 1=1";

        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            if ($filters['complet'] == '1') {
                $whereConditions .= " AND IsComplete = 1";
            } else {
                $whereConditions .= " AND (IsComplete = 0 OR IsComplete IS NULL)";
            }
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
            $searchValue = $filters['search'] . '%';
            $whereConditions .= " AND (Nom LIKE :search1 OR Prenom LIKE :search2 OR NumEtu LIKE :search3 OR EmailPersonnel LIKE :search4)";
            $params['search1'] = $searchValue;
            $params['search2'] = $searchValue;
            $params['search3'] = $searchValue;
            $params['search4'] = $searchValue;
        }

        // Total Count
        $totalCount = 0;
        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM dossiers" . $whereConditions);
            foreach ($params as $key => $value) {
                $countStmt->bindValue(':' . $key, $value);
            }
            $countStmt->execute();
            $row = $countStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) {
                $totalCount = (int)($row['total'] ?? 0);
            }
        } catch (PDOException $e) {
            error_log(" Error counting folders: " . $e->getMessage());
        }

        // Pagination Query
        $sql = "SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives
                FROM dossiers " . $whereConditions;
        $sql .= " ORDER BY Nom ASC, Prenom ASC";
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT :limit OFFSET :offset";

        try {
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $data,
                'total' => $totalCount,
                'totalPages' => ($totalCount > 0) ? (int)ceil($totalCount / $perPage) : 0
            ];
        } catch (PDOException $e) {
            error_log("Error searching folders with pagination: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'totalPages' => 0];
        }
    }
}

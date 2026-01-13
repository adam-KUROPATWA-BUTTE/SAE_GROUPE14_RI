<?php

namespace Model\Folder;

use PDO;
use PDOException;

/**
 * Class FolderAdmin
 *
 * Model responsible for managing student folders in the database (Administrator context).
 * Handles CRUD operations, file storage (encoded in JSON/Base64), and advanced search filtering.
 */
class FolderAdmin
{
    /**
     * Get a PDO connection via the Database singleton.
     *
     * @return PDO The PDO connection instance.
     */
    private static function getConnection(): PDO
    {
        return \Database::getInstance()->getConnection();
    }

    /**
     * Retrieve all folders from the database sorted by name.
     *
     * @return array<int, array<string, mixed>> List of all student folders.
     */
    public static function getAll(): array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT *
                FROM dossiers
                ORDER BY Nom, Prenom
            ");

            if ($stmt === false) {
                return [];
            }

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return is_array($results) ? $results : [];
        } catch (PDOException $e) {
            error_log("Error fetching all folders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve a student's folder details by their student ID (NumEtu).
     * Automatically decodes the JSON 'PiecesJustificatives' column.
     *
     * @param string $numetu The student identifier.
     * @return array<string, mixed>|null The folder data or null if not found.
     */
    public static function getStudentDetails(string $numetu): ?array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("SELECT * FROM dossiers WHERE NumEtu = :numetu LIMIT 1");
            $stmt->execute([':numetu' => $numetu]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && is_array($result)) {
                // Safely handle JSON decoding for documents
                $rawJson = $result['PiecesJustificatives'] ?? '{}';
                $jsonString = is_string($rawJson) ? $rawJson : '{}';

                $decoded = json_decode($jsonString, true);
                $result['pieces'] = is_array($decoded) ? $decoded : [];
                return $result;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error fetching student details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieves a folder by Email (used for duplicate checks).
     *
     * @param string $email The email to search for.
     * @return array<string, mixed>|null The folder data or null.
     */
    public static function getByEmail(string $email): ?array
    {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM dossiers WHERE EmailPersonnel = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (is_array($result)) ? $result : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Retrieves a folder by NumEtu (alias for getStudentDetails).
     *
     * @param string $numetu
     * @return array<string, mixed>|null
     */
    public static function getByNumetu(string $numetu): ?array
    {
        return self::getStudentDetails($numetu);
    }

    /**
     * Create a new student folder.
     *
     * @param array<string, mixed> $data           Student data (Nom, Prenom, etc.).
     * @param string|null          $photoData      Binary content of photo.
     * @param string|null          $cvData         Binary content of CV.
     * @param string|null          $conventionData Binary content of convention.
     * @param string|null          $lettreData     Binary content of motivation letter.
     * @return bool True on success, false on failure.
     */
    public static function creerDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();

        try {
            // Encode files to Base64
            $pieces = [];
            if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
            if ($conventionData !== null) $pieces['convention'] = base64_encode($conventionData);
            if ($lettreData !== null) $pieces['lettre_motivation'] = base64_encode($lettreData);

            $piecesJson = json_encode($pieces) ?: '{}';

            $sql = "INSERT INTO dossiers (
                        NumEtu, Nom, Prenom, EmailPersonnel, Type, Zone,
                        Adresse, CodePostal, Ville, Telephone, EmailAMU, CodeDepartement,
                        DateNaissance, Sexe, PiecesJustificatives, IsComplete
                    ) VALUES (
                        :numetu, :nom, :prenom, :email, :type, :zone,
                        :adresse, :cp, :ville, :tel, :email_amu, :dept,
                        :date_nais, :sexe, :pieces, 0
                    )";

            $stmt = $pdo->prepare($sql);

            return $stmt->execute([
                ':numetu'    => $data['NumEtu'],
                ':nom'       => $data['Nom'],
                ':prenom'    => $data['Prenom'],
                ':email'     => $data['EmailPersonnel'],
                ':type'      => $data['Type'],
                ':zone'      => $data['Zone'],
                ':adresse'   => $data['Adresse'],
                ':cp'        => $data['CodePostal'],
                ':ville'     => $data['Ville'],
                ':tel'       => $data['Telephone'],
                ':email_amu' => $data['EmailAMU'],
                ':dept'      => $data['CodeDepartement'],
                ':date_nais' => $data['DateNaissance'],
                ':sexe'      => $data['Sexe'],
                ':pieces'    => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Error creating folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing student folder.
     *
     * @param array<string, mixed> $data           Updated data.
     * @param string|null          $photoData      New photo content (optional).
     * @param string|null          $cvData         New CV content (optional).
     * @param string|null          $conventionData New convention content (optional).
     * @param string|null          $lettreData     New letter content (optional).
     * @return bool True on success.
     */
    public static function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();

        try {
            // 1. Retrieve existing JSON to preserve unchecked files
            $currentData = self::getStudentDetails((string)$data['NumEtu']);
            $pieces = $currentData['pieces'] ?? [];

            // 2. Update specific files if provided
            if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
            if ($conventionData !== null) $pieces['convention'] = base64_encode($conventionData);
            if ($lettreData !== null) $pieces['lettre_motivation'] = base64_encode($lettreData);

            $piecesJson = json_encode($pieces) ?: '{}';

            // 3. Update Record
            $sql = "UPDATE dossiers SET
                        Nom = :nom, Prenom = :prenom, EmailPersonnel = :email,
                        Telephone = :tel, Adresse = :adresse, CodePostal = :cp,
                        Ville = :ville, CodeDepartement = :dept, Type = :type, Zone = :zone,
                        PiecesJustificatives = :pieces
                    WHERE NumEtu = :numetu";

            $stmt = $pdo->prepare($sql);

            return $stmt->execute([
                ':nom'    => $data['Nom'],
                ':prenom' => $data['Prenom'],
                ':email'  => $data['EmailPersonnel'],
                ':tel'    => $data['Telephone'],
                ':adresse'=> $data['Adresse'],
                ':cp'     => $data['CodePostal'],
                ':ville'  => $data['Ville'],
                ':dept'   => $data['CodeDepartement'],
                ':type'   => $data['Type'],
                ':zone'   => $data['Zone'],
                ':pieces' => $piecesJson,
                ':numetu' => $data['NumEtu']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggles the completion status of a folder.
     *
     * @param string $numetu
     * @return bool
     */
    public static function toggleComplete(string $numetu): bool
    {
        $pdo = self::getConnection();
        $sql = "UPDATE dossiers SET IsComplete = NOT IsComplete WHERE NumEtu = :numetu";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':numetu' => $numetu]);
    }

    /**
     * Upload specific photo (Helper method).
     */
    public static function uploadPhoto(string $numetu, array $file): bool
    {
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return false;
        }
        $content = file_get_contents($file['tmp_name']);
        return ($content !== false) ? self::updatePieceJustificative($numetu, 'photo', $content) : false;
    }

    /**
     * Upload specific CV (Helper method).
     */
    public static function uploadCV(string $numetu, array $file): bool
    {
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return false;
        }
        $content = file_get_contents($file['tmp_name']);
        return ($content !== false) ? self::updatePieceJustificative($numetu, 'cv', $content) : false;
    }

    /**
     * Updates a single document in the JSON blob.
     *
     * @param string $numetu
     * @param string $key     Document key (photo, cv, convention, etc.)
     * @param string $content Binary content
     * @return bool
     */
    private static function updatePieceJustificative(string $numetu, string $key, string $content): bool
    {
        $current = self::getStudentDetails($numetu);
        if (!$current) return false;

        $pieces = $current['pieces'] ?? [];
        $pieces[$key] = base64_encode($content);

        $pdo = self::getConnection();
        $stmt = $pdo->prepare("UPDATE dossiers SET PiecesJustificatives = :json WHERE NumEtu = :numetu");
        return $stmt->execute([
            ':json' => json_encode($pieces),
            ':numetu' => $numetu
        ]);
    }

    /**
     * Search with pagination and filters.
     *
     * @param array<string, mixed> $filters
     * @param int                  $page
     * @param int                  $perPage
     * @return array{data: array, total: int, totalPages: int}
     */
    public static function rechercherAvecPagination(array $filters, int $page = 1, int $perPage = 10): array
    {
        $pdo = self::getConnection();
        $params = [];
        $where = ["1=1"];

        // Build conditions
        if (!empty($filters['search'])) {
            $where[] = "(Nom LIKE :search OR Prenom LIKE :search OR NumEtu LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $where[] = "Type = :type";
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['zone']) && $filters['zone'] !== 'all') {
            $where[] = "Zone = :zone";
            $params['zone'] = $filters['zone'];
        }
        if (!empty($filters['complet']) && $filters['complet'] !== 'all') {
            $where[] = "IsComplete = :complet";
            $params['complet'] = ($filters['complet'] === '1') ? 1 : 0;
        }

        $whereSql = implode(' AND ', $where);

        // Count total
        $countSql = "SELECT COUNT(*) FROM dossiers WHERE $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue(":$k", $v);
        }
        $stmtCount->execute();
        $totalCount = (int)$stmtCount->fetchColumn();

        // Fetch Data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM dossiers WHERE $whereSql ORDER BY Nom, Prenom LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }
        // Strict typing for Limit/Offset
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => is_array($data) ? $data : [],
            'total' => $totalCount,
            'totalPages' => ($totalCount > 0) ? (int)ceil($totalCount / $perPage) : 0
        ];
    }
}
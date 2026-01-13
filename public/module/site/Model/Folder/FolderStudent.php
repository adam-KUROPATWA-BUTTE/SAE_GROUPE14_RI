<?php

namespace Model\Folder;

use PDO;
use PDOException;

/**
 * Class FolderStudent
 *
 * Handles student-specific folder operations.
 * Allows retrieving personal data and updating specific fields (Address, Phone, etc.)
 * and uploading documents.
 */
class FolderStudent
{
    /**
     * Get a PDO connection using the Database singleton.
     *
     * @return PDO The active database connection.
     */
    private static function getConnection(): PDO
    {
        return \Database::getInstance()->getConnection();
    }

    /**
     * Retrieve a student's folder by their student number.
     * Includes decoding of the 'PiecesJustificatives' JSON column.
     *
     * @param string $numetu The student identifier.
     * @return array<string, mixed>|null Returns associative array of folder data or null if not found.
     */
    public static function getStudentDetails(string $numetu): ?array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                    EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone,
                    IsComplete, PiecesJustificatives
                FROM dossiers
                WHERE NumEtu = :numetu
                LIMIT 1
            ");
            
            $stmt->execute([':numetu' => $numetu]);
            
            // Fix: fetch can return false, strict check required
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result === false) {
                return null;
            }

            // Safe JSON decoding
            $rawJson = $result['PiecesJustificatives'] ?? '{}';
            // Ensure we pass a string to json_decode
            $jsonString = is_string($rawJson) ? $rawJson : '{}';
            $decoded = json_decode($jsonString, true);
            
            // Ensure the result is an array
            $result['pieces'] = is_array($decoded) ? $decoded : [];

            return is_array($result) ? $result : null;
        } catch (PDOException $e) {
            error_log("Error retrieving student folder: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve the folder for the currently logged-in student (dashboard use).
     *
     * @param int|string $etudiantId The student internal ID or Number (casted to string).
     * @return array<string, mixed>|null
     */
    public static function getMyFolder($etudiantId): ?array
    {
        // Convert ID to string for compatibility with getStudentDetails
        return self::getStudentDetails((string)$etudiantId);
    }

    /**
     * Create a new folder for the student.
     *
     * @param array<string, mixed> $data           Folder data.
     * @param string|null          $photoData      Binary photo data.
     * @param string|null          $cvData         Binary CV data.
     * @param string|null          $conventionData Binary Convention data.
     * @param string|null          $lettreData     Binary Motivation Letter data.
     * @return bool True on success.
     */
    public static function createDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();

        try {
            // Clean data: Convert empty strings to NULL
            $cleanedData = [];
            foreach ($data as $key => $value) {
                $cleanedData[$key] = ($value === '') ? null : $value;
            }

            // Handle Date format safety
            if (!empty($cleanedData['DateNaissance']) && is_string($cleanedData['DateNaissance'])) {
                $date = \DateTime::createFromFormat('Y-m-d', $cleanedData['DateNaissance']);
                if (!$date || $date->format('Y-m-d') !== $cleanedData['DateNaissance']) {
                    $cleanedData['DateNaissance'] = null;
                }
            }

            $sql = "INSERT INTO dossiers (
                        NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                        EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone,
                        IsComplete, PiecesJustificatives
                    ) VALUES (
                        :NumEtu, :Nom, :Prenom, :DateNaissance, :Sexe, :Adresse, :CodePostal, :Ville,
                        :EmailPersonnel, :EmailAMU, :Telephone, :CodeDepartement, :Type, :Zone,
                        0, :PiecesJustificatives
                    )";

            $stmt = $pdo->prepare($sql);

            // Prepare justificative files as JSON
            $pieces = [];
            if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
            if ($conventionData !== null) $pieces['convention'] = base64_encode($conventionData);
            if ($lettreData !== null) $pieces['lettre_motivation'] = base64_encode($lettreData);

            $piecesJson = json_encode($pieces) ?: '{}';

            return $stmt->execute([
                ':NumEtu' => $cleanedData['NumEtu'] ?? null,
                ':Nom' => $cleanedData['Nom'] ?? null,
                ':Prenom' => $cleanedData['Prenom'] ?? null,
                ':DateNaissance' => $cleanedData['DateNaissance'] ?? null,
                ':Sexe' => $cleanedData['Sexe'] ?? null,
                ':Adresse' => $cleanedData['Adresse'] ?? null,
                ':CodePostal' => $cleanedData['CodePostal'] ?? null,
                ':Ville' => $cleanedData['Ville'] ?? null,
                ':EmailPersonnel' => $cleanedData['EmailPersonnel'] ?? null,
                ':EmailAMU' => $cleanedData['EmailAMU'] ?? null,
                ':Telephone' => $cleanedData['Telephone'] ?? null,
                ':CodeDepartement' => $cleanedData['CodeDepartement'] ?? null,
                ':Type' => $cleanedData['Type'] ?? null,
                ':Zone' => $cleanedData['Zone'] ?? null,
                ':PiecesJustificatives' => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Error creating student folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a student's folder (Limited fields for students).
     *
     * @param array<string, mixed> $data           Associative array containing fields to update.
     * @param string|null          $photoData      Optional new photo data (binary string).
     * @param string|null          $cvData         Optional new CV data (binary string).
     * @param string|null          $conventionData Optional new Convention data.
     * @param string|null          $lettreData     Optional new Letter data.
     * @return bool True on success, false on failure.
     */
    public static function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();
        $numEtu = (string)($data['NumEtu'] ?? '');

        if ($numEtu === '') {
            return false;
        }

        try {
            // 1. Retrieve existing supporting documents to preserve unchanged files
            $existing = self::getStudentDetails($numEtu);
            $pieces = $existing['pieces'] ?? [];

            // 2. Update specific files if provided
            if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
            if ($conventionData !== null) $pieces['convention'] = base64_encode($conventionData);
            if ($lettreData !== null) $pieces['lettre_motivation'] = base64_encode($lettreData);

            $piecesJson = json_encode($pieces) ?: '{}';

            // 3. Update SQL record
            $stmt = $pdo->prepare("
                UPDATE dossiers
                SET 
                    Adresse = :Adresse,
                    CodePostal = :CodePostal,
                    Ville = :Ville,
                    Telephone = :Telephone,
                    EmailPersonnel = :EmailPersonnel,
                    PiecesJustificatives = :PiecesJustificatives
                WHERE NumEtu = :NumEtu
            ");

            return $stmt->execute([
                ':NumEtu' => $numEtu,
                ':Adresse' => $data['Adresse'] ?? null,
                ':CodePostal' => $data['CodePostal'] ?? null,
                ':Ville' => $data['Ville'] ?? null,
                ':Telephone' => $data['Telephone'] ?? null,
                ':EmailPersonnel' => $data['EmailPersonnel'] ?? null,
                ':PiecesJustificatives' => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Error updating student folder: " . $e->getMessage());
            return false;
        }
    }
}
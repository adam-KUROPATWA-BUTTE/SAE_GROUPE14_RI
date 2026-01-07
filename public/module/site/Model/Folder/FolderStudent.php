<?php

namespace Model\Folder;

use PDO;
use PDOException;

/**
 * FolderStudent
 *
 * Handles student-specific folder operations, such as retrieving
 * and updating their own data and uploaded documents.
 */
class FolderStudent
{
    /**
     * Get a PDO connection using the Database singleton
     *
     * @return PDO
     * @throws \RuntimeException if Database class is not loaded
     */
    private static function getConnection(): PDO
    {
        if (!class_exists('\Database')) {
            throw new \RuntimeException("Database class not found. Ensure Config/Database.php is loaded.");
        }

        return \Database::getInstance()->getConnection();
    }

    /**
     * Retrieve a student's folder by their student number
     *
     * @param string $numetu
     * @return array|null Returns associative array of folder data or null if not found
     */
    public static function getStudentDetails(string $numetu): ?array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu,
                    Nom,
                    Prenom,
                    DateNaissance,
                    Sexe,
                    Adresse,
                    CodePostal,
                    Ville,
                    EmailPersonnel,
                    EmailAMU,
                    Telephone,
                    CodeDepartement,
                    Type,
                    Zone,
                    IsComplete,
                    PiecesJustificatives
                FROM dossiers
                WHERE NumEtu = :numetu
                LIMIT 1
            ");
            $stmt->execute(['numetu' => $numetu]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Decode supporting documents JSON
                $result['pieces'] = !empty($result['PiecesJustificatives'])
                    ? json_decode($result['PiecesJustificatives'], true)
                    : [];
            }

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error retrieving student folder: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve the folder for the currently logged-in student (dashboard use)
     *
     * @param int $etudiantId
     * @return array|null
     */
    public static function getMyFolder(int $etudiantId): ?array
    {
        // Convert student ID to string for compatibility
        return self::getStudentDetails((string)$etudiantId);
    }

    /**
     * Create a new folder for the student.
     *
     * @param array $data Folder data
     * @param string|null $photoData Binary photo data
     * @param string|null $cvData Binary CV data
     * @param string|null $conventionData Binary Convention data
     * @param string|null $lettreData Binary Motivation Letter data
     * @return bool
     */
    public static function createDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();

        try {
            // Convert empty strings to NULL
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // Handle Date
            if (!empty($data['DateNaissance'])) {
                $date = \DateTime::createFromFormat('Y-m-d', $data['DateNaissance']);
                if (!$date || $date->format('Y-m-d') !== $data['DateNaissance']) {
                    $data['DateNaissance'] = null;
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

            // Prepare justificative files as JSON
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
                ':NumEtu' => $data['NumEtu'],
                ':Nom' => $data['Nom'],
                ':Prenom' => $data['Prenom'],
                ':DateNaissance' => $data['DateNaissance'],
                ':Sexe' => $data['Sexe'],
                ':Adresse' => $data['Adresse'],
                ':CodePostal' => $data['CodePostal'],
                ':Ville' => $data['Ville'],
                ':EmailPersonnel' => $data['EmailPersonnel'],
                ':EmailAMU' => $data['EmailAMU'],
                ':Telephone' => $data['Telephone'],
                ':CodeDepartement' => $data['CodeDepartement'],
                ':Type' => $data['Type'],
                ':Zone' => $data['Zone'],
                ':PiecesJustificatives' => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Error creating student folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a student's folder
     *
     * @param array $data Associative array containing fields to update
     * @param string|null $photoData Optional new photo data (binary string)
     * @param string|null $cvData Optional new CV data (binary string)
     * @param string|null $conventionData Optional new Convention data
     * @param string|null $lettreData Optional new Letter data
     * @return bool True on success, false on failure
     */
    public static function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $pdo = self::getConnection();

        try {
            // Retrieve existing supporting documents
            $stmt = $pdo->prepare("SELECT PiecesJustificatives FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute(['numetu' => $data['NumEtu']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            $pieces = $existing && !empty($existing['PiecesJustificatives'])
                ? json_decode($existing['PiecesJustificatives'], true)
                : [];

            // Update files if provided
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

            // Update SQL record
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
                'NumEtu' => $data['NumEtu'],
                'Adresse' => $data['Adresse'] ?? '',
                'CodePostal' => $data['CodePostal'] ?? '',
                'Ville' => $data['Ville'] ?? '',
                'Telephone' => $data['Telephone'] ?? '',
                'EmailPersonnel' => $data['EmailPersonnel'] ?? '',
                'PiecesJustificatives' => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Error updating student folder: " . $e->getMessage());
            return false;
        }
    }
}

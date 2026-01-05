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
     * Update a student's folder
     *
     * Only certain fields are editable: address, postal code, city, telephone, email, and uploaded files.
     *
     * @param array $data Associative array containing fields to update
     * @param string|null $photoData Optional new photo data (binary string)
     * @param string|null $cvData Optional new CV data (binary string)
     * @return bool True on success, false on failure
     */
    public static function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null): bool
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
            if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);

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

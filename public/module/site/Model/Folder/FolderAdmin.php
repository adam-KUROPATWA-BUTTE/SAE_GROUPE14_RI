<?php

namespace Model\Folder;

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
     * @return \PDO The PDO connection instance.
     */
    private static function getConnection(): \PDO
    {
        return \Database::getInstance()->getConnection();
    }

    /**
     * Retrieve all folders from the database.
     *
     * @return array List of all student folders.
     */
    public static function getAll()
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT 
                    NumEtu,
                    Nom,
                    Prenom,
                    EmailPersonnel as email,
                    Telephone,
                    Type,
                    Zone,
                    DateNaissance,
                    Sexe,
                    Adresse,
                    CodePostal,
                    Ville,
                    EmailAMU,
                    CodeDepartement,
                    IsComplete,
                    PiecesJustificatives
                FROM dossiers
                ORDER BY Nom, Prenom
            ");

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching folders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve incomplete folders (where IsComplete is 0 or NULL).
     *
     * @return array List of incomplete folders.
     */
    public static function getDossiersIncomplets()
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT 
                    NumEtu,
                    Nom,
                    Prenom,
                    EmailPersonnel as email,
                    Telephone,
                    Type,
                    Zone,
                    DateNaissance,
                    Sexe,
                    Adresse,
                    CodePostal,
                    Ville,
                    EmailAMU,
                    CodeDepartement,
                    IsComplete,
                    PiecesJustificatives
                FROM dossiers
                WHERE IsComplete = 0 OR IsComplete IS NULL
                ORDER BY Nom, Prenom
            ");

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching incomplete folders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new folder in the database.
     *
     * @param array       $data           Associative array containing student information.
     * @param string|null $photoData      Binary data of the photo (optional).
     * @param string|null $cvData         Binary data of the CV (optional).
     * @param string|null $conventionData Binary data of the Internship Agreement (optional).
     * @param string|null $lettreData     Binary data of the Motivation Letter (optional).
     * @return bool True on success, False on failure.
     */
    public static function creerDossier($data, $photoData = null, $cvData = null, $conventionData = null, $lettreData = null)
    {
        $pdo = self::getConnection();

        try {
            // Convert empty strings to NULL to keep the database clean
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // Ensure DateNaissance is NULL or a valid DATE format
            if (!empty($data['naissance'])) {
                $date = \DateTime::createFromFormat('Y-m-d', $data['naissance']);
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

            // Prepare justificative files as a JSON object
            $pieces = [];
            if ($photoData !== null) {
                $pieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $pieces['cv'] = base64_encode($cvData);
            }
            // Add Convention if provided
            if ($conventionData !== null) {
                $pieces['convention'] = base64_encode($conventionData);
            }
            // Add Motivation Letter if provided
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
        } catch (\PDOException $e) {
            error_log("Error creating folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve a folder by personal email.
     *
     * @param string $email The student's personal email.
     * @return array|null The student data or null if not found.
     */
    public static function getByEmail(string $email)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu,
                    Nom,
                    Prenom,
                    EmailPersonnel as email,
                    Telephone,
                    Type,
                    Zone,
                    DateNaissance,
                    Sexe,
                    Adresse,
                    CodePostal,
                    Ville,
                    EmailAMU,
                    CodeDepartement,
                    IsComplete,
                    PiecesJustificatives
                FROM dossiers 
                WHERE EmailPersonnel = :email 
                LIMIT 1
            ");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching folder by email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve a folder by student number (NumEtu).
     *
     * @param string $numetu The student ID number.
     * @return array|null The student data or null if not found.
     */
    public static function getByNumetu(string $numetu)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu,
                    Nom,
                    Prenom,
                    EmailPersonnel as email,
                    Telephone,
                    Type,
                    Zone,
                    DateNaissance,
                    Sexe,
                    Adresse,
                    CodePostal,
                    Ville,
                    EmailAMU,
                    CodeDepartement,
                    IsComplete,
                    PiecesJustificatives
                FROM dossiers 
                WHERE NumEtu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching folder by NumEtu: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get full student details including decoded justificative files.
     *
     * @param string $numetu The student ID number.
     * @return array|null The student data containing a 'pieces' array, or null.
     */
    public static function getStudentDetails(string $numetu)
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
            $stmt->execute([':numetu' => $numetu]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                // Decode justificative files JSON into an array
                $result['pieces'] = !empty($result['PiecesJustificatives'])
                    ? json_decode($result['PiecesJustificatives'], true) ?? []
                    : [];
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Error fetching student details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing folder.
     *
     * @param array       $data           Associative array containing student information.
     * @param string|null $photoData      Binary data of the photo (optional).
     * @param string|null $cvData         Binary data of the CV (optional).
     * @param string|null $conventionData Binary data of the Internship Agreement (optional).
     * @param string|null $lettreData     Binary data of the Motivation Letter (optional).
     * @return bool True on success, False on failure.
     */
    public static function updateDossier($data, $photoData = null, $cvData = null, $conventionData = null, $lettreData = null)
    {
        $pdo = self::getConnection();

        try {
            // Retrieve old justificative files to preserve existing ones if not replaced
            $existing = self::getByNumetu($data['NumEtu']);
            $oldPieces = [];
            if ($existing && !empty($existing['PiecesJustificatives'])) {
                $oldPieces = json_decode($existing['PiecesJustificatives'], true) ?? [];
            }

            // Update files only if new data is provided
            if ($photoData !== null) {
                $oldPieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $oldPieces['cv'] = base64_encode($cvData);
            }
            if ($conventionData !== null) {
                $oldPieces['convention'] = base64_encode($conventionData);
            }
            if ($lettreData !== null) {
                $oldPieces['lettre_motivation'] = base64_encode($lettreData);
            }

            $piecesJson = json_encode($oldPieces);

            $stmt = $pdo->prepare("
                UPDATE dossiers
                SET 
                    Nom = :Nom,
                    Prenom = :Prenom,
                    DateNaissance = :DateNaissance,
                    Sexe = :Sexe,
                    Adresse = :Adresse,
                    CodePostal = :CodePostal,
                    Ville = :Ville,
                    EmailPersonnel = :EmailPersonnel,
                    EmailAMU = :EmailAMU,
                    Telephone = :Telephone,
                    CodeDepartement = :CodeDepartement,
                    Type = :Type,
                    Zone = :Zone,
                    PiecesJustificatives = :PiecesJustificatives
                WHERE NumEtu = :NumEtu
            ");

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
        } catch (\PDOException $e) {
            error_log("Error updating folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a folder by student ID.
     *
     * @param string $numetu The student ID number.
     * @return bool True on success, False on failure.
     */
    public static function supprimerDossier($numetu)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("DELETE FROM dossiers WHERE NumEtu = :numetu");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Error deleting folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a reminder.
     * (Placeholder method, currently logic is empty).
     *
     * @param int    $dossierId The folder database ID.
     * @param string $message   The reminder message.
     * @param int    $adminId   The ID of the admin creating the reminder.
     * @return bool Always returns true for now.
     */
    public static function ajouterRelance($dossierId, $message, $adminId)
    {
        return true;
    }

    /**
     * Validate a folder (Sets IsComplete to 1).
     *
     * @param string   $numetu  The student ID number.
     * @param int|null $adminId The admin ID performing the validation (optional).
     * @return bool True on success, False on failure.
     */
    public static function valider($numetu, $adminId = null)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                UPDATE dossiers 
                SET IsComplete = 1
                WHERE NumEtu = :numetu
            ");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Error validating folder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle the complete/incomplete status of a folder.
     *
     * @param string $numetu The student ID number.
     * @return bool True on success, False on failure.
     */
    public static function toggleCompleteStatus(string $numetu): bool
    {
        $pdo = self::getConnection();

        try {
            // Retrieve current status
            $stmt = $pdo->prepare("
                SELECT IsComplete 
                FROM dossiers 
                WHERE NumEtu = :numetu
            ");
            $stmt->execute([':numetu' => $numetu]);
            $current = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$current) {
                return false;
            }

            // Toggle logic: 0/NULL becomes 1, 1 becomes 0
            $newStatus = ($current['IsComplete'] == 1) ? 0 : 1;

            $stmt = $pdo->prepare("
                UPDATE dossiers 
                SET IsComplete = :status
                WHERE NumEtu = :numetu
            ");
            return $stmt->execute([':status' => $newStatus, ':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Error toggling folder status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload a photo and update the 'PiecesJustificatives' JSON column.
     *
     * @param string $numetu The student ID number.
     * @param array  $file   The uploaded file array from $_FILES.
     * @return bool True on success, False on failure.
     */
    public static function uploadPhoto($numetu, $file)
    {
        if (!file_exists($file['tmp_name'])) {
            return false;
        }
        $photoData = file_get_contents($file['tmp_name']);
        return self::updatePieceJustificative($numetu, 'photo', $photoData);
    }

    /**
     * Upload a CV and update the 'PiecesJustificatives' JSON column.
     *
     * @param string $numetu The student ID number.
     * @param array  $file   The uploaded file array from $_FILES.
     * @return bool True on success, False on failure.
     */
    public static function uploadCV($numetu, $file)
    {
        if (!file_exists($file['tmp_name'])) {
            return false;
        }
        $cvData = file_get_contents($file['tmp_name']);
        return self::updatePieceJustificative($numetu, 'cv', $cvData);
    }

    /**
     * Private helper method to update a specific file inside the PiecesJustificatives JSON.
     *
     * @param string $numetu The student ID number.
     * @param string $type   The key/type of the document (e.g., 'photo', 'cv').
     * @param string $data   The binary data of the file.
     * @return bool True on success, False on failure.
     */
    private static function updatePieceJustificative(string $numetu, string $type, string $data)
    {
        $pdo = self::getConnection();

        try {
            $existing = self::getByNumetu($numetu);
            $pieces = [];
            if ($existing && !empty($existing['PiecesJustificatives'])) {
                $pieces = json_decode($existing['PiecesJustificatives'], true) ?? [];
            }

            // Update or add the new file (Base64 encoded)
            $pieces[$type] = base64_encode($data);
            $piecesJson = json_encode($pieces);

            $stmt = $pdo->prepare("
                UPDATE dossiers
                SET PiecesJustificatives = :pieces
                WHERE NumEtu = :numetu
            ");
            return $stmt->execute([':pieces' => $piecesJson, ':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Error updating justificative file ($type): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search folders using advanced filters.
     *
     * @param array $filters Associative array of filters (type, zone, search, dates, completeness).
     * @return array List of folders matching the filters.
     */
    public static function rechercher(array $filters)
    {
        $pdo = self::getConnection();
        $params = [];

        $sql = "
            SELECT 
                NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone,
                Type, Zone, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                EmailAMU, CodeDepartement, IsComplete, PiecesJustificatives
            FROM dossiers
            WHERE 1=1
        ";

        // --- Filter: Completeness Status ---
        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            if ($filters['complet'] == '1') {
                $sql .= " AND IsComplete = 1";
            } else {
                // Incomplete includes 0 and NULL
                $sql .= " AND (IsComplete = 0 OR IsComplete IS NULL)";
            }
        }

        // --- Filter: Date Range (Birth Date) ---
        if (!empty($filters['date_debut'])) {
            $sql .= " AND DateNaissance >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $sql .= " AND DateNaissance <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }

        // --- Other Filters (Type, Zone) ---
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $sql .= " AND Type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['zone']) && $filters['zone'] !== 'all') {
            $sql .= " AND Zone = :zone";
            $params[':zone'] = $filters['zone'];
        }

        // --- Text Search (Name, Email, Student ID) ---
        if (!empty($filters['search'])) {
            $sql .= " AND (Nom LIKE :search OR Prenom LIKE :search OR NumEtu LIKE :search OR EmailPersonnel LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // --- Sorting ---
        $orderDirection = (isset($filters['tri_date']) && strtoupper($filters['tri_date']) === 'ASC') ? 'ASC' : 'DESC';
        $sql .= " ORDER BY DateNaissance $orderDirection, Nom ASC";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error searching folders: " . $e->getMessage());
            return [];
        }
    }
}

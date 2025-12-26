<?php
namespace Model\Folder;

use Model\Folder\FolderAdmin;

class FolderAdmin
{
    /**
     * Get a PDO connection via the Database class
     *
     * @return \PDO
     */
    private static function getConnection(): \PDO
    {
        return \Database::getInstance()->getConnection();
    }

    /**
     * Retrieve all folders (equivalent to getAll)
     *
     * @return array List of all folders
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
     * Retrieve incomplete folders (IsComplete = 0 or NULL)
     *
     * @return array List of incomplete folders
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
     * Create a new folder in the folders table
     *
     * @param array $data Folder data
     * @param string|null $photoData Binary photo data
     * @param string|null $cvData Binary CV data
     * @return bool
     */
    public static function creerDossier($data, $photoData = null, $cvData = null)
    {
        $pdo = self::getConnection();

        try {
            // Convert empty strings to NULL
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // Ensure DateNaissance is NULL or valid DATE
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

            // Prepare justificative files as JSON
            $pieces = [];
            if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
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
     * Retrieve a folder by email
     *
     * @param string $email
     * @return array|null
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
     * Retrieve a folder by student number (NumEtu)
     *
     * @param string $numetu
     * @return array|null
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
     * Get full student details with decoded justificative files
     *
     * @param string $numetu
     * @return array|null
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
                // Decode justificative files JSON
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
     * Update a folder
     *
     * @param array $data Folder data
     * @param string|null $photoData Binary photo data
     * @param string|null $cvData Binary CV data
     * @return bool
     */
    public static function updateDossier($data, $photoData = null, $cvData = null)
    {
        $pdo = self::getConnection();

        try {
            // Retrieve old justificative files
            $existing = self::getByNumetu($data['NumEtu']);
            $oldPieces = [];
            if ($existing && !empty($existing['PiecesJustificatives'])) {
                $oldPieces = json_decode($existing['PiecesJustificatives'], true) ?? [];
            }

            if ($photoData !== null) $oldPieces['photo'] = base64_encode($photoData);
            if ($cvData !== null) $oldPieces['cv'] = base64_encode($cvData);
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
     * Delete a folder by NumEtu
     *
     * @param string $numetu
     * @return bool
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
     * Add a reminder (currently empty, to be completed as needed)
     *
     * @param int $dossierId
     * @param string $message
     * @param int $adminId
     * @return bool
     */
    public static function ajouterRelance($dossierId, $message, $adminId)
    {
        return true;
    }

    /**
     * Validate a folder (for example, set IsComplete to 1)
     *
     * @param string $numetu
     * @param int|null $adminId
     * @return bool
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
     * Toggle the complete/incomplete status of a folder
     *
     * @param string $numetu
     * @return bool
     */
    public static function toggleCompleteStatus(string $numetu): bool
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT IsComplete 
                FROM dossiers 
                WHERE NumEtu = :numetu
            ");
            $stmt->execute([':numetu' => $numetu]);
            $current = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$current) return false;

            // Toggle status: 0/NULL becomes 1, 1 becomes 0
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
     * Upload a photo and update PiecesJustificatives
     *
     * @param string $numetu
     * @param array $file Uploaded file array
     * @return bool
     */
    public static function uploadPhoto($numetu, $file)
    {
        if (!file_exists($file['tmp_name'])) return false;
        $photoData = file_get_contents($file['tmp_name']);
        return self::updatePieceJustificative($numetu, 'photo', $photoData);
    }

    /**
     * Upload a CV and update PiecesJustificatives
     *
     * @param string $numetu
     * @param array $file Uploaded file array
     * @return bool
     */
    public static function uploadCV($numetu, $file)
    {
        if (!file_exists($file['tmp_name'])) return false;
        $cvData = file_get_contents($file['tmp_name']);
        return self::updatePieceJustificative($numetu, 'cv', $cvData);
    }

    /**
     * Private method to update a justificative file in PiecesJustificatives JSON
     *
     * @param string $numetu
     * @param string $type 'photo' or 'cv'
     * @param string $data Binary data
     * @return bool
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
     * Advanced filters with filters
     * * @param array $filters array 
     * @return array list folder after filters
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

        // --- Filter : Complétude ---
        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            if ($filters['complet'] == '1') {
                $sql .= " AND IsComplete = 1";
            } else {
                // Incomplet inclut 0 et NULL
                $sql .= " AND (IsComplete = 0 OR IsComplete IS NULL)";
            }
        }

        // --- Filter : Date  ---
        if (!empty($filters['date_debut'])) {
            $sql .= " AND DateNaissance >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $sql .= " AND DateNaissance <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }

        // --- Other filters 
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

<?php
namespace Model;

class FolderAdmin
{
    private static function getConnection(): \PDO
    {
        return \Database::getInstance()->getConnection();
    }

    // Récupérer tous les dossiers (équivalent de getAll)
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
            error_log("Erreur récupération dossiers : " . $e->getMessage());
            return [];
        }
    }

    // Récupérer dossiers incomplets (IsComplete = 0 ou NULL)
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
            error_log("Erreur récupération dossiers incomplets : " . $e->getMessage());
            return [];
        }
    }

    // Créer un nouveau dossier dans la table dossiers
    public static function creerDossier($data, $photoData = null, $cvData = null)
    {
        $pdo = self::getConnection();

        try {
            // ✅ Convertir les chaînes vides en NULL
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // ✅ Gérer spécifiquement DateNaissance qui doit être NULL ou DATE valide
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

            // Préparer les pièces justificatives en JSON
            $pieces = [];
            if ($photoData !== null) {
                $pieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $pieces['cv'] = base64_encode($cvData);
            }
            $piecesJson = json_encode($pieces);

            $result = $stmt->execute([
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

            return $result;
        } catch (\PDOException $e) {
            error_log("Erreur création dossier : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer un dossier par email
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
            error_log("Erreur récupération dossier par email : " . $e->getMessage());
            return null;
        }
    }

    // Récupérer un dossier par NumEtu
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
            error_log("Erreur récupération dossier par NumEtu : " . $e->getMessage());
            return null;
        }
    }

    // Récupérer les détails complets d'un étudiant avec décodage des pièces
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
                // Décoder les pièces justificatives JSON
                if (!empty($result['PiecesJustificatives'])) {
                    $result['pieces'] = json_decode($result['PiecesJustificatives'], true) ?? [];
                } else {
                    $result['pieces'] = [];
                }
            }
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Erreur récupération détails étudiant : " . $e->getMessage());
            return null;
        }
    }
    // Mettre à jour un dossier
    public static function updateDossier($data, $photoData = null, $cvData = null)
    {
        $pdo = self::getConnection();

        try {
            // Récupérer l'ancien PiecesJustificatives
            $existing = self::getByNumetu($data['NumEtu']);
            $oldPieces = [];
            if ($existing && !empty($existing['PiecesJustificatives'])) {
                $oldPieces = json_decode($existing['PiecesJustificatives'], true) ?? [];
            }

            if ($photoData !== null) {
                $oldPieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $oldPieces['cv'] = base64_encode($cvData);
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
            error_log("Erreur mise à jour dossier : " . $e->getMessage());
            return false;
        }
    }

    // Supprimer un dossier par NumEtu
    public static function supprimerDossier($numetu)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("DELETE FROM dossiers WHERE NumEtu = :numetu");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Erreur suppression dossier : " . $e->getMessage());
            return false;
        }
    }

    // Ajouter une relance (vide ici, à compléter selon besoin)
    public static function ajouterRelance($dossierId, $message, $adminId)
    {
        return true;
    }

    // Valider un dossier (par exemple changer IsComplete à 1)
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
            error_log("Erreur validation dossier : " . $e->getMessage());
            return false;
        }
    }

    // Upload photo (met à jour PiecesJustificatives)
    public static function uploadPhoto($numetu, $file)
    {
        if (!file_exists($file['tmp_name'])) {
            return false;
        }
        $photoData = file_get_contents($file['tmp_name']);
        return self::updatePieceJustificative($numetu, 'photo', $photoData);
    }

    // Upload CV (met à jour PiecesJustificatives)
    public static function uploadCV($numetu, $file)
    {
        if (!file_exists($file['tmp_name'])) {
            return false;
        }
        $cvData = file_get_contents($file['tmp_name']);
        return self::updatePieceJustificative($numetu, 'cv', $cvData);
    }

    // Méthode privée pour mettre à jour une pièce justificative dans PiecesJustificatives JSON
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
            return $stmt->execute([
                ':pieces' => $piecesJson,
                ':numetu' => $numetu
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour pièce justificative ($type) : " . $e->getMessage());
            return false;
        }
    }
    // Marquer un dossier comme complet (IsComplete = 1)
    public static function markAsComplete(string $numetu): bool
    {
        $pdo = self::getConnection();

        // Vérifier si l'étudiant existe
        $existing = self::getByNumetu($numetu);
        if (!$existing) {
            error_log("Étudiant non trouvé pour NumEtu: $numetu");
            return false; // ou lever une exception
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE dossiers 
                SET IsComplete = 1
                WHERE NumEtu = :numetu
            ");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Erreur marquage dossier complet : " . $e->getMessage());
            return false;
        }
    }



    // ✅ BONUS - Marquer un dossier comme incomplet (si besoin de revenir en arrière)
    public static function markAsIncomplete(string $numetu): bool
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                UPDATE dossiers 
                SET IsComplete = 0
                WHERE NumEtu = :numetu
            ");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Erreur marquage dossier incomplet : " . $e->getMessage());
            return false;
        }
    }
}

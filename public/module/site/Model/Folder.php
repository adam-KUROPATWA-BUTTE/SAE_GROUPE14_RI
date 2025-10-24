<?php
namespace Model;

class Folder
{
    private static function getConnection(): \PDO
    {
        return \Database::getInstance()->getConnection();
    }

    public static function getAll()
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT 
                    NumEtu as numetu,
                    Nom as nom,
                    Prenom as prenom,
                    EmailPersonnel as email,
                    Telephone as telephone,
                    Type as type,
                    Zone as zone,
                    NULL as stage,
                    NULL as etude,
                    NULL as photo,
                    NULL as cv,
                    0 as total_pieces,
                    0 as pieces_fournies,
                    0 as date_derniere_relance
                FROM dossiers 
                ORDER BY Nom, Prenom
            ");

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur récupération étudiants : " . $e->getMessage());
            return [];
        }
    }

    public static function getDossiersIncomplets()
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT 
                    NumEtu as numetu,
                    Nom as nom,
                    Prenom as prenom,
                    EmailPersonnel as email,
                    Telephone as telephone,
                    Type as type,
                    Zone as zone,
                    NULL as stage,
                    NULL as etude,
                    NULL as photo,
                    NULL as cv,
                    0 as total_pieces,
                    0 as pieces_fournies,
                    NULL as date_derniere_relance
                FROM dossiers 
                WHERE IsComplete = 0
                ORDER BY Nom, Prenom
            ");

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur récupération dossiers incomplets : " . $e->getMessage());
            return [];
        }
    }

    public static function creerDossier($data)
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
                    NumEtu, Nom, Prenom, EmailPersonnel, Telephone, 
                    Type, Zone, IsComplete, DateNaissance, Sexe, 
                    Adresse, CodePostal, Ville, EmailAMU, CodeDepartement
                ) VALUES (
                    :numetu, :nom, :prenom, :email, :telephone,
                    :type, :zone, 0, :naissance, :sexe,
                    :adresse, :cp, :ville, :email_amu, :departement
                )
            ");

            return $stmt->execute([
                ':numetu' => $data['numetu'],
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':telephone' => $data['telephone'],
                ':type' => $data['type'],
                ':zone' => $data['zone'],
                ':naissance' => $data['naissance'],
                ':sexe' => $data['sexe'],
                ':adresse' => $data['adresse'],
                ':cp' => $data['cp'],
                ':ville' => $data['ville'],
                ':email_amu' => $data['email_amu'],
                ':departement' => $data['departement']
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur création dossier : " . $e->getMessage());
            return false;
        }
    }

    public static function getByEmail(string $email)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu as numetu,
                    Nom as nom,
                    Prenom as prenom,
                    EmailPersonnel as email,
                    Telephone as telephone,
                    Type as type
                FROM dossiers 
                WHERE EmailPersonnel = :email 
                LIMIT 1
            ");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur récupération étudiant : " . $e->getMessage());
            return null;
        }
    }

    public static function getByNumetu(string $numetu)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu as numetu,
                    Nom as nom,
                    Prenom as prenom,
                    EmailPersonnel as email,
                    Telephone as telephone,
                    Type as type
                FROM dossiers 
                WHERE NumEtu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur récupération étudiant : " . $e->getMessage());
            return null;
        }
    }

    public static function getStudentDetails(string $numetu)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu as numetu,
                    Nom as nom,
                    Prenom as prenom,
                    EmailPersonnel as email,
                    Telephone as telephone,
                    Type as type,
                    Zone as zone,
                    DateNaissance as naissance,
                    Sexe as sexe,
                    Adresse as adresse,
                    CodePostal as cp,
                    Ville as ville,
                    EmailPersonnel as email_perso,
                    EmailAMU as email_amu,
                    CodeDepartement as departement,
                    NULL as mobilite_type,
                    NULL as photo,
                    NULL as cv
                FROM dossiers 
                WHERE NumEtu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur récupération détails étudiant : " . $e->getMessage());
            return null;
        }
    }

    public static function updateStudent($data)
    {
        $pdo = self::getConnection();

        try {
            // Convertir les chaînes vides en NULL
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // Gérer spécifiquement DateNaissance qui doit être NULL ou DATE valide
            if (!empty($data['naissance'])) {
                $date = \DateTime::createFromFormat('Y-m-d', $data['naissance']);
                if (!$date || $date->format('Y-m-d') !== $data['naissance']) {
                    $data['naissance'] = null;
                }
            }

            $stmt = $pdo->prepare("
                UPDATE dossiers 
                SET 
                    Nom = :nom,
                    Prenom = :prenom,
                    EmailPersonnel = :email,
                    Telephone = :telephone,
                    Type = :type,
                    Zone = :zone,
                    DateNaissance = :naissance,
                    Sexe = :sexe,
                    Adresse = :adresse,
                    CodePostal = :cp,
                    Ville = :ville,
                    EmailAMU = :email_amu,
                    CodeDepartement = :departement
                WHERE NumEtu = :numetu
            ");

            return $stmt->execute([
                ':numetu' => $data['numetu'],
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':telephone' => $data['telephone'],
                ':type' => $data['type'],
                ':zone' => $data['zone'],
                ':naissance' => $data['naissance'],
                ':sexe' => $data['sexe'],
                ':adresse' => $data['adresse'],
                ':cp' => $data['cp'],
                ':ville' => $data['ville'],
                ':email_amu' => $data['email_amu'],
                ':departement' => $data['departement']
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour étudiant : " . $e->getMessage());
            return false;
        }
    }

    public static function valider($numetu, $adminId)
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

    public static function ajouterRelance($dossierId, $message, $adminId)
    {
        return true;
    }

    public static function ajouterEtudiant($numetu, $nom, $prenom, $email, $telephone)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO dossiers (NumEtu, Nom, Prenom, EmailPersonnel, Telephone)
                VALUES (:numetu, :nom, :prenom, :email, :telephone)
            ");

            return $stmt->execute([
                ':numetu' => $numetu,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':telephone' => $telephone
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur ajout étudiant : " . $e->getMessage());
            return false;
        }
    }

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

    public static function uploadPhoto($numetu, $file)
    {
        // À implémenter selon vos besoins
        return true;
    }

    public static function uploadCV($numetu, $file)
    {
        // À implémenter selon vos besoins
        return true;
    }


}
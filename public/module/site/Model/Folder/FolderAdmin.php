<?php
namespace Model;

class Folder
{
    private static function getConnection(): \PDO
    {
        // ✅ Utiliser la classe Database au lieu de créer une connexion directe
        return \Database::getInstance()->getConnection();
    }

    public static function getAll()
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT 
                    numetu,
                    nom,
                    prenom,
                    email,
                    telephone,
                    type_etudiant as type,
                    'europe' as zone,
                    NULL as stage,
                    NULL as etude,
                    NULL as photo,
                    NULL as cv,
                    0 as total_pieces,
                    0 as pieces_fournies,
                    0 as date_derniere_relance
                FROM etudiants 
                ORDER BY nom, prenom
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
                    numetu,
                    nom,
                    prenom,
                    email,
                    telephone,
                    type_etudiant as type,
                    'europe' as zone,
                    NULL as stage,
                    NULL as etude,
                    NULL as photo,
                    NULL as cv,
                    0 as total_pieces,
                    0 as pieces_fournies,
                    NULL as date_derniere_relance
                FROM etudiants 
                ORDER BY nom, prenom
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
            $stmt = $pdo->prepare("
                INSERT INTO etudiants (
                    numetu, nom, prenom, email, telephone, 
                    type_etudiant, password
                ) VALUES (
                    :numetu, :nom, :prenom, :email, :telephone,
                    :type_etudiant, :password
                )
            ");

            return $stmt->execute([
                ':numetu' => $data['numetu'],
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':telephone' => $data['telephone'],
                ':type_etudiant' => $data['type'] ?? null,
                ':password' => password_hash($data['password'] ?? 'default123', PASSWORD_DEFAULT)
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
                    numetu,
                    nom,
                    prenom,
                    email,
                    telephone,
                    type_etudiant as type
                FROM etudiants 
                WHERE email = :email 
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
                    numetu,
                    nom,
                    prenom,
                    email,
                    telephone,
                    type_etudiant as type
                FROM etudiants 
                WHERE numetu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur récupération étudiant : " . $e->getMessage());
            return null;
        }
    }

    // ✅ NOUVELLE MÉTHODE - Récupérer un étudiant complet pour affichage
    public static function getStudentDetails(string $numetu)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    numetu,
                    nom,
                    prenom,
                    email,
                    telephone,
                    type_etudiant as type,
                    'europe' as zone,
                    NULL as naissance,
                    NULL as sexe,
                    NULL as adresse,
                    NULL as cp,
                    NULL as ville,
                    email as email_perso,
                    NULL as email_amu,
                    NULL as departement,
                    NULL as mobilite_type,
                    NULL as photo,
                    NULL as cv
                FROM etudiants 
                WHERE numetu = :numetu 
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numetu]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur récupération détails étudiant : " . $e->getMessage());
            return null;
        }
    }

    // ✅ NOUVELLE MÉTHODE - Mettre à jour un étudiant
    public static function updateStudent($data)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                UPDATE etudiants 
                SET 
                    nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    telephone = :telephone,
                    type_etudiant = :type_etudiant
                WHERE numetu = :numetu
            ");

            return $stmt->execute([
                ':numetu' => $data['numetu'],
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':telephone' => $data['telephone'],
                ':type_etudiant' => $data['type'] ?? null
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
                UPDATE etudiants 
                SET last_connexion = NOW()
                WHERE numetu = :numetu
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
                INSERT INTO etudiants (numetu, nom, prenom, email, telephone, password)
                VALUES (:numetu, :nom, :prenom, :email, :telephone, :password)
            ");

            return $stmt->execute([
                ':numetu' => $numetu,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':telephone' => $telephone,
                ':password' => password_hash('default123', PASSWORD_DEFAULT)
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
            $stmt = $pdo->prepare("DELETE FROM etudiants WHERE numetu = :numetu");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Erreur suppression dossier : " . $e->getMessage());
            return false;
        }
    }

    public static function uploadPhoto($numetu, $file)
    {
        return true;
    }

    public static function uploadCV($numetu, $file)
    {
        return true;
    }

    public static function updateLastConnexion($numetu)
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                UPDATE etudiants 
                SET last_connexion = NOW()
                WHERE numetu = :numetu
            ");
            return $stmt->execute([':numetu' => $numetu]);
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour connexion : " . $e->getMessage());
            return false;
        }
    }
}
<?php

namespace Model\User;

use PDO;
use PDOException;

/**
 * Class UserStudent
 *
 * Model responsible for managing student accounts.
 * Handles authentication, registration, folder checks, and data retrieval.
 */
class UserStudent
{
    /**
     * Authenticates a student using their student number (NumEtu).
     *
     * @param string $numetu   The student identifier.
     * @param string $password The student password.
     * @return array{success: bool, role?: string} Authentication result.
     */
    public static function login(string $numetu, string $password): array
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, numetu, nom, prenom, password, type_etudiant 
                    FROM etudiants WHERE numetu = :numetu";
            $stmt = $db->prepare($sql);
            $stmt->execute(['numetu' => $numetu]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password safely (cast to string to avoid null issues)
            if ($user && is_array($user) && password_verify($password, (string)($user['password'] ?? ''))) {
                // Initialize Session
                $_SESSION['user_role'] = 'etudiant';
                $_SESSION['etudiant_id'] = $user['id'];
                $_SESSION['etudiant_nom'] = $user['nom'];
                $_SESSION['etudiant_prenom'] = $user['prenom'];
                $_SESSION['numetu'] = $user['numetu'];
                $_SESSION['type_etudiant'] = $user['type_etudiant'];

                // Update last connection timestamp
                $db->prepare("UPDATE etudiants SET last_connexion = NOW() WHERE id = :id")
                   ->execute(['id' => $user['id']]);

                return ['success' => true, 'role' => 'etudiant'];
            }

            return ['success' => false];
        } catch (PDOException $e) {
            error_log("Student login error: " . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Registers a new student (Public registration).
     *
     * @param string $email        Student email.
     * @param string $password     Password (will be hashed).
     * @param string $nom          Last name.
     * @param string $prenom       First name.
     * @param string $typeEtudiant Type ('entrant' or 'sortant').
     * @return bool True on success, false on failure or duplicates.
     */
    public static function register(string $email, string $password, string $nom, string $prenom, string $typeEtudiant): bool
    {
        try {
            $db = \Database::getInstance()->getConnection();

            // 1. Check for existing email in both admins and students tables
            $sql = "SELECT id FROM admins WHERE email = :email 
                    UNION 
                    SELECT id FROM etudiants WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                return false; // Email already in use
            }

            // 2. Validate Student Type
            if (!in_array($typeEtudiant, ['entrant', 'sortant'], true)) {
                return false;
            }

            // 3. Hash Password and Insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO etudiants (email, password, nom, prenom, type_etudiant, created_at) 
                    VALUES (:email, :password, :nom, :prenom, :type_etudiant, NOW())";
            $stmt = $db->prepare($sql);

            return $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'nom' => $nom,
                'prenom' => $prenom,
                'type_etudiant' => $typeEtudiant
            ]);
        } catch (PDOException $e) {
            error_log("Student registration error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Initiates a password reset process.
     * Placeholder implementation.
     *
     * @param string $email The email address.
     * @return void
     */
    public static function resetPassword(string $email): void
    {
        error_log("Password reset requested for Student: " . $email);
        // TODO: Implement actual reset logic
    }

    /**
     * Checks if a folder already exists for this student.
     *
     * @param int $etudiantId The internal student ID.
     * @return bool True if folder exists.
     */
    public static function checkDossierExists(int $etudiantId): bool
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT COUNT(*) as count FROM dossiers WHERE etudiant_id = :etudiant_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['etudiant_id' => $etudiantId]);
            
            // fetchColumn is safer for single value results
            $count = $stmt->fetchColumn();
            
            return $count > 0;
        } catch (PDOException $e) {
            error_log("checkDossierExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves the student's folder data.
     *
     * @param int $etudiantId The internal student ID.
     * @return array<string, mixed>|null Folder data or null on failure.
     */
    public static function getDossier(int $etudiantId): ?array
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT * FROM dossiers WHERE etudiant_id = :etudiant_id LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['etudiant_id' => $etudiantId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        } catch (PDOException $e) {
            error_log("getDossier error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Creates a new folder for the student.
     * Fails if one already exists.
     *
     * @param int $etudiantId The internal student ID.
     * @return bool True on success.
     */
    public static function createDossier(int $etudiantId): bool
    {
        try {
            $db = \Database::getInstance()->getConnection();

            // Prevent duplicate folders
            if (self::checkDossierExists($etudiantId)) {
                return false;
            }

            $sql = "INSERT INTO dossiers (etudiant_id, statut, date_creation) 
                    VALUES (:etudiant_id, 'en_cours', NOW())";
            $stmt = $db->prepare($sql);

            return $stmt->execute(['etudiant_id' => $etudiantId]);
        } catch (PDOException $e) {
            error_log("createDossier error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logs out the student.
     *
     * Clears session and cookies.
     *
     * @return bool True on success.
     */
    public static function logout(): bool
    {
        try {
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    (string)session_name(), // Cast to string for strict typing
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();
            return true;
        } catch (\Exception $e) {
            error_log("Student logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if the current user is logged in as a student.
     *
     * @return bool True if student, false otherwise.
     */
    public static function isStudent(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'etudiant';
    }

    /**
     * Retrieves student profile by ID.
     *
     * @param int $id The internal student ID.
     * @return array<string, mixed>|null Student profile data or null.
     */
    public static function getById(int $id): ?array
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, nom, prenom, numetu, type_etudiant, created_at, last_connexion 
                    FROM etudiants WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        } catch (PDOException $e) {
            error_log("getById student error: " . $e->getMessage());
            return null;
        }
    }
}
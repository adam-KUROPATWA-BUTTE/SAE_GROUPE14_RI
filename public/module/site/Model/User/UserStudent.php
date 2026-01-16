<?php

// phpcs:disable Generic.Files.LineLength

namespace Model\User;

use PDO;
use PDOException;
use Exception;

/**
 * UserStudent
 *
 * Handles student authentication, registration, and folder management.
 */
class UserStudent
{
    /**
     * Get PDO connection
     */
    private static function getConnection(): PDO
    {
        if (!class_exists('\Database')) {
            throw new \RuntimeException('Database class not found');
        }

        return \Database::getInstance()->getConnection();
    }

    /**
     * Student login
     *
     * @param string $numetu
     * @param string $password
     * @return array<string, bool|string>
     */
    public static function login(string $numetu, string $password): array
    {
        try {
            $db = self::getConnection();

            $stmt = $db->prepare(
                'SELECT id, numetu, nom, prenom, password, type_etudiant
                 FROM etudiants WHERE numetu = :numetu'
            );
            $stmt->execute([':numetu' => $numetu]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($user)) {
                return ['success' => false];
            }

            $hashedPassword = $user['password'] ?? '';
            if ($hashedPassword && password_verify($password, (string)$hashedPassword)) {
                $_SESSION['user_role'] = 'etudiant';
                $_SESSION['etudiant_id'] = (int)($user['id'] ?? 0);
                $_SESSION['etudiant_nom'] = (string)($user['nom'] ?? '');
                $_SESSION['etudiant_prenom'] = (string)($user['prenom'] ?? '');
                $_SESSION['numetu'] = (string)($user['numetu'] ?? '');
                $_SESSION['type_etudiant'] = (string)($user['type_etudiant'] ?? '');

                $updateStmt = $db->prepare(
                    'UPDATE etudiants SET last_connexion = NOW() WHERE id = :id'
                );
                $updateStmt->execute([':id' => (int)($user['id'] ?? 0)]);

                return ['success' => true, 'role' => 'etudiant'];
            }

            return ['success' => false];
        } catch (PDOException $e) {
            error_log('Student login error: ' . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Student registration
     *
     * @param string $email
     * @param string $password
     * @param string $nom
     * @param string $prenom
     * @param string $typeEtudiant
     * @return bool
     */
    public static function register(
        string $email,
        string $password,
        string $nom,
        string $prenom,
        string $typeEtudiant
    ): bool {
        try {
            $db = self::getConnection();

            $stmt = $db->prepare(
                'SELECT id FROM admins WHERE email = :email
                 UNION
                 SELECT id FROM etudiants WHERE email = :email'
            );
            $stmt->execute([':email' => $email]);

            if ($stmt->fetch()) {
                return false;
            }
            if (!in_array($typeEtudiant, ['entrant', 'sortant'], true)) {
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare(
                'INSERT INTO etudiants (email, password, nom, prenom, type_etudiant)
                 VALUES (:email, :password, :nom, :prenom, :type_etudiant)'
            );

            return $stmt->execute([
                ':email' => $email,
                ':password' => $hashedPassword,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':type_etudiant' => $typeEtudiant
            ]);
        } catch (PDOException $e) {
            error_log('Student registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a student has a folder
     *
     * @param int $etudiantId
     * @return bool
     */
    public static function checkDossierExists(int $etudiantId): bool
    {
        try {
            $db = self::getConnection();

            $stmt = $db->prepare(
                'SELECT COUNT(*) AS count FROM dossiers WHERE etudiant_id = :etudiant_id'
            );
            $stmt->execute([':etudiant_id' => $etudiantId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return is_array($result) && ($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log('checkDossierExists error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve a student's folder
     *
     * @param int $etudiantId
     * @return array<string, mixed>|false
     */
    public static function getDossier(int $etudiantId): array|false
    {
        try {
            $db = self::getConnection();

            $stmt = $db->prepare('SELECT * FROM dossiers WHERE etudiant_id = :etudiant_id');
            $stmt->execute([':etudiant_id' => $etudiantId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return is_array($result) ? $result : false;
        } catch (PDOException $e) {
            error_log('getDossier error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a folder for a student
     *
     * @param int $etudiantId
     * @return bool
     */
    public static function createDossier(int $etudiantId): bool
    {
        try {
            $db = self::getConnection();

            if (self::checkDossierExists($etudiantId)) {
                return false;
            }

            $stmt = $db->prepare(
                'INSERT INTO dossiers (etudiant_id, statut, date_creation)
                 VALUES (:etudiant_id, \'en_cours\', NOW())'
            );

            return $stmt->execute([':etudiant_id' => $etudiantId]);
        } catch (PDOException $e) {
            error_log('createDossier error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Student logout
     *
     * @return bool
     */
    public static function logout(): bool
    {
        try {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                $name = (string) session_name();
                setcookie(
                    $name,
                    '',
                    time() - 42000,
                    $params['path'] ?? '',
                    $params['domain'] ?? '',
                    $params['secure'] ?? false,
                    $params['httponly'] ?? false
                );
            }

            session_destroy();
            return true;
        } catch (Exception $e) {
            error_log('Student logout error: ' . $e->getMessage());
            return false;
        }
    }

    public static function isStudent(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'etudiant';
    }

    /**
     * Get student by ID
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public static function getById(int $id): array|false
    {
        try {
            $db = self::getConnection();

            $stmt = $db->prepare(
                'SELECT id, email, nom, prenom, numetu, type_etudiant, created_at, last_connexion
                 FROM etudiants WHERE id = :id'
            );
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return is_array($result) ? $result : false;
        } catch (PDOException $e) {
            error_log('getById student error: ' . $e->getMessage());
            return false;
        }
    }
}

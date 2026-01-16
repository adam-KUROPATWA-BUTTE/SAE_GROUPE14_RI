<?php

// phpcs:disable Generic.Files.LineLength

namespace Model\User;

use PDO;
use PDOException;
use Exception;

/**
 * UserAdmin
 *
 * Handles admin authentication, registration, and management.
 */
class UserAdmin
{
    /**
     * Get the PDO connection
     */
    private static function getConnection(): PDO
    {
        if (!class_exists('\Database')) {
            throw new \RuntimeException('Database class not found');
        }

        return \Database::getInstance()->getConnection();
    }

    /**
     * Admin login
     *
     * @param string $email
     * @param string $password
     * @return array<string, bool|string> Always returns keys: success (bool), role (string, optional)
     */
    public static function login(string $email, string $password): array
    {
        try {
            $db = self::getConnection();

            $stmt = $db->prepare(
                'SELECT id, email, password, nom, prenom, is_super_admin 
                FROM admins 
                WHERE email = :email'
            );
            $stmt->execute([':email' => $email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($user)) {
                return ['success' => false];
            }

            $hashedPassword = $user['password'] ?? '';
            if ($hashedPassword && password_verify($password, (string)$hashedPassword)) {
                $_SESSION['user_role'] = 'admin';
                $_SESSION['admin_id'] = (int)($user['id'] ?? 0);
                $_SESSION['admin_nom'] = (string)($user['nom'] ?? '');
                $_SESSION['admin_prenom'] = (string)($user['prenom'] ?? '');
                $_SESSION['is_super_admin'] = !empty($user['is_super_admin']);

                $updateStmt = $db->prepare('UPDATE admins SET last_login = NOW() WHERE id = :id');
                $updateStmt->execute([':id' => (int)($user['id'] ?? 0)]);

                return ['success' => true, 'role' => 'admin'];
            }

            return ['success' => false];
        } catch (PDOException $e) {
            error_log('Admin login error: ' . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Register a new admin
     *
     * @param string $email
     * @param string $password
     * @param string $nom
     * @param string $prenom
     * @param int $requestingAdminId
     * @return bool
     */
    public static function register(
        string $email,
        string $password,
        string $nom,
        string $prenom,
        int $requestingAdminId
    ): bool {
        try {
            $db = self::getConnection();

            $stmt = $db->prepare('SELECT id FROM admins WHERE id = :id');
            $stmt->execute([':id' => $requestingAdminId]);
            if (!$stmt->fetch()) {
                return false;
            }

            $stmt = $db->prepare(
                'SELECT id FROM admins WHERE email = :email 
                 UNION 
                 SELECT id FROM etudiants WHERE email = :email'
            );
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare(
                'INSERT INTO admins (email, password, nom, prenom, is_super_admin) 
                VALUES (:email, :password, :nom, :prenom, 0)'
            );

            return $stmt->execute([
                ':email' => $email,
                ':password' => $hashedPassword,
                ':nom' => $nom,
                ':prenom' => $prenom
            ]);
        } catch (PDOException $e) {
            error_log('Admin registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin logout
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
            error_log('Admin logout error: ' . $e->getMessage());
            return false;
        }
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function isSuperAdmin(): bool
    {
        return self::isAdmin() && !empty($_SESSION['is_super_admin']);
    }

    /**
     * Get admin by ID
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public static function getById(int $id): array|false
    {
        try {
            $db = self::getConnection();
            $stmt = $db->prepare(
                'SELECT id, email, nom, prenom, is_super_admin, created_at 
                 FROM admins 
                 WHERE id = :id'
            );
            $stmt->execute([':id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($result)) {
                return false;
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Get admin by ID error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all admins
     *
     * @param int $requestingAdminId
     * @return array<int, array<string, mixed>>|false
     */
    public static function getAll(int $requestingAdminId): array|false
    {
        try {
            if (!self::isAdmin()) {
                return false;
            }

            $db = self::getConnection();

            $stmt = $db->prepare(
                'SELECT id, email, nom, prenom, is_super_admin, created_at, last_login 
                 FROM admins 
                 ORDER BY created_at DESC'
            );
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!is_array($result)) {
                return false;
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Get all admins error: ' . $e->getMessage());
            return false;
        }
    }
}

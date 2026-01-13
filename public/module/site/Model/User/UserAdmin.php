<?php

namespace Model\User;

use PDO;
use PDOException;

/**
 * Class UserAdmin
 *
 * Model responsible for managing administrator accounts.
 * Handles authentication, registration, session management, and retrieval of admin data.
 */
class UserAdmin
{
    /**
     * Authenticates an administrator.
     *
     * Verifies credentials against the database. On success, initializes the session
     * with admin details and updates the last login timestamp.
     *
     * @param string $email    Admin email.
     * @param string $password Admin password.
     * @return array{success: bool, role?: string} Login result (success status and role).
     */
    public static function login(string $email, string $password): array
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, password, nom, prenom, is_super_admin FROM admins WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify user exists and password is correct
            // Cast password to string to ensure type safety for password_verify
            if ($user && is_array($user) && password_verify($password, (string)($user['password'] ?? ''))) {
                $_SESSION['user_role'] = 'admin';
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_nom'] = $user['nom'];
                $_SESSION['admin_prenom'] = $user['prenom'];
                $_SESSION['is_super_admin'] = $user['is_super_admin'];

                // Update last login timestamp
                $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id")
                   ->execute(['id' => $user['id']]);

                return ['success' => true, 'role' => 'admin'];
            }

            return ['success' => false];
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Registers a new administrator.
     *
     * This action is restricted to existing administrators. It checks for email uniqueness
     * across both admins and students tables before creating the account.
     *
     * @param string $email             New admin email.
     * @param string $password          New admin password.
     * @param string $nom               Last name.
     * @param string $prenom            First name.
     * @param int    $requestingAdminId ID of the admin performing the registration.
     * @return bool True on success, false on failure.
     */
    public static function register(string $email, string $password, string $nom, string $prenom, int $requestingAdminId): bool
    {
        try {
            $db = \Database::getInstance()->getConnection();

            // 1. Verify requester exists and is an admin
            $sql = "SELECT id FROM admins WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $requestingAdminId]);

            if (!$stmt->fetch()) {
                error_log("Security Warning: Attempt to create admin by invalid ID: $requestingAdminId");
                return false;
            }

            // 2. Check if email is already taken (in admins or students tables)
            $sql = "SELECT id FROM admins WHERE email = :email 
                    UNION 
                    SELECT id FROM etudiants WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                return false; // Email already exists
            }

            // 3. Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admins (email, password, nom, prenom, is_super_admin, created_at) 
                    VALUES (:email, :password, :nom, :prenom, 0, NOW())";
            $stmt = $db->prepare($sql);

            return $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'nom' => $nom,
                'prenom' => $prenom
            ]);
        } catch (PDOException $e) {
            error_log("Admin registration error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Initiates a password reset process.
     *
     * Currently a placeholder implementation.
     *
     * @param string $email The email address requesting the reset.
     * @return void
     */
    public static function resetPassword(string $email): void
    {
        // TODO: Implement token generation and email sending logic
        error_log("Password reset requested for Admin: " . $email);
    }

    /**
     * Logs out the administrator.
     *
     * Destroys the session and clears the session cookie.
     *
     * @return bool True on success, false on failure.
     */
    public static function logout(): bool
    {
        try {
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                // Fix: session_name() can technically return false, casting to string satisfies PHPStan
                setcookie(
                    (string)session_name(),
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
            error_log("Admin logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if the currently logged-in user has the 'admin' role.
     *
     * @return bool True if admin, false otherwise.
     */
    public static function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Checks if the currently logged-in admin has super-admin privileges.
     *
     * @return bool True if super admin, false otherwise.
     */
    public static function isSuperAdmin(): bool
    {
        return self::isAdmin() && (!empty($_SESSION['is_super_admin']));
    }

    /**
     * Retrieves admin information by ID.
     *
     * @param int $id Admin ID.
     * @return array<string, mixed>|null Admin data or null on failure/not found.
     */
    public static function getById(int $id): ?array
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, nom, prenom, is_super_admin, created_at 
                    FROM admins WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        } catch (PDOException $e) {
            error_log("Get admin by ID error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieves a list of all administrators.
     * Restricted to authenticated admins.
     *
     * @return array<int, array<string, mixed>> List of admins. Returns empty array on failure or access denied.
     */
    public static function getAll(): array
    {
        try {
            // Verify requester is logged in as admin
            if (!self::isAdmin()) {
                return [];
            }

            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, nom, prenom, is_super_admin, created_at, last_login 
                    FROM admins ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($results) ? $results : [];
        } catch (PDOException $e) {
            error_log("Get all admins error: " . $e->getMessage());
            return [];
        }
    }
}
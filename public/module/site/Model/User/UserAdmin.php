<?php

namespace Model\User;

class UserAdmin
{
    /**
     * Admin login
     *
     * Verifies admin credentials. On success, sets session variables:
     * - user_role
     * - admin_id
     * - admin_nom
     * - admin_prenom
     * - is_super_admin
     *
     * Updates last_login timestamp in the database.
     *
     * @param string $email Admin email
     * @param string $password Admin password
     * @return array ['success' => bool, 'role' => 'admin' if success]
     */
    public static function login($email, $password)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, password, nom, prenom, is_super_admin FROM admins WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
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
        } catch (\PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Register a new admin (ONLY by an existing admin)
     *
     * Checks that the requesting user is an admin, verifies email uniqueness,
     * hashes the password, and inserts the new admin.
     *
     * @param string $email Admin email
     * @param string $password Admin password
     * @param string $nom Admin last name
     * @param string $prenom Admin first name
     * @param int $requestingAdminId ID of the admin making the request
     * @return bool True on success, false on failure
     */
    public static function register($email, $password, $nom, $prenom, $requestingAdminId)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            // Verify requester is admin
            $sql = "SELECT id FROM admins WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $requestingAdminId]);

            if (!$stmt->fetch()) {
                error_log("Attempt to create admin by non-admin (ID: $requestingAdminId)");
                return false;
            }

            // Check if email already exists in admins or students
            $sql = "SELECT id FROM admins WHERE email = :email 
                    UNION 
                    SELECT id FROM etudiants WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                return false; // Email already used
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admins (email, password, nom, prenom, is_super_admin) 
                    VALUES (:email, :password, :nom, :prenom, 0)";
            $stmt = $db->prepare($sql);

            return $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'nom' => $nom,
                'prenom' => $prenom
            ]);
        } catch (\PDOException $e) {
            error_log("Admin registration error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin logout
     *
     * Clears session data and cookies.
     *
     * @return bool True on success, false on failure
     */
    public static function logout(): bool
    {
        try {
            $_SESSION = array();

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
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
     * Check if the logged-in user is an admin
     *
     * @return bool True if admin, false otherwise
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Check if the logged-in admin is a super admin
     *
     * @return bool True if super admin, false otherwise
     */
    public static function isSuperAdmin(): bool
    {
        return self::isAdmin() && ($_SESSION['is_super_admin'] ?? false);
    }

    /**
     * Get admin information by ID
     *
     * @param int $id Admin ID
     * @return array|false Admin data or false on failure
     */
    public static function getById($id)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, nom, prenom, is_super_admin, created_at 
                    FROM admins WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("Get admin by ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * List all admins (restricted to admins)
     *
     * @param int $requestingAdminId ID of the admin making the request
     * @return array|false List of admins or false on failure/not authorized
     */
    public static function getAll($requestingAdminId)
    {
        try {
            // Verify requester is admin
            if (!self::isAdmin()) {
                return false;
            }

            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, nom, prenom, is_super_admin, created_at, last_login 
                    FROM admins ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Get all admins error: " . $e->getMessage());
            return false;
        }
    }
}

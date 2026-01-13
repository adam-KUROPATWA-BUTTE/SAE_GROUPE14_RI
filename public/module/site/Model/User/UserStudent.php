<?php

// phpcs:disable Generic.Files.LineLength

namespace Model\User;

class UserStudent
{
    /**
     * Student login (via student number)
     *
     * Verifies credentials and sets session variables on success:
     * - user_role
     * - etudiant_id
     * - etudiant_nom
     * - etudiant_prenom
     * - numetu
     * - type_etudiant
     *
     * Updates last_connexion timestamp in the database.
     *
     * @param string $numetu Student number
     * @param string $password Student password
     * @return array ['success' => bool, 'role' => 'etudiant' if success]
     */
    public static function login($numetu, $password)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, numetu, nom, prenom, password, type_etudiant 
                    FROM etudiants WHERE numetu = :numetu";
            $stmt = $db->prepare($sql);
            $stmt->execute(['numetu' => $numetu]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_role'] = 'etudiant';
                $_SESSION['etudiant_id'] = $user['id'];
                $_SESSION['etudiant_nom'] = $user['nom'];
                $_SESSION['etudiant_prenom'] = $user['prenom'];
                $_SESSION['numetu'] = $user['numetu'];
                $_SESSION['type_etudiant'] = $user['type_etudiant'];

                // Update last login timestamp
                $db->prepare("UPDATE etudiants SET last_connexion = NOW() WHERE id = :id")
                   ->execute(['id' => $user['id']]);

                return ['success' => true, 'role' => 'etudiant'];
            }

            return ['success' => false];
        } catch (\PDOException $e) {
            error_log("Student login error: " . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Student registration (public)
     *
     * Checks email uniqueness and validates student type ('entrant' or 'sortant').
     * Password is hashed before storing.
     *
     * @param string $email Student email
     * @param string $password Student password
     * @param string $nom Student last name
     * @param string $prenom Student first name
     * @param string $typeEtudiant 'entrant' or 'sortant'
     * @return bool True on success, false on failure
     */
    public static function register($email, $password, $nom, $prenom, $typeEtudiant)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            // Check if email already exists in admins or students
            $sql = "SELECT id FROM admins WHERE email = :email 
                    UNION 
                    SELECT id FROM etudiants WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                return false; // Email already used
            }

            // Validate student type
            if (!in_array($typeEtudiant, ['entrant', 'sortant'])) {
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO etudiants (email, password, nom, prenom, type_etudiant) 
                    VALUES (:email, :password, :nom, :prenom, :type_etudiant)";
            $stmt = $db->prepare($sql);

            $result = $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'nom' => $nom,
                'prenom' => $prenom,
                'type_etudiant' => $typeEtudiant
            ]);

            // Do not create folder automatically; student must create it after login
            return $result;
        } catch (\PDOException $e) {
            error_log("Student registration error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a student has a folder
     *
     * @param int $etudiantId Student ID
     * @return bool True if folder exists, false otherwise
     */
    public static function checkDossierExists($etudiantId)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT COUNT(*) as count FROM dossiers WHERE etudiant_id = :etudiant_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['etudiant_id' => $etudiantId]);
            $result = $stmt->fetch();

            return $result['count'] > 0;
        } catch (\PDOException $e) {
            error_log("checkDossierExists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve a student's folder
     *
     * @param int $etudiantId Student ID
     * @return array|false Folder data or false on failure
     */
    public static function getDossier($etudiantId)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT * FROM dossiers WHERE etudiant_id = :etudiant_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['etudiant_id' => $etudiantId]);

            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("getDossier error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a folder for a student (manually after login)
     *
     * @param int $etudiantId Student ID
     * @return bool True on success, false if folder exists or on error
     */
    public static function createDossier($etudiantId)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            // Check if folder already exists
            if (self::checkDossierExists($etudiantId)) {
                return false;
            }

            $sql = "INSERT INTO dossiers (etudiant_id, statut, date_creation) 
                    VALUES (:etudiant_id, 'en_cours', NOW())";
            $stmt = $db->prepare($sql);

            return $stmt->execute(['etudiant_id' => $etudiantId]);
        } catch (\PDOException $e) {
            error_log("createDossier error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Student logout
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
            error_log("Student logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if the logged-in user is a student
     *
     * @return bool True if student, false otherwise
     */
    public static function isStudent(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'etudiant';
    }

    /**
     * Get student information by ID
     *
     * @param int $id Student ID
     * @return array|false Student data or false on failure
     */
    public static function getById($id)
    {
        try {
            $db = \Database::getInstance()->getConnection();

            $sql = "SELECT id, email, nom, prenom, numetu, type_etudiant, created_at, last_connexion 
                    FROM etudiants WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("getById student error: " . $e->getMessage());
            return false;
        }
    }
}

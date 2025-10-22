<?php
namespace Model;

require_once ROOT_PATH . '/Database.php';

class UserAdmin
{
    /**
     * Connexion admin
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
                
                // Mettre à jour la date de dernière connexion
                $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id")
                   ->execute(['id' => $user['id']]);

                return ['success' => true, 'role' => 'admin'];
            }

            return ['success' => false];

        } catch (\PDOException $e) {
            error_log("Erreur login admin : " . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Créer un admin (UNIQUEMENT par un admin existant)
     */
    public static function register($email, $password, $nom, $prenom, $requestingAdminId)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            // Vérifier que celui qui fait la demande est bien admin
            $sql = "SELECT id FROM admins WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $requestingAdminId]);
            
            if (!$stmt->fetch()) {
                error_log("Tentative de création d'admin par un non-admin (ID: $requestingAdminId)");
                return false;
            }
            
            // Vérifier si l'email existe déjà
            $sql = "SELECT id FROM admins WHERE email = :email 
                    UNION 
                    SELECT id FROM etudiants WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            if ($stmt->fetch()) {
                return false; // Email déjà utilisé
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
            error_log("Erreur register admin : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialisation du mot de passe admin
     */
    public static function resetPassword($email)
    {
        try {
            $db = \Database::getInstance()->getConnection();
        
            $sql = "SELECT id FROM admins WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
        
            if (!$user) {
                return true; // Ne pas révéler si l'email existe
            }
        
            $token = bin2hex(random_bytes(32));
        
            // Supprimer les anciens tokens
            $sql = "DELETE FROM reset_tokens WHERE admin_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user['id']]);
            
            $sql = "INSERT INTO reset_tokens (admin_id, etudiant_id, token, expires_at)
                    VALUES (:user_id, NULL, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $user['id'],
                'token' => $token
            ]);
        
            if ($result) {
                require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'EmailService.php';
                \Service\EmailService::sendPasswordReset($email, $token);
            }
        
            return true;
        
        } catch (\PDOException $e) {
            error_log("Erreur resetPassword admin : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le mot de passe avec token
     */
    public static function updatePasswordWithToken($token, $newPassword)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $sql = "SELECT admin_id FROM reset_tokens
                    WHERE token = :token AND expires_at > NOW() AND admin_id IS NOT NULL";
            $stmt = $db->prepare($sql);
            $stmt->execute(['token' => $token]);
            $reset = $stmt->fetch();
            
            if (!$reset) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE admins SET password = :password WHERE id = :id";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'password' => $hashedPassword,
                'id' => $reset['admin_id']
            ]);
            
            if ($result) {
                $sql = "DELETE FROM reset_tokens WHERE token = :token";
                $stmt = $db->prepare($sql);
                $stmt->execute(['token' => $token]);
            }
            
            return $result;
            
        } catch (\PDOException $e) {
            error_log("Erreur updatePasswordWithToken admin : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Déconnexion
     */
    public static function logout(): bool
    {   
        try {
            $_SESSION = array();
            
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
            return true;
            
        } catch (\Exception $e) {
            error_log("Erreur logout admin : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si l'utilisateur connecté est admin
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Vérifier si l'utilisateur connecté est super admin
     */
    public static function isSuperAdmin(): bool
    {
        return self::isAdmin() && ($_SESSION['is_super_admin'] ?? false);
    }

    /**
     * Récupérer les informations d'un admin
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
            error_log("Erreur getById admin : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lister tous les admins (réservé aux admins)
     */
    public static function getAll($requestingAdminId)
    {
        try {
            // Vérifier que celui qui demande est admin
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
            error_log("Erreur getAll admins : " . $e->getMessage());
            return false;
        }
    }
}
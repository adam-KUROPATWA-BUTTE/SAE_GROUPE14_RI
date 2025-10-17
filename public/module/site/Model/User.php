<?php
namespace Model;

require_once ROOT_PATH . '/Database.php';

class User
{
    public static function login($email, $password)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $sql = "SELECT * FROM admins WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Mise à jour du dernier login
                $updateSql = "UPDATE admins SET last_login = NOW() WHERE id = :id";
                $updateStmt = $db->prepare($updateSql);
                $updateStmt->execute(['id' => $admin['id']]);
                
                // Stocker en session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_nom'] = $admin['nom'] ?? '';
                $_SESSION['admin_prenom'] = $admin['prenom'] ?? '';
                
                return true;
            }
            
            return false;
            
        } catch (\PDOException $e) {
            error_log("Erreur login : " . $e->getMessage());
            return false;
        }
    }

    public static function register($email, $password, $nom = '', $prenom = '')
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            // Vérifier si l'email existe déjà
            $sql = "SELECT id FROM admins WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            if ($stmt->fetch()) {
                return false;
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO admins (email, password, nom, prenom) VALUES (:email, :password, :nom, :prenom)";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'nom' => $nom,
                'prenom' => $prenom
            ]);
            
        } catch (\PDOException $e) {
            error_log("Erreur register : " . $e->getMessage());
            return false;
        }
    }

    public static function resetPassword($email)
    {
        try {
            $db = \Database::getInstance()->getConnection();
        
            $sql = "SELECT id FROM admins WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch();
        
            if (!$admin) {
                return true;
            }
        
            $token = bin2hex(random_bytes(32));
        
            // Supprimer les anciens tokens
            $sql = "DELETE FROM reset_tokens WHERE admin_id = :admin_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['admin_id' => $admin['id']]);

            $sql = "INSERT INTO reset_tokens (admin_id, token, expires_at)
                    VALUES (:admin_id, :token, CONVERT_TZ(DATE_ADD(NOW(), INTERVAL 1 HOUR), '+00:00', '+02:00'))";
            $stmt = $db->prepare($sql);
        
            $result = $stmt->execute([
                'admin_id' => $admin['id'],
                'token' => $token
            ]);
        
            if ($result) {
                require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'EmailService.php';
                $emailSent = \Service\EmailService::sendPasswordReset($email, $token);
                error_log("Email de reset envoyé à $email - Résultat: " . ($emailSent ? 'SUCCESS' : 'FAILED'));
            }
        
            return true;
        
        } catch (\PDOException $e) {
            error_log("Erreur resetPassword : " . $e->getMessage());
            return false;
        }
    }
    
    public static function updatePasswordWithToken($token, $newPassword)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $sql = "SELECT admin_id FROM reset_tokens
                    WHERE token = :token AND expires_at > NOW()";
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
                
                return true;
            }
            
            return false;
            
        } catch (\PDOException $e) {
            error_log("Erreur updatePasswordWithToken : " . $e->getMessage());
            return false;
        }
    }

    public static function logout(): bool
    {   
        try {
            // Optionnel : Enregistrer la déconnexion dans les logs
            if (isset($_SESSION['admin_id'])) {
                $db = \Database::getInstance()->getConnection();
                $sql = "UPDATE admins SET last_logout = NOW() WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['id' => $_SESSION['admin_id']]);
            }
            
            // Nettoyer la session
            $_SESSION = array();
            
            // Détruire le cookie de session si utilisé
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Détruire la session
            session_destroy();
            
            return true;
            
        } catch (\PDOException $e) {
            error_log("Erreur logout : " . $e->getMessage());
            return false;
        }
    }
}
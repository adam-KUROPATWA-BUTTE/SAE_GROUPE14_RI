<?php
namespace Model;

require_once '/' . DIRECTORY_SEPARATOR . 'modules/blog/config/Database.php';

class User
{
    public static function login($email, $password)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
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
            
        } catch (PDOException $e) {
            error_log("Erreur login : " . $e->getMessage());
            return false;
        }
    }

    public static function register($email, $password, $nom = '', $prenom = '')
    {
        try {
            $db = Database::getInstance()->getConnection();
            
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
            
        } catch (PDOException $e) {
            error_log("Erreur register : " . $e->getMessage());
            return false;
        }
    }

    public static function resetPassword($email)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT id FROM admins WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                return true; // Retourner true même si l'email n'existe pas (sécurité)
            }
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $sql = "INSERT INTO reset_tokens (admin_id, token, expires_at)
                    VALUES (:admin_id, :token, :expires_at)
                    ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at";
            $stmt = $db->prepare($sql);
            
            $result = $stmt->execute([
                'admin_id' => $admin['id'],
                'token' => $token,
                'expires_at' => $expires
            ]);
            
            if ($result) {
                // TODO: Envoyer l'email avec le lien
                // $resetLink = "http://votre-site.com/index.php?page=reset&token=$token";
                // mail($email, "Réinitialisation mot de passe", "Cliquez ici: $resetLink");
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur resetPassword : " . $e->getMessage());
            return false;
        }
    }

    public static function updatePasswordWithToken($token, $newPassword)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
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
            
        } catch (PDOException $e) {
            error_log("Erreur updatePasswordWithToken : " . $e->getMessage());
            return false;
        }
    }
}
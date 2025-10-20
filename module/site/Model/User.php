<?php
namespace Model;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Database.php';

class User
{
    /**
     * Connexion universelle (admin ou étudiant (type 1 et 2))
     */
    public static function login($identifier, $password)
{
    try {
        $db = \Database::getInstance()->getConnection();

        // Vérifier dans les admins
        $sqlAdmin = "SELECT id, email, password, nom, prenom, 'admin' AS user_type FROM admins WHERE email = :identifier";
        $stmt = $db->prepare($sqlAdmin);
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_role'] = 'admin';
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nom'] = $user['nom'];
            $_SESSION['admin_prenom'] = $user['prenom'];
            return ['success' => true, 'role' => 'admin'];
        }

        // Vérifier dans les étudiants (numéro étudiant)
        $sqlEtudiant = "SELECT id, numetu, nom, prenom, password, type_etudiant, 'etudiant' AS user_type 
                        FROM etudiants WHERE numetu = :identifier";
        $stmt = $db->prepare($sqlEtudiant);
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_role'] = 'etudiant';
            $_SESSION['etudiant_id'] = $user['id'];
            $_SESSION['etudiant_nom'] = $user['nom'];
            $_SESSION['etudiant_prenom'] = $user['prenom'];
            $_SESSION['numetu'] = $user['numetu'];
            $_SESSION['type_etudiant'] = $user['type_etudiant'];

            // Mettre à jour la date de dernière connexion
            $db->prepare("UPDATE etudiants SET last_connexion = NOW() WHERE id = :id")
               ->execute(['id' => $user['id']]);

            return ['success' => true, 'role' => 'etudiant'];
        }

        return ['success' => false];

    } catch (\PDOException $e) {
        error_log("Erreur login : " . $e->getMessage());
        return ['success' => false];
    }
}


    /**
     * Vérifier si un étudiant a un dossier
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
            error_log("Erreur checkDossierExists : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer le dossier d'un étudiant
     */
    public static function getDossier($etudiantId)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $sql = "SELECT COUNT(*) as count FROM dossiers WHERE etudiant_id = :etudiant_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['etudiant_id' => $etudiantId]);
            
            return $stmt->fetch();
            
        } catch (\PDOException $e) {
            error_log("Erreur getDossier : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inscription étudiant (publique)
     */
    public static function registerEtudiant($email, $password, $nom, $prenom, $typeEtudiant)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            // Vérifier si l'email existe déjà (admin ou étudiant)
            $sql = "SELECT id FROM admins WHERE email = :email 
                    UNION 
                    SELECT id FROM etudiants WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            if ($stmt->fetch()) {
                return false; // Email déjà utilisé
            }
            
            // Valider le type d'étudiant
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
            
            // NE PAS créer automatiquement le dossier
            // L'étudiant devra le créer après connexion
            
            return $result;
            
        } catch (\PDOException $e) {
            error_log("Erreur register étudiant : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Créer un admin (UNIQUEMENT par un admin existant)
     */
    public static function registerAdmin($email, $password, $nom, $prenom, $requestingAdminId)
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
     * Créer un dossier pour un étudiant (appelé manuellement après connexion)
     */
    public static function createDossier($etudiantId)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            // Vérifier que le dossier n'existe pas déjà
            if (self::checkDossierExists($etudiantId)) {
                return false;
            }
            
            $sql = "INSERT INTO dossiers (etudiant_id, statut, date_creation) 
                    VALUES (:etudiant_id, 'en_cours', NOW())";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute(['etudiant_id' => $etudiantId]);
            
        } catch (\PDOException $e) {
            error_log("Erreur création dossier : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialisation du mot de passe (universel)
     */
    public static function resetPassword($email)
    {
        try {
            $db = \Database::getInstance()->getConnection();
        
            // Chercher dans admins et étudiants
            $sql = "SELECT id, 'admin' as user_type FROM admins WHERE email = :email 
                    UNION 
                    SELECT id, 'etudiant' as user_type FROM etudiants WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
        
            if (!$user) {
                return true; // Ne pas révéler si l'email existe
            }
        
            $token = bin2hex(random_bytes(32));
        
            // Supprimer les anciens tokens
            if ($user['user_type'] === 'admin') {
                $sql = "DELETE FROM reset_tokens WHERE admin_id = :user_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $user['id']]);
                
                $sql = "INSERT INTO reset_tokens (admin_id, etudiant_id, token, expires_at)
                        VALUES (:user_id, NULL, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
            } else {
                $sql = "DELETE FROM reset_tokens WHERE etudiant_id = :user_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $user['id']]);
                
                $sql = "INSERT INTO reset_tokens (admin_id, etudiant_id, token, expires_at)
                        VALUES (NULL, :user_id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
            }
            
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
            error_log("Erreur resetPassword : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le mot de passe avec token (universel)
     */
    public static function updatePasswordWithToken($token, $newPassword)
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $sql = "SELECT admin_id, etudiant_id FROM reset_tokens
                    WHERE token = :token AND expires_at > NOW()";
            $stmt = $db->prepare($sql);
            $stmt->execute(['token' => $token]);
            $reset = $stmt->fetch();
            
            if (!$reset) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Déterminer quelle table mettre à jour
            if ($reset['admin_id']) {
                $sql = "UPDATE admins SET password = :password WHERE id = :id";
                $userId = $reset['admin_id'];
            } else {
                $sql = "UPDATE etudiants SET password = :password WHERE id = :id";
                $userId = $reset['etudiant_id'];
            }
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'password' => $hashedPassword,
                'id' => $userId
            ]);
            
            if ($result) {
                $sql = "DELETE FROM reset_tokens WHERE token = :token";
                $stmt = $db->prepare($sql);
                $stmt->execute(['token' => $token]);
            }
            
            return $result;
            
        } catch (\PDOException $e) {
            error_log("Erreur updatePasswordWithToken : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Déconnexion universelle
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
            error_log("Erreur logout : " . $e->getMessage());
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
     * Récupérer les informations d'un utilisateur
     */
    public static function getById($id, $type = 'etudiant')
    {
        try {
            $db = \Database::getInstance()->getConnection();
            
            if ($type === 'admin') {
                $sql = "SELECT id, email, nom, prenom, is_super_admin, created_at 
                        FROM admins WHERE id = :id";
            } else {
                $sql = "SELECT id, email, nom, prenom, type_etudiant, created_at 
                        FROM etudiants WHERE id = :id";
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
            
        } catch (\PDOException $e) {
            error_log("Erreur getById : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lister tous les admins (réservé aux admins)
     */
    public static function getAllAdmins($requestingAdminId)
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
            error_log("Erreur getAllAdmins : " . $e->getMessage());
            return false;
        }
    }
}
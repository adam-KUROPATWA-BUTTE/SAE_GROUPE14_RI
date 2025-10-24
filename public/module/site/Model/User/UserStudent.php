<?php
namespace Model;

require_once ROOT_PATH . '/Database.php';

class UserStudent
{
    /**
     * Connexion étudiant (via numéro étudiant)
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

                // Mettre à jour la date de dernière connexion
                $db->prepare("UPDATE etudiants SET last_connexion = NOW() WHERE id = :id")
                   ->execute(['id' => $user['id']]);

                return ['success' => true, 'role' => 'etudiant'];
            }

            return ['success' => false];

        } catch (\PDOException $e) {
            error_log("Erreur login étudiant : " . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Inscription étudiant (publique)
     */
    public static function register($email, $password, $nom, $prenom, $typeEtudiant)
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
            
            $sql = "SELECT * FROM dossiers WHERE etudiant_id = :etudiant_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['etudiant_id' => $etudiantId]);
            
            return $stmt->fetch();
            
        } catch (\PDOException $e) {
            error_log("Erreur getDossier : " . $e->getMessage());
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
            error_log("Erreur logout étudiant : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si l'utilisateur connecté est étudiant
     */
    public static function isStudent(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'etudiant';
    }

    /**
     * Récupérer les informations d'un étudiant
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
            error_log("Erreur getById étudiant : " . $e->getMessage());
            return false;
        }
    }
}
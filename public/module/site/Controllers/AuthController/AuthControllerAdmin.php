<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\User;

class AuthController implements ControllerInterface
{
    public function control()
    {
        $page = $_GET['page'] ?? 'login';
        
        // Gestion de la déconnexion
        if ($page === 'logout') {
            $this->handleLogout();
            return;
        }
        
        // Si déjà connecté, rediriger
        if (isset($_SESSION['user_role'])) {
            if ($_SESSION['user_role'] === 'admin') {
                header('Location: index.php?page=dashboard');
            } else {
                // Vérifier si l'étudiant a un dossier
                $hasDossier = User::checkDossierExists($_SESSION['etudiant_id']);
                if ($hasDossier) {
                    header('Location: index.php?page=dashboard_etudiant');
                } else {
                    header('Location: index.php?page=create_dossier');
                }
            }
            exit;
        }

        $message = '';
        $isLogin = ($page === 'login');
        $isReset = ($page === 'reset');
        $isTokenReset = isset($_GET['token']) && !empty($_GET['token']);
        $token = $_GET['token'] ?? '';
        $isRegisterEtudiant = ($page === 'register_etudiant');
        $isRegisterAdmin = ($page === 'register_admin');

        // Traitement des formulaires POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
        
            switch ($action) {
                case 'login':
                        $identifier = $_POST['identifier'] ?? '';
                        $password = $_POST['password'] ?? '';

                        $result = User::login($identifier, $password);

                    
                    if ($result['success']) {
                        // Rediriger selon le rôle et la présence du dossier
                        if ($result['role'] === 'admin') {
                            header('Location: index.php?page=dashboard');
                        } else {
                            if ($result['has_dossier']) {
                                header('Location: index.php?page=dashboard_etudiant');
                            } else {
                                header('Location: index.php?page=create_dossier');
                            }
                        }
                        exit();
                    } else {
                        $message = "Identifiant ou mot de passe incorrect.";
                    }
                    break;

                case 'register_etudiant':
                    $nom = $_POST['nom'] ?? '';
                    $prenom = $_POST['prenom'] ?? '';
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';
                    $typeEtudiant = $_POST['type_etudiant'] ?? '';

                    // Validations
                    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($typeEtudiant)) {
                        $message = "Tous les champs sont obligatoires.";
                        break;
                    }

                    if (!in_array($typeEtudiant, ['entrant', 'sortant'])) {
                        $message = "Type d'étudiant invalide.";
                        break;
                    }

                    if (strlen($password) < 8) {
                        $message = "Le mot de passe doit contenir au moins 8 caractères.";
                        break;
                    }

                    if (User::registerEtudiant($email, $password, $nom, $prenom, $typeEtudiant)) {
                        $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter. Vous devrez créer votre dossier après connexion.";
                        $isLogin = true;
                        $isRegisterEtudiant = false;
                        $page = 'login';
                    } else {
                        $message = "Erreur lors de la création du compte. L'email existe peut-être déjà.";
                    }
                    break;

                case 'register_admin':
                    // Vérifier que l'utilisateur est bien admin
                    if (!User::isAdmin()) {
                        $message = "Accès non autorisé.";
                        header('Location: index.php?page=login');
                        exit;
                    }

                    $nom = $_POST['nom'] ?? '';
                    $prenom = $_POST['prenom'] ?? '';
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';

                    // Validations
                    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                        $message = "Tous les champs sont obligatoires.";
                        break;
                    }

                    if (strlen($password) < 8) {
                        $message = "Le mot de passe doit contenir au moins 8 caractères.";
                        break;
                    }

                    $requestingAdminId = $_SESSION['admin_id'];
                    
                    if (User::registerAdmin($email, $password, $nom, $prenom, $requestingAdminId)) {
                        $message = "Nouvel administrateur créé avec succès !";
                    } else {
                        $message = "Erreur lors de la création de l'administrateur. L'email existe peut-être déjà.";
                    }
                    break;

                case 'reset':
                    $email = $_POST['email'] ?? '';
                    
                    if (User::resetPassword($email)) {
                        $message = "Si cet email existe, un lien de réinitialisation a été envoyé.";
                    } else {
                        $message = "Erreur lors de l'envoi du lien.";
                    }
                    break;

                case 'token_reset':
                    $token = $_POST['token'] ?? '';
                    $newPassword = $_POST['new_password'] ?? '';
                    
                    if (strlen($newPassword) < 8) {
                        $message = "Le mot de passe doit contenir au moins 8 caractères.";
                        break;
                    }

                    if (User::updatePasswordWithToken($token, $newPassword)) {
                        $message = "Mot de passe modifié avec succès ! Vous pouvez vous connecter.";
                        $isLogin = true;
                        $isReset = false;
                        $isTokenReset = false;
                        $page = 'login';
                    } else {
                        $message = "Erreur : token invalide ou expiré.";
                    }
                    break;

                default:
                    $message = "Action non reconnue.";
            }
        }
        
        // Charger la vue appropriée
        if ($isRegisterAdmin) {
            if (!User::isAdmin()) {
                header('Location: index.php?page=login');
                exit;
            }
            require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'register_admin.php';
        } else {
            require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Login.php';
        }
    }

    /**
     * Gère la déconnexion de l'utilisateur
     */
    private function handleLogout(): void
    {
        User::logout();
        header('Location: index.php?page=login');
        exit;
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['login', 'register_etudiant', 'register_admin', 'reset', 'logout']);
    }
}
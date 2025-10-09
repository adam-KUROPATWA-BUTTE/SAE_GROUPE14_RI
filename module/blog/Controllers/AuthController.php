<?php
namespace Controllers\Blog;

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

        // Si déjà connecté, rediriger vers le dashboard
        if (isset($_SESSION['admin_id'])) {
            header('Location: index.php?page=dashboard');
            exit;
        }

        $message = '';
        
        $isLogin = ($page === 'login');
        $isReset = ($page === 'reset');
        $isTokenReset = isset($_GET['token']) && !empty($_GET['token']);
        $token = $_GET['token'] ?? '';

        // Traitement des formulaires POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
        
            switch ($action) {
                case 'login':
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';

                    if (User::login($email, $password)) {
                        header('Location: index.php?page=dashboard');
                        exit();
                    } else {
                        $message = "Email ou mot de passe incorrect.";
                    }
                    break;

                case 'register':
                    $nom = $_POST['nom'] ?? '';
                    $prenom = $_POST['prenom'] ?? '';
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';

                    if (User::register($email, $password, $nom, $prenom)) {
                        $message = "Compte créé avec succès ! Vous pouvez vous connecter.";
                        $isLogin = true;
                        $page = 'login';
                    } else {
                        $message = "Erreur lors de la création du compte. L'email existe peut-être déjà.";
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

        require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'login.php';
    }

    /**
     * Gère la déconnexion de l'utilisateur
     */
    private function handleLogout(): void
    {
        // Détruire toutes les variables de session
        $_SESSION = array();

        // Si on veut détruire complètement la session, effacer aussi le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finalement, détruire la session
        session_destroy();

        // Rediriger vers la page de connexion
        header('Location: index.php?page=login');
        exit;
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['login', 'register', 'reset', 'logout']);
    }
}
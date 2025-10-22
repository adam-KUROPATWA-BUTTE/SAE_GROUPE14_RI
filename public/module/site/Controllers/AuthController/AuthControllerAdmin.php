<?php
namespace Controllers\site\AuthController;

use Controllers\ControllerInterface;
use Controllers\Auth_Guard;
use Model\User\UserAdmin;
use Config\UrlHelper;

class AuthControllerAdmin implements ControllerInterface
{
    public function control(): void
    {
        // Si déjà connecté en tant qu'admin, rediriger
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            UrlHelper::redirect('dashboard-admin');
        }

        $message = '';
        $lang = $_GET['lang'] ?? 'fr';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $result = User::loginAdmin($email, $password);

            if ($result['success']) {
                UrlHelper::redirect('dashboard-admin');
            } else {
                $message = "Identifiant ou mot de passe incorrect.";
            }
        }

        // Charger la vue de connexion admin
        require __DIR__ . '/../../View/LoginPageAdmin.php';
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['login-admin', 'register-admin']) && in_array($method, ['GET', 'POST']);
    }
}
<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;

class AuthController implements ControllerInterface
{
    public function control()
    {
        if (isset($_SESSION['admin_id'])) {
            header('Location: /dashboard');
            exit;
        }

        $message = '';
        $isLogin = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once ROOT_PATH . '/module/blog/Model/User.php';
            $action = $_POST['action'] ?? '';

            if ($action === 'login') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                if (\Model\User::login($email, $password)) {
                    $_SESSION['admin_id'] = 1; // exemple
                    $_SESSION['message'] = "Connexion réussie !";
                    header('Location: /dashboard');
                    exit();
                } else {
                    $message = "Email ou mot de passe incorrect.";
                }
            }
        }

        require ROOT_PATH . '/module/blog/View/login.php';
    }

    public static function support(string $page, string $method): bool
{
    return in_array($page, ['login', 'register', 'reset', 'logout']);
}

}

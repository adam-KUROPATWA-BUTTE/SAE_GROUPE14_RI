<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Persistence\UserRepositoryPDO;

class AuthController implements ControllerInterface
{
    private UserRepositoryPDO $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepositoryPDO();
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['login', 'register', 'reset-password']);
    }

    public function control(): void
    {
        $page = $_GET['page'] ?? 'login';

        switch ($page) {
            case 'login':
                $this->handleLogin();
                break;
            case 'register':
                $this->handleRegister();
                break;
            case 'reset-password':
                $this->handleResetPassword();
                break;
        }
    }

    private function handleLogin(): void
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['identifier'] ?? '';
            $password = $_POST['password'] ?? '';

            $result = $this->userRepository->login($identifier, $password);

            if ($result['success']) {
                $_SESSION['user_role'] = $result['role'];

                if ($result['role'] === 'student' && isset($result['numetu'])) {
                    $_SESSION['numetu'] = $result['numetu'];
                }

                // Rediriger selon le rôle
                if ($result['role'] === 'admin') {
                    header('Location: index.php?page=home-admin');
                } else {
                    header('Location: index.php?page=home-student');
                }
                exit;
                }
            } else {
                $message = 'Identifiants incorrects';
            }

        // Render login page using absolute path
        $isLogin = true;
        $isReset = false;
        $isTokenReset = false;
        $token = '';

        // Try multiple possible paths
        $possiblePaths = [
            ROOT_PATH . '/public/View/Login.php',
            ROOT_PATH . '/public/module/site/View/Login.php',
            ROOT_PATH . '/View/Login.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }

        // If no path works, show error
        die("Login.php not found. Checked paths: " . implode(', ', $possiblePaths));
    }

    private function handleRegister(): void
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $numetu = $_POST['numetu'] ?? '';

            $message = 'Inscription réussie !';
        }

        $isLogin = false;
        $isReset = false;
        $isTokenReset = false;
        $token = '';

        $possiblePaths = [
            ROOT_PATH . '/public/View/Login.php',
            ROOT_PATH . '/public/module/site/View/Login.php',
            ROOT_PATH . '/View/Login.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }

        die("Login.php not found. Checked paths: " . implode(', ', $possiblePaths));
    }

    private function handleResetPassword(): void
    {
        $message = '';
        $isTokenReset = isset($_GET['token']);
        $token = $_GET['token'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($isTokenReset) {
                $newPassword = $_POST['password'] ?? '';
                $message = 'Mot de passe réinitialisé !';
            } else {
                $email = $_POST['email'] ?? '';
                $result = $this->userRepository->resetPassword($email);

                if ($result) {
                    $message = 'Email de réinitialisation envoyé !';
                } else {
                    $message = 'Email non trouvé';
                }
            }
        }

        $isLogin = false;
        $isReset = true;

        $possiblePaths = [
            ROOT_PATH . '/public/View/Login.php',
            ROOT_PATH . '/public/module/site/View/Login.php',
            ROOT_PATH . '/View/Login.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }

        die("Login.php not found. Checked paths: " . implode(', ', $possiblePaths));
    }
}
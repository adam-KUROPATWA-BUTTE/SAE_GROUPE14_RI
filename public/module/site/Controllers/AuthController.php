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

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__, 3));
        }
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

            $result = $this->userRepository->login(
                is_string($identifier) ? $identifier : '',
                is_string($password) ? $password : ''
            );

            if ($result['success']) {
                $_SESSION['user_role'] = $result['role'];

                if ($result['role'] === 'student' && isset($result['numetu'])) {
                    $_SESSION['numetu'] = $result['numetu'];
                }

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

        // Fix: Explicitly check if constant is a string
        $constPath = defined('ROOT_PATH') ? constant('ROOT_PATH') : null;
        $rootPath = is_string($constPath) ? $constPath : dirname(__DIR__, 3);

        $possiblePaths = [
            $rootPath . '/public/View/Login.php',
            $rootPath . '/public/module/site/View/Login.php',
            $rootPath . '/View/Login.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }

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

        // Fix: Explicitly check if constant is a string
        $constPath = defined('ROOT_PATH') ? constant('ROOT_PATH') : null;
        $rootPath = is_string($constPath) ? $constPath : dirname(__DIR__, 3);

        $possiblePaths = [
            $rootPath . '/public/View/Login.php',
            $rootPath . '/public/module/site/View/Login.php',
            $rootPath . '/View/Login.php',
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
                $result = $this->userRepository->resetPassword(is_string($email) ? $email : '');

                if ($result) {
                    $message = 'Email de réinitialisation envoyé !';
                } else {
                    $message = 'Email non trouvé';
                }
            }
        }

        $isLogin = false;
        $isReset = true;

        // Fix: Explicitly check if constant is a string
        $constPath = defined('ROOT_PATH') ? constant('ROOT_PATH') : null;
        $rootPath = is_string($constPath) ? $constPath : dirname(__DIR__, 3);

        $possiblePaths = [
            $rootPath . '/public/View/Login.php',
            $rootPath . '/public/module/site/View/Login.php',
            $rootPath . '/View/Login.php',
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

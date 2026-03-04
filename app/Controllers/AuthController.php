<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Persistence\UserRepositoryPDO;
use Core\View;

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
            $password   = $_POST['password']   ?? '';

            $result = $this->userRepository->login(
                is_string($identifier) ? $identifier : '',
                is_string($password)   ? $password   : ''
            );

            // FIX: Guard with isset before accessing optional keys 'role' and 'numetu'.
            // PHPStan sees them as role?: string — they may not exist even on success.
            if ($result['success'] && isset($result['role'])) {

                $role = $result['role'];
                $_SESSION['role'] = $role;

                if ($role === 'student' && isset($result['numetu'])) {
                    $_SESSION['numetu'] = $result['numetu'];
                }

                $destination = match($role) {
                    'super_admin'          => 'index.php?page=super-admin',
                    'admin'                => 'index.php?page=home-admin',
                    'coordinateur_etude'   => 'index.php?page=coordinateur-etude',
                    'coordinateur_stage'   => 'index.php?page=coordinateur-stage',
                    'chef_departement'     => 'index.php?page=chef-departement',
                    'coordinateur'         => 'index.php?page=home-coordinateur',
                    default                => 'index.php?page=home-student',
                };

                header('Location: ' . $destination);
                exit;

            } else {
                $message = 'Identifiants incorrects';
            }
        }

        View::render('login', [
            'message'      => $message,
            'isLogin'      => true,
            'isReset'      => false,
            'isTokenReset' => false,
            'token'        => ''
        ]);
    }

    private function handleRegister(): void
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = 'Inscription réussie !';
        }

        View::render('login', [
            'message'      => $message,
            'isLogin'      => false,
            'isReset'      => false,
            'isTokenReset' => false,
            'token'        => ''
        ]);
    }

    private function handleResetPassword(): void
    {
        $isTritanopia = !empty($_SESSION['tritanopia']);

        $message = '';
        $error   = '';
        $success = '';

        $isTokenReset = isset($_GET['token']);
        $token = $_GET['token'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ($isTokenReset) {

                $password        = $_POST['password']         ?? '';
                $passwordConfirm = $_POST['password_confirm'] ?? '';

                if ($password !== $passwordConfirm) {
                    $error = "Les mots de passe ne correspondent pas.";
                } elseif (strlen($password) < 8) {
                    $error = "Le mot de passe doit faire au moins 8 caractères.";
                } else {
                    $success = 'Mot de passe réinitialisé avec succès !';
                }

            } else {

                $email  = $_POST['email'] ?? '';
                $result = $this->userRepository->resetPassword(is_string($email) ? $email : '');

                $message = $result
                    ? 'Email de réinitialisation envoyé !'
                    : 'Email non trouvé';
            }
        }

        if ($isTokenReset) {
            View::render('reset_password', [
                'token'        => $token,
                'error'        => $error,
                'success'      => $success,
                'isTritanopia' => $isTritanopia
            ]);
        } else {
            View::render('login', [
                'message'      => $message,
                'isLogin'      => false,
                'isReset'      => true,
                'isTokenReset' => false,
                'token'        => ''
            ]);
        }
    }
}
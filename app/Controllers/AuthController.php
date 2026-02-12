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
            } else {
                // Le message d'erreur s'affiche seulement si la connexion échoue
                $message = 'Identifiants incorrects';
            }
        }

        // Appel de la vue avec le nouveau moteur !
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
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $numetu = $_POST['numetu'] ?? '';

            $message = 'Inscription réussie !';
        }

        // Appel de la vue pour l'inscription (isLogin passe à false)
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
        // 1. Démarrage session si nécessaire (Logique déplacée de la vue vers le contrôleur)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);

        $message = ''; // Utilisé pour la vue "login" (demande de reset)
        $error = '';   // Utilisé pour la vue "reset_password" (changement effectif)
        $success = ''; // Utilisé pour la vue "reset_password"

        $isTokenReset = isset($_GET['token']);
        $token = $_GET['token'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($isTokenReset) {
                // Logique de traitement du NOUVEAU mot de passe
                $password = $_POST['password'] ?? '';
                $passwordConfirm = $_POST['password_confirm'] ?? '';

                if ($password !== $passwordConfirm) {
                    $error = "Les mots de passe ne correspondent pas.";
                } elseif (strlen($password) < 8) {
                    $error = "Le mot de passe doit faire au moins 8 caractères.";
                } else {
                    // Ici appel au repository pour changer le mdp avec le token...
                    // $this->userRepository->updatePasswordWithToken($token, $password);
                    $success = 'Mot de passe réinitialisé avec succès !';
                }
            } else {
                // Logique d'envoi de l'email (inchangée)
                $email = $_POST['email'] ?? '';
                $result = $this->userRepository->resetPassword(is_string($email) ? $email : '');

                if ($result) {
                    $message = 'Email de réinitialisation envoyé !';
                } else {
                    $message = 'Email non trouvé';
                }
            }
        }

        // 2. Choix de la vue à afficher
        if ($isTokenReset) {
            // Affichage du formulaire de changement de mot de passe (Nouvelle Vue)
            View::render('reset_password', [
                'token' => $token,
                'error' => $error,
                'success' => $success,
                'isTritanopia' => $isTritanopia
            ]);
        } else {
            // Affichage du formulaire de demande d'email (Ancienne Vue Login modifiée)
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
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
        return in_array($page, ['login', 'register', 'reset-password', 'force-reset-password', 'mentions-legales']);
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
            case 'force-reset-password':
                $this->handleForceResetPassword();
                break;
            case 'mentions-legales':
                $this->handleMentionsLegales();
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

            if ($result['success'] && isset($result['role'])) {

                $role = $result['role'];
                $_SESSION['role'] = $role;

                if ($role === 'student' && isset($result['numetu'])) {
                    $_SESSION['numetu'] = $result['numetu'];
                } else {
                    // Stocker le login pour les personnels (admin, coordinateurs...)
                    $_SESSION['login'] = $identifier;
                }

                if (isset($result['departement'])) {
                    $_SESSION['departement'] = $result['departement'];
                }

                // Stocker nom + prénom pour les bannières "modifié par"
                if (isset($result['nom']))    $_SESSION['admin_nom']    = $result['nom'];
                if (isset($result['prenom'])) $_SESSION['admin_prenom'] = $result['prenom'];

                // SÉCURITÉ : Interception pour première connexion (Étudiants ET Personnels)
                if (!empty($result['force_change_password'])) {
                    header('Location: index.php?page=force-reset-password');
                    exit;
                }

                $destination = match($role) {
                    'super_admin'        => 'index.php?page=super-admin',
                    'admin'              => 'index.php?page=home-admin',
                    'coordinateur_etude',
                    'coordinateur_stage',
                    'chef_departement',
                    'coordinateur'       => 'index.php?page=home-coordinateur',
                    default              => 'index.php?page=home-student',
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

    private function handleForceResetPassword(): void
    {
        // Vérifie qu'un identifiant est bien présent en session (étudiant ou personnel)
        if (empty($_SESSION['numetu']) && empty($_SESSION['login'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password        = $_POST['password']         ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            if ($password !== $passwordConfirm) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{12,}$/', $password)) {
                $error = "Le mot de passe doit contenir au moins 12 caractères, dont une majuscule et un caractère spécial.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Récupère l'identifiant concerné (le numéro étudiant ou l'email du personnel)
                $userIdentifier = !empty($_SESSION['numetu']) ? $_SESSION['numetu'] : $_SESSION['login'];
                
                // Mise à jour en base de données
                $this->userRepository->updatePasswordAndUnlock($userIdentifier, $hashedPassword);

                // Redirection vers le bon tableau de bord selon le rôle
                $role = $_SESSION['role'] ?? 'student';
                $destination = match($role) {
                    'super_admin'        => 'index.php?page=super-admin',
                    'admin'              => 'index.php?page=home-admin',
                    'coordinateur_etude',
                    'coordinateur_stage',
                    'chef_departement',
                    'coordinateur'       => 'index.php?page=home-coordinateur',
                    default              => 'index.php?page=home-student',
                };

                header('Location: ' . $destination);
                exit;
            }
        }

        View::render('force_reset_password', [
            'error'   => $error,
            'success' => $success
        ]);
    }

    private function handleRegister(): void
    {
        // ... (Pas de changement ici)
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
        // ... (Pas de changement ici)
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
                } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{12,}$/', $password)) {
                    $error = "Le mot de passe doit contenir au moins 12 caractères, dont une majuscule et un caractère spécial.";
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

    private function handleMentionsLegales(): void
    {
        $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
        
        $t = function(array $frEn) use ($lang) {
            return $frEn[$lang] ?? $frEn['fr'] ?? '';
        };

        View::render('mentions_legales', [
            'lang' => $lang,
            't'    => $t
        ]);
    }
}
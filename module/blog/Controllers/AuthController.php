<?php
// NE PAS redémarrer la session ici, elle est déjà démarrée dans index.php
require_once __DIR__ . '/../Model/User.php';

class AuthController
{
    public function index()
    {
        if (isset($_SESSION['admin_id'])) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        $message = '';
        $isLogin = true;
        $isReset = false;
        $isTokenReset = false;
        $token = $_GET['token'] ?? null;
        $page = $_GET['page'] ?? 'login';

        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if ($page === 'reset' && $token) {
            $isLogin = false;
            $isReset = false;
            $isTokenReset = true;
        } elseif ($page === 'register') {
            $isLogin = false;
            $isReset = false;
        } elseif ($page === 'reset') {
            $isReset = true;
            $isLogin = false;
        } elseif ($page === 'login') {
            $isLogin = true;
            $isReset = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'login') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if (User::login($email, $password)) {
                    $_SESSION['message'] = "Connexion réussie !";
                    header('Location: index.php?page=dashboard');
                    exit;
                } else {
                    $message = "Email ou mot de passe incorrect.";
                }
            } elseif ($action === 'register') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $nom = $_POST['nom'] ?? '';
                $prenom = $_POST['prenom'] ?? '';
                
                if (User::register($email, $password, $nom, $prenom)) {
                    $_SESSION['message'] = "Compte créé avec succès !";
                    header('Location: index.php?page=login');
                    exit;
                } else {
                    $message = "Erreur lors de la création du compte.";
                    $isLogin = false;
                    $isReset = false;
                }
            } elseif ($action === 'reset') {
                $email = $_POST['email'] ?? '';
                
                if (User::resetPassword($email)) {
                    $message = "Si cet email existe, un lien de réinitialisation a été envoyé.";
                    $isReset = false;
                    $isLogin = true;
                } else {
                    $message = "Erreur lors de la demande de réinitialisation.";
                }
            } elseif ($action === 'token_reset') {
                $token = $_POST['token'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                
                if (User::updatePasswordWithToken($token, $newPassword)) {
                    $_SESSION['message'] = "Mot de passe réinitialisé avec succès.";
                    header('Location: index.php?page=login');
                    exit;
                } else {
                    $message = "Lien invalide ou expiré.";
                    $isTokenReset = true;
                    $isLogin = false;
                }
            }
        }

        require __DIR__ . '/../View/login.php';
    }

    public function logout()
    {
        // Déconnexion simple sans middleware
        session_unset();
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
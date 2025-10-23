<?php
namespace Controllers\site;

require_once ROOT_PATH . '/public/module/site/Model/User/UserAdmin.php';
require_once ROOT_PATH . '/public/module/site/Model/User/UserStudent.php';

use Controllers\ControllerInterface;
use Model\UserAdmin;
use Model\UserStudent;

class AuthController implements ControllerInterface
{
    /**
     * Méthode qui vérifie si ce contrôleur gère la page demandée
     */
    public static function support(string $page, string $method): bool
    {
        $authPages = [
            'login',
            'register',
            'register-admin',
            'logout',
            'forgot-password',
            'reset-password'
        ];
        
        return in_array($page, $authPages);
    }

    /**
     * Méthode principale qui route vers la bonne action
     */
    public function control(): void
    {
        // Récupérer la page actuelle
        $page = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        switch ($page) {
            case 'login':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    self::login();
                } else {
                    self::showLoginPage();
                }
                break;
                
            case 'register':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    self::registerStudent();
                } else {
                    self::showRegisterPage();
                }
                break;
                
            case 'register-admin':
                self::registerAdmin();
                break;
                
            case 'logout':
                self::logout();
                break;
                
            case 'forgot-password':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    self::requestPasswordReset();
                } else {
                    self::showForgotPasswordPage();
                }
                break;
                
            case 'reset-password':
                self::resetPassword();
                break;
        }
    }

    /**
     * Connexion universelle - détecte automatiquement le type d'utilisateur
     */
    public static function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs';
            header('Location: /login');
            exit();
        }

        // Déterminer si c'est un email (admin) ou un numéro étudiant
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            // Tentative de connexion Admin
            $result = UserAdmin::login($identifier, $password);
        } else {
            // Tentative de connexion Étudiant (par numéro)
            $result = UserStudent::login($identifier, $password);
        }

        if ($result['success']) {
            // Redirection selon le rôle
            if ($result['role'] === 'admin') {
                header('Location: /dashboard-admin');
            } else {
                header('Location: /dashboard-student');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Identifiant ou mot de passe incorrect';
            header('Location: /login');
            exit();
        }
    }

    /**
     * Inscription étudiant (publique)
     */
    public static function registerStudent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $typeEtudiant = $_POST['type_etudiant'] ?? '';

        // Validation
        if (empty($email) || empty($password) || empty($nom) || empty($prenom) || empty($typeEtudiant)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs';
            header('Location: /register');
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email invalide';
            header('Location: /register');
            exit();
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Les mots de passe ne correspondent pas';
            header('Location: /register');
            exit();
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caractères';
            header('Location: /register');
            exit();
        }

        $result = UserStudent::register($email, $password, $nom, $prenom, $typeEtudiant);

        if ($result) {
            $_SESSION['success'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            header('Location: /login');
        } else {
            $_SESSION['error'] = 'Une erreur est survenue lors de l\'inscription. L\'email est peut-être déjà utilisé.';
            header('Location: /register');
        }
        exit();
    }

    /**
     * Inscription admin (réservé aux admins connectés)
     */
    public static function registerAdmin()
    {
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Afficher le formulaire
            require_once ROOT_PATH . '/public/module/site/View/register_admin.php';
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');

        // Validation
        if (empty($email) || empty($password) || empty($nom) || empty($prenom)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs';
            header('Location: /register-admin');
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email invalide';
            header('Location: /register-admin');
            exit();
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Les mots de passe ne correspondent pas';
            header('Location: /register-admin');
            exit();
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caractères';
            header('Location: /register-admin');
            exit();
        }

        $result = UserAdmin::register($email, $password, $nom, $prenom, $_SESSION['admin_id']);

        if ($result) {
            $_SESSION['success'] = 'Admin créé avec succès !';
            header('Location: /dashboard-admin');
        } else {
            $_SESSION['error'] = 'Une erreur est survenue. L\'email est peut-être déjà utilisé.';
            header('Location: /register-admin');
        }
        exit();
    }

    /**
     * Demande de réinitialisation de mot de passe
     */
    public static function requestPasswordReset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit();
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Veuillez entrer une adresse email valide';
            header('Location: /forgot-password');
            exit();
        }

        // Tenter les deux (admin et étudiant)
        UserAdmin::resetPassword($email);
        UserStudent::resetPassword($email);

        // Message générique pour ne pas révéler si l'email existe
        $_SESSION['success'] = 'Si cette adresse email existe, vous recevrez un lien de réinitialisation.';
        header('Location: /login');
        exit();
    }

    /**
     * Réinitialisation du mot de passe avec token
     */
    public static function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Afficher le formulaire
            $token = $_GET['token'] ?? '';
            if (empty($token)) {
                header('Location: /login');
                exit();
            }
            require_once ROOT_PATH . '/public/module/site/View/ResetPasswordPage.php';
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($token) || empty($password)) {
            $_SESSION['error'] = 'Données manquantes';
            header('Location: /login');
            exit();
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Les mots de passe ne correspondent pas';
            header('Location: /reset-password?token=' . urlencode($token));
            exit();
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caractères';
            header('Location: /reset-password?token=' . urlencode($token));
            exit();
        }

        // Tenter admin puis étudiant
        $result = UserAdmin::updatePasswordWithToken($token, $password);
        
        if (!$result) {
            $result = UserStudent::updatePasswordWithToken($token, $password);
        }

        if ($result) {
            $_SESSION['success'] = 'Mot de passe réinitialisé avec succès !';
            header('Location: /login');
        } else {
            $_SESSION['error'] = 'Le lien de réinitialisation est invalide ou a expiré';
            header('Location: /login');
        }
        exit();
    }

    /**
     * Déconnexion universelle
     */
    public static function logout()
    {
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        
        if ($isAdmin) {
            UserAdmin::logout();
        } else {
            UserStudent::logout();
        }
        
        header('Location: /login');
        exit();
    }

    /**
     * Afficher la page de connexion
     */
    public static function showLoginPage()
    {
        // Récupérer les messages de session
        $message = '';
        if (isset($_SESSION['error'])) {
            $message = $_SESSION['error'];
            unset($_SESSION['error']);
        } elseif (isset($_SESSION['success'])) {
            $message = $_SESSION['success'];
            unset($_SESSION['success']);
        }
        
        // Utiliser le fichier Login.php qui instancie LoginPage
        $isTokenReset = false;
        $isLogin = true;
        $isReset = false;
        $token = '';
        
        require_once ROOT_PATH . '/public/module/site/View/Login.php';
    }

    /**
     * Afficher la page d'inscription étudiant
     */
    public static function showRegisterPage()
    {
        // TODO: Créer RegisterPage.php si nécessaire
        echo "Page d'inscription étudiant - À implémenter";
    }

    /**
     * Afficher la page de demande de réinitialisation
     */
    public static function showForgotPasswordPage()
    {
        // TODO: Créer ForgotPasswordPage.php si nécessaire
        echo "Page mot de passe oublié - À implémenter";
    }
}
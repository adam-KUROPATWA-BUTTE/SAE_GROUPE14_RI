<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\User\UserAdmin;
use Model\User\UserStudent;

/**
 * AuthController
 *
 * Handles authentication and registration for both students and admins.
 *
 * Responsibilities:
 *  - Display login and registration pages
 *  - Process login for students and admins
 *  - Register new students or admins
 *  - Handle logout and password reset requests
 */
class AuthController implements ControllerInterface
{
    /**
     * Determines if this controller handles the requested page.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if this controller handles the page
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
     * Main control method that routes requests to the appropriate action.
     */
    public function control(): void
    {
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
        }
    }

    /**
     * Universal login method - automatically detects user type.
     *
     * Students login with student number, admins with email.
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
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: /login');
            exit();
        }

        // Detect if identifier is email (admin) or student number
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $result = UserAdmin::login($identifier, $password);
        } else {
            $result = UserStudent::login($identifier, $password);
        }

        if ($result['success']) {
            if ($result['role'] === 'admin') {
                header('Location: /home-admin');
            } else {
                header('Location: /home-student');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect username or password';
            header('Location: /login');
            exit();
        }
    }

    /**
     * Student registration (public).
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

        // Validate inputs
        if (empty($email) || empty($password) || empty($nom) || empty($prenom) || empty($typeEtudiant)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: /register');
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email';
            header('Location: /register');
            exit();
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: /register');
            exit();
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: /register');
            exit();
        }

        $result = UserStudent::register($email, $password, $nom, $prenom, $typeEtudiant);

        if ($result) {
            $_SESSION['success'] = 'Registration successful! You can now log in.';
            header('Location: /login');
        } else {
            $_SESSION['error'] = 'Error occurred during registration. The email may already be used.';
            header('Location: /register');
        }
        exit();
    }

    /**
     * Admin registration (restricted to logged-in admins).
     */
    public static function registerAdmin()
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once ROOT_PATH . '/public/module/site/View/register_admin.php';
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');

        // Validate inputs
        if (empty($email) || empty($password) || empty($nom) || empty($prenom)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: /register-admin');
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email';
            header('Location: /register-admin');
            exit();
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: /register-admin');
            exit();
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: /register-admin');
            exit();
        }

        $result = UserAdmin::register($email, $password, $nom, $prenom, $_SESSION['admin_id']);

        if ($result) {
            $_SESSION['success'] = 'Admin created successfully!';
            header('Location: /dashboard-admin');
        } else {
            $_SESSION['error'] = 'Error occurred. Email may already be used.';
            header('Location: /register-admin');
        }
        exit();
    }

    /**
     * Request password reset.
     */
    public static function requestPasswordReset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit();
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
            header('Location: /forgot-password');
            exit();
        }

        // Attempt reset for both admin and student
        UserAdmin::resetPassword($email);
        UserStudent::resetPassword($email);

        $_SESSION['success'] = 'If this email exists, you will receive a reset link.';
        header('Location: /login');
        exit();
    }

    /**
     * Universal logout.
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
     * Display login page.
     */
    public static function showLoginPage()
    {
        $message = '';
        if (isset($_SESSION['error'])) {
            $message = $_SESSION['error'];
            unset($_SESSION['error']);
        } elseif (isset($_SESSION['success'])) {
            $message = $_SESSION['success'];
            unset($_SESSION['success']);
        }

        $isTokenReset = false;
        $isLogin = true;
        $isReset = false;
        $token = '';

        require_once ROOT_PATH . '/public/module/site/View/Login.php';
    }

    /**
     * Display student registration page.
     */
    public static function showRegisterPage()
    {
        echo "Student registration page - To be implemented";
    }
}

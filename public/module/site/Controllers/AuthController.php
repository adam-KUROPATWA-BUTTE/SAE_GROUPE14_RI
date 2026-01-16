<?php

// phpcs:disable Generic.Files.LineLength

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
 * - Display login and registration pages
 * - Process login for students and admins
 * - Register new students or admins
 * - Handle logout and password reset requests
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
        // Correction Level 9: Sécurisation de parse_url (peut retourner false/null) et $_GET
        $requestUri = strval($_SERVER['REQUEST_URI'] ?? '');
        $path = parse_url($requestUri, PHP_URL_PATH);

        // Si $_GET['page'] existe, on le cast en string, sinon on prend le path parsé casté en string
        $page = isset($_GET['page'])
            ? strval($_GET['page'])
            : trim(strval($path), '/');

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

            case 'reset-password':
            case 'forgot-password':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    self::requestPasswordReset();
                } else {
                    // Logique d'affichage par défaut si besoin
                    header('Location: /login');
                }
                break;
        }
    }

    /**
     * Universal login method - automatically detects user type.
     *
     * Students login with student number, admins with email.
     */
    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $identifier = trim(strval($_POST['identifier'] ?? ''));
        $password = strval($_POST['password'] ?? '');

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
            $role = $result['role'] ?? 'student';
            if ($role === 'admin') {
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
    public static function registerStudent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit();
        }

        // Correction Level 9: Cast explicite strval() avant trim()
        $email = trim(strval($_POST['email'] ?? ''));
        $password = strval($_POST['password'] ?? '');
        $passwordConfirm = strval($_POST['password_confirm'] ?? '');
        $nom = trim(strval($_POST['nom'] ?? ''));
        $prenom = trim(strval($_POST['prenom'] ?? ''));
        $typeEtudiant = strval($_POST['type_etudiant'] ?? '');

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
    public static function registerAdmin(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../../View/RegisterAdmin.php';
            return;
        }

        // Correction Level 9: Cast explicite strval()
        $email = trim(strval($_POST['email'] ?? ''));
        $password = strval($_POST['password'] ?? '');
        $passwordConfirm = strval($_POST['password_confirm'] ?? '');
        $nom = trim(strval($_POST['nom'] ?? ''));
        $prenom = trim(strval($_POST['prenom'] ?? ''));

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

        $adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
        $result = UserAdmin::register($email, $password, $nom, $prenom, $adminId);

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
    public static function requestPasswordReset(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit();
        }

        // Correction Level 9: Cast explicite strval()
        $email = isset($_POST['email']) ? trim(strval($_POST['email'])) : '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
            header('Location: /forgot-password');
            exit();
        }

        // Attempt reset for both admin and student
        // @phpstan-ignore-next-line
        UserAdmin::resetPassword($email);
        // @phpstan-ignore-next-line
        UserStudent::resetPassword($email);

        $_SESSION['success'] = 'If this email exists, you will receive a reset link.';
        header('Location: /login');
        exit();
    }

    /**
     * Universal logout.
     */
    public static function logout(): void
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
    public static function showLoginPage(): void
    {
        $message = '';
        if (isset($_SESSION['error'])) {
            $message = (string)$_SESSION['error'];
            unset($_SESSION['error']);
        } elseif (isset($_SESSION['success'])) {
            $message = (string)$_SESSION['success'];
            unset($_SESSION['success']);
        }

        $isTokenReset = false;
        $isLogin = true;
        $isReset = false;
        $token = '';

        require_once __DIR__ . '/../View/Login.php';
    }

    /**
     * Display student registration page.
     */
    public static function showRegisterPage(): void
    {
        echo "Student registration page - To be implemented";
    }
}

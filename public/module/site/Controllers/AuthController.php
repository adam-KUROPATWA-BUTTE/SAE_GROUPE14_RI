<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\User\UserAdmin;
use Model\User\UserStudent;

/**
 * Class AuthController
 *
 * Handles authentication flows (Login, Registration, Logout, Password Reset).
 */
class AuthController implements ControllerInterface
{
    /**
     * Determines if the controller supports the requested page and method.
     *
     * @param string $page   The requested page (e.g., 'login', 'dashboard').
     * @param string $method The HTTP method (e.g., 'GET', 'POST').
     * @return bool True if supported, false otherwise.
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

        return in_array($page, $authPages, true);
    }

    /**
     * Main method that executes the controller logic.
     *
     * @return void
     */
    public function control(): void
    {
        // Fix PHPStan: parse_url can return null or false, casting to string ensures trim works.
        $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $page = $_GET['page'] ?? trim((string)$uriPath, '/');

        switch ($page) {
            case 'login':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    self::login();
                } else {
                    self::showLoginPage();
                }
                break;
            case 'register':
                self::registerStudent();
                break;
            case 'register-admin':
                self::registerAdmin();
                break;
            case 'logout':
                self::logout();
                break;
            case 'forgot-password':
                self::requestPasswordReset();
                break;
            case 'reset-password':
                // Logic for the actual reset form could go here
                break;
            default:
                self::showLoginPage();
                break;
        }
    }

    /**
     * Processes the login form submission.
     *
     * @return void
     */
    private static function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $identifier = $_POST['identifier'] ?? '';
        $password = $_POST['password'] ?? '';

        // Attempt Admin Login
        $adminResult = UserAdmin::login($identifier, $password);
        if ($adminResult['success']) {
            header('Location: index.php?page=dashboard-admin');
            exit();
        }

        // Attempt Student Login
        $studentResult = UserStudent::login($identifier, $password);
        if ($studentResult['success']) {
            header('Location: index.php?page=dashboard-student');
            exit();
        }

        $_SESSION['error'] = 'Identifiants invalides.'; // Keeping French message for end-users
        header('Location: index.php?page=login');
        exit();
    }

    /**
     * Handles student registration logic.
     *
     * @return void
     */
    private static function registerStudent(): void
    {
        // Placeholder for student registration logic
        self::showRegisterPage();
    }

    /**
     * Handles admin registration logic.
     *
     * @return void
     */
    private static function registerAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (defined('ROOT_PATH')) {
            require_once ROOT_PATH . '/public/module/site/View/RegisterAdmin.php';
        } else {
            // Fallback for static analysis or local dev context
            require_once __DIR__ . '/../../View/RegisterAdmin.php';
        }
    }

    /**
     * Handles password reset requests.
     *
     * @return void
     */
    private static function requestPasswordReset(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_POST['email'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
            header('Location: /forgot-password');
            exit();
        }

        // Note: These methods must be implemented in the UserAdmin/UserStudent models
        // to fully satisfy PHPStan if it analyzes the whole project at once.
        UserAdmin::resetPassword($email);
        UserStudent::resetPassword($email);

        $_SESSION['success'] = 'If this email exists, you will receive a reset link.';
        header('Location: /login');
        exit();
    }

    /**
     * Logs out the current user.
     *
     * @return void
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

        if ($isAdmin) {
            UserAdmin::logout();
        } else {
            UserStudent::logout();
        }

        header('Location: index.php?page=login');
        exit();
    }

    /**
     * Displays the login page.
     *
     * @return void
     */
    public static function showLoginPage(): void
    {
        $message = '';
        if (isset($_SESSION['error'])) {
            $message = $_SESSION['error'];
            unset($_SESSION['error']);
        } elseif (isset($_SESSION['success'])) {
            $message = $_SESSION['success'];
            unset($_SESSION['success']);
        }

        // Variables used in the View
        $isTokenReset = false;
        $isLogin = true;
        $isReset = false;
        $token = '';

        if (defined('ROOT_PATH')) {
            require_once ROOT_PATH . '/public/module/site/View/Login.php';
        } else {
            require_once __DIR__ . '/../../View/Login.php';
        }
    }

    /**
     * Displays the student registration page.
     *
     * @return void
     */
    public static function showRegisterPage(): void
    {
        echo "Student registration page - To be implemented";
    }
}
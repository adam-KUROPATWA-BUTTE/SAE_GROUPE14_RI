<?php

namespace Controllers;

/**
 * Class AuthGuard
 *
 * Handles user authentication checks and role-based access control.
 * Provides static methods to restrict access to specific pages based on the user's session.
 */
class AuthGuard
{
    /**
     * Ensures that the current user is an administrator.
     * If not, redirects to the login page.
     *
     * @return void
     */
    public static function requireAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Fix: Use 'user_role' to match AuthController
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Ensures that the current user is a student.
     * If not, redirects to the login page.
     *
     * @return void
     */
    public static function requireStudent(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Fix: Use 'user_role' and check for 'etudiant' (as set in AuthController)
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Ensures that the user is authenticated (any role).
     * If not, redirects to the login page.
     *
     * @return void
     */
    public static function requireAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_role'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Ensures that the user has a specific role.
     *
     * @param string $role The required role (e.g., 'admin', 'etudiant').
     * @return void
     */
    public static function requireRole(string $role): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Checks if the current user is an administrator.
     *
     * @return bool True if admin, false otherwise.
     */
    public static function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Checks if the current user is a student.
     *
     * @return bool True if student, false otherwise.
     */
    public static function isStudent(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Fix: check for 'etudiant' to match AuthController
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'etudiant';
    }

    /**
     * Redirects the user to their respective dashboard based on their role.
     *
     * @return void
     */
    public static function redirectToDashboard(): void
    {
        if (self::isAdmin()) {
            header('Location: index.php?page=dashboard-admin');
            exit;
        }

        if (self::isStudent()) {
            header('Location: index.php?page=dashboard-student');
            exit;
        }

        // Fallback: Redirect to login if role is unknown or not logged in
        header('Location: index.php?page=login');
        exit;
    }
}
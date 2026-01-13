<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers;

/**
 * AuthGuard
 *
 * Handles user authentication and role-based access control.
 * Provides methods to ensure that a user is logged in and has the correct role.
 */
class AuthGuard
{
    /**
     * Ensures that an admin user is logged in.
     *
     * If the user is not an admin, they are redirected to the login page.
     */
    public static function requireAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Ensures that a student user is logged in.
     *
     * If the user is not a student, they are redirected to the login page.
     */
    public static function requireStudent(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Ensures that any authenticated user is logged in.
     *
     * Redirects to login page if no user is authenticated.
     */
    public static function requireAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Ensures that the user has a specific role.
     *
     * @param string $role Role to check (e.g., 'admin', 'student')
     */
    public static function requireRole(string $role): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            $loginPage = $role === 'admin' ? '/login' : '/login';
            header('Location: ' . $loginPage);
            exit;
        }
    }

    /**
     * Checks if the current user is an admin.
     *
     * @return bool True if admin, false otherwise
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Checks if the current user is a student.
     *
     * @return bool True if student, false otherwise
     */
    public static function isStudent(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
    }

    /**
     * Redirects the user to their dashboard based on role.
     *
     * Admin users are redirected to '/dashboard-admin', students to '/dashboard-student'.
     */
    public static function redirectToDashboard(): void
    {
        if (self::isAdmin()) {
            header('Location: /dashboard-admin');
        } else {
            header('Location: /dashboard-student');
        }
        exit;
    }
}

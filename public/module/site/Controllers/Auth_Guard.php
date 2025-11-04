<?php
namespace Controllers;

class Auth_Guard
{
    /**
     * Vérifie qu'un utilisateur admin est connecté
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
     * Vérifie qu'un étudiant est connecté
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
     * Vérifie qu'un utilisateur (peu importe le rôle) est connecté
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
     * Vérifie un rôle spécifique (ancienne méthode pour compatibilité)
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
     * Vérifie si l'utilisateur est admin
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est étudiant
     */
    public static function isStudent(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
    }

    /**
     * Redirige l'utilisateur vers son dashboard selon son rôle
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
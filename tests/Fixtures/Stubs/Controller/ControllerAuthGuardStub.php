<?php

namespace Controllers;

class Auth_Guard
{
    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function isStudent(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'etudiant';
    }

    public static function requireRole(string $role): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            throw new \RuntimeException('Redirected');
        }
    }
}

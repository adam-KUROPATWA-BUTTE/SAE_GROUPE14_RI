<?php

namespace Model\User;

class UserStudent
{
    public static $loginReturn = ['success' => false, 'role' => 'etudiant'];
    public static $loginArgs = null;
    public static $registerReturn = false;
    public static $registerArgs = null;
    public static $logoutReturn = true;
    public static $logoutCalled = false;

    public static function login($identifier, $password)
    {
        self::$loginArgs = [$identifier, $password];
        return self::$loginReturn;
    }

    public static function register($email, $password, $nom, $prenom, $typeEtudiant)
    {
        self::$registerArgs = [$email, $password, $nom, $prenom, $typeEtudiant];
        return self::$registerReturn;
    }

    public static function resetPassword($email): void
    {
        // Intentionally no-op for tests
    }

    public static function logout(): bool
    {
        self::$logoutCalled = true;
        return self::$logoutReturn;
    }

    public static function reset(): void
    {
        self::$loginReturn = ['success' => false, 'role' => 'etudiant'];
        self::$loginArgs = null;
        self::$registerReturn = false;
        self::$registerArgs = null;
        self::$logoutReturn = true;
        self::$logoutCalled = false;
    }
}

<?php

namespace Model\User;

class UserStudent
{
    /** @var array<string, mixed> */
    public static array $loginReturn = ['success' => false, 'role' => 'etudiant'];
    /** @var array<int, string>|null */
    public static ?array $loginArgs = null;
    public static bool $registerReturn = false;
    /** @var array<int, string>|null */
    public static ?array $registerArgs = null;
    public static bool $logoutReturn = true;
    public static bool $logoutCalled = false;

    /**
     * @return array<string, mixed>
     */
    public static function login(string $identifier, string $password): array
    {
        self::$loginArgs = [$identifier, $password];
        return self::$loginReturn;
    }

    public static function register(string $email, string $password, string $nom, string $prenom, string $typeEtudiant): bool
    {
        self::$registerArgs = [$email, $password, $nom, $prenom, $typeEtudiant];
        return self::$registerReturn;
    }

    public static function resetPassword(string $email): void
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

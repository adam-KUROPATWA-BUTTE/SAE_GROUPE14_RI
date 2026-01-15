<?php

namespace Model\User;

class UserAdmin
{
    /** @var array<string, mixed> */
    public static array $loginReturn = ['success' => false];
    /** @var array<int, string>|null */
    public static ?array $loginArgs = null;
    public static bool $registerReturn = false;
    /** @var array<int, string|int>|null */
    public static ?array $registerArgs = null;
    public static bool $logoutReturn = true;
    public static bool $logoutCalled = false;

    /**
     * @return array<string, mixed>
     */
    public static function login(string $email, string $password): array
    {
        self::$loginArgs = [$email, $password];
        return self::$loginReturn;
    }

    public static function register(string $email, string $password, string $nom, string $prenom, string|int $requestingAdminId): bool
    {
        self::$registerArgs = [$email, $password, $nom, $prenom, $requestingAdminId];
        return self::$registerReturn;
    }

    public static function logout(): bool
    {
        self::$logoutCalled = true;
        return self::$logoutReturn;
    }

    public static function reset(): void
    {
        self::$loginReturn = ['success' => false];
        self::$loginArgs = null;
        self::$registerReturn = false;
        self::$registerArgs = null;
        self::$logoutReturn = true;
        self::$logoutCalled = false;
    }
}

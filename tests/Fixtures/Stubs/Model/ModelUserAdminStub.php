<?php

namespace Model\User;

class UserAdmin
{
    public static $loginReturn = ['success' => false];
    public static $loginArgs = null;
    public static $registerReturn = false;
    public static $registerArgs = null;
    public static $logoutReturn = true;
    public static $logoutCalled = false;

    public static function login($email, $password)
    {
        self::$loginArgs = [$email, $password];
        return self::$loginReturn;
    }

    public static function register($email, $password, $nom, $prenom, $requestingAdminId)
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

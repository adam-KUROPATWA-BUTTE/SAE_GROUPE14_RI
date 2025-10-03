<?php

class User
{
    public static function login($email, $password)
    {
        // Ici, tu mets la logique de connexion (exemple fictif)
        if ($email === 'admin@amu.fr' && $password === 'admin') {
            return true;
        }
        return false;
    }

    public static function register($email, $password)
    {
        // Ici, tu mets la logique d'inscription (exemple fictif)
        return true;
    }

    public static function resetPassword($email)
    {
        // Ici, tu mets la logique de reset (exemple fictif)
        return true;
    }
}
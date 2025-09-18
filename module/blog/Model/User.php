<?php

namespace Model;

class User
{

    function login(string $username, string $password): bool
    {
        return $username === 'admin' && $password === 'admin';
    }

    function register(string $username, string $password): DTO\User
    {

        return new DTO\User($username, $password);

    }
}
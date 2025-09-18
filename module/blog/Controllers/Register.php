<?php

namespace Controllers;


class Register implements Controller
{
    const string PATH = 'user/register';

    function control(): void{
        echo 'bonjour';
    }

    static function resolve(string $path): bool {
        return $path ===self::PATH;
    }
}
<?php

namespace Controllers;

use View\User\LoginFormView;

class Login implements Controller
{

    function control(): void{
        $LoginFormView = new LoginFormView();
        echo $LoginFormView->render();
    }

    static function resolve(string $path): bool {
        return $path === 'user/login';
    }
}
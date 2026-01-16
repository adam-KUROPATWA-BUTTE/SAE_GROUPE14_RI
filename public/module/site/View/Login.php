<?php

// phpcs:disable Generic.Files.LineLength

// File View/Login.php
// Instantiation and rendering of the login/registration page

require_once __DIR__ . DIRECTORY_SEPARATOR . 'LoginPage.php';

use View\LoginPage;

/**
 * Possible parameters passed from the controller:
 * @var string|null $message      Message to display (error, info)
 * @var bool|null   $isTokenReset Indicates if we are in the password reset phase
 * @var bool|null   $isLogin      Indicates if the page is for login (true) or registration (false)
 * @var bool|null   $isReset      Indicates if we are in reset mode (reset form)
 * @var string|null $token        Token for password reset
 */
$loginPage = new LoginPage(
    $message ?? '',
    $isTokenReset ?? false,
    $isLogin ?? true,
    $isReset ?? false,
    $token ?? ''
);

$loginPage->render();

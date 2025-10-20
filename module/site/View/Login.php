<?php
// Fichier View/Login.php
// Ce fichier instancie et affiche la page de login

require_once __DIR__ . DIRECTORY_SEPARATOR . 'LoginPage.php';

$loginPage = new \View\LoginPage(
    $message ?? '',
    $isTokenReset ?? false,
    $isLogin ?? true,
    $isReset ?? false,
    $token ?? ''
);

$loginPage->render();
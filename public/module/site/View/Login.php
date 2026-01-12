<?php

// Fichier View/Login.php
// Instanciation et affichage de la page de connexion / login

require_once __DIR__ . DIRECTORY_SEPARATOR . 'LoginPage.php';

use View\LoginPage;

/**
 * Paramètres possibles :
 * @var string $message Message à afficher (erreur, info)
 * @var bool $isTokenReset Indique si on est en phase de réinitialisation de mot de passe
 * @var bool $isLogin Indique si la page est affichée pour login (true) ou inscription (false)
 * @var bool $isReset Indique si on est en mode reset (formulaire de réinitialisation)
 * @var string $token Token pour réinitialisation
 */
$loginPage = new LoginPage(
    $message ?? '',
    $isTokenReset ?? false,
    $isLogin ?? true,
    $isReset ?? false,
    $token ?? ''
);

// Affichage de la page
$loginPage->render();

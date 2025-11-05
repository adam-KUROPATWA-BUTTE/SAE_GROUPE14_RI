<?php
// Charge l'autoload de Composer (pour Mailjet, Dotenv, PHPUnit, etc.)
require_once __DIR__ . '/../vendor/autoload.php';

// Charge ton autoloader perso (pour tes classes du projet)
require_once __DIR__ . '/../Autoloader.php';
Autoloader::register();

// Facultatif : charger les variables d'environnement (si tu utilises .env)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

<?php
session_start(); // IMPORTANT : Ajoutez cette ligne au tout début si ce n'est pas déjà fait

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'dashboard':
        require_once __DIR__ . '/module/blog/Controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'login':
    case 'register':
    case 'reset':
        require_once __DIR__ . '/module/blog/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->index();
        break;

    case 'settings':
        require_once __DIR__ . '/module/blog/Controllers/SettingsController.php';
        $controller = new SettingsController();
        $controller->index();
        break;

    case 'folders':
        require_once __DIR__ . '/module/blog/Controllers/FoldersController.php';
        $controller = new FoldersController();
        $controller->index();
        break;

    case 'help':
        require_once __DIR__ . '/module/blog/Controllers/HelpController.php';
        $controller = new HelpController();
        $controller->index();
        break;

    case 'web_plan':
        require_once __DIR__ . '/module/blog/Controllers/WebPlanController.php';
        $controller = new WebPlanController();
        $controller->index();
        break;

    case 'home':
    default:
        require_once __DIR__ . '/module/blog/Controllers/IndexController.php';
        $controller = new IndexController();
        $controller->index();
        break;
}
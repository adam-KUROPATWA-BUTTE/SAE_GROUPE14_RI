<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'login':
    case 'register':
    case 'reset':
        require_once __DIR__ . '/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->index();
        break;
    
    case 'logout':
        require_once __DIR__ . '/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
    
    case 'dashboard':
        require_once __DIR__ . '/Controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;
    
    case 'folders':
        require_once __DIR__ . '/Controllers/FoldersController.php';
        $controller = new FoldersController();
        $controller->index();
        break;
    
    case 'settings':
        require_once __DIR__ . '/Controllers/SettingsController.php';
        $controller = new SettingsController();
        $controller->index();
        break;
    
    case 'help':
        require_once __DIR__ . '/Controllers/HelpController.php';
        $controller = new HelpController();
        $controller->index();
        break;
    
    case 'web_plan':
        require_once __DIR__ . '/Controllers/WebPlanController.php';
        $controller = new WebPlanController();
        $controller->index();
        break;
    
    case 'home':
    default:
        if (isset($_SESSION['admin_id'])) {
            header('Location: index.php?page=dashboard');
        } else {
            header('Location: index.php?page=login');
        }
        exit;
        break;
}
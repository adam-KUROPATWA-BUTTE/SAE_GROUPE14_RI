<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

define('ROOT_PATH', __DIR__);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "Autoloader.php";

use Controllers\site\AuthController;
use Controllers\site\DashboardController;
use Controllers\site\FoldersController;
use Controllers\site\HelpController;
use Controllers\site\IndexController;
use Controllers\site\SettingsController;
use Controllers\site\WebPlanController;

$controllers = [
    new AuthController(),
    new DashboardController(),
    new FoldersController(),
    new HelpController(),
    new SettingsController(),
    new WebPlanController(),
    new IndexController()
];

// Récupère la page depuis GET ou PATH_INFO
$page = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($page === '') $page = 'home';

// --- Gestion de la déconnexion ---
if ($page === 'logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// --- Contrôleur correspondant ---
foreach ($controllers as $controller) {
    if ($controller::support($page, $_SERVER['REQUEST_METHOD'])) {
        $controller->control();
        exit();
    }
}

http_response_code(404);
echo "Page non trouvée (404)";

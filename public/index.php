<?php
session_start();

// bascule uniquement si le paramètre est présent
if (isset($_GET['toggleTritanopia'])) {
    $_SESSION['tritanopia'] = !($_SESSION['tritanopia'] ?? false);
    // redirige pour supprimer ?toggleTritanopia de l'URL
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// lire la session pour toutes les pages
$tritanopiaMode = $_SESSION['tritanopia'] ?? false;

define('ROOT_PATH', dirname(__DIR__));

// Affichage des erreurs en dev
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chargement des dépendances
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/Autoloader.php';
require_once ROOT_PATH . '/Database.php';

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Enregistrement de l'autoloader personnalisé
Autoloader::register();

use Controllers\site\AuthController;
use Controllers\site\DashboardController;
use Controllers\site\FoldersController;
use Controllers\site\HelpController;
use Controllers\site\IndexController;
use Controllers\site\SaveStudentController;
use Controllers\site\PartnersController;
use Controllers\site\WebPlanController;

// Liste des contrôleurs
$controllers = [
    new AuthController(),
    new DashboardController(),
    new FoldersController(),
    new HelpController(),
    new PartnersController(),
    new WebPlanController(),
    new IndexController(),
    new SaveStudentController(),
];

// Récupération de la page demandée
$page = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($page === '') $page = 'home';

// Gestion de la déconnexion
if ($page === 'logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// Appel du bon contrôleur
foreach ($controllers as $controller) {
    if ($controller::support($page, $_SERVER['REQUEST_METHOD'])) {
        $controller->control();
        exit();
    }
}

// Page non trouvée
http_response_code(404);
echo "Page non trouvée (404)";

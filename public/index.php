<?php

/**
 * Main Entry Point (Front Controller).
 *
 * This file handles all incoming requests, initializes the environment,
 * manages sessions, and dispatches the request to the appropriate Controller.
 */

// --- 1. Environment & Error Reporting ---

// Enable full error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Define the root path of the application
define('ROOT_PATH', dirname(__DIR__));

// --- 2. Session Management ---

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Accessibility: Toggle Tritanopia mode (Color blindness support)
// Checks if the 'toggleTritanopia' parameter is in the URL
if (isset($_GET['toggleTritanopia'])) {
    $_SESSION['tritanopia'] = !($_SESSION['tritanopia'] ?? false);

    // Clean the URL by removing the query parameter and redirecting
    $cleanUrl = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: " . $cleanUrl);
    exit;
}

// --- 3. Autoloading & Configuration ---

// Load Composer dependencies (Dotenv, etc.)
require_once ROOT_PATH . '/vendor/autoload.php';

// Load Custom Autoloader and Database singleton
require_once ROOT_PATH . '/Autoloader.php';
require_once ROOT_PATH . '/Database.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Register the custom autoloader
Autoloader::register();

// --- 4. Import Controllers ---

use Controllers\site\AuthController;
use Controllers\site\DashboardController;
use Controllers\site\HelpController;
use Controllers\site\NotFoundController;
use Controllers\site\SaveStudentController;

// Folder Controllers
use Controllers\FolderController\FoldersControllerAdmin;
use Controllers\site\FolderController\FoldersControllerStudent;

// Home Controllers
use Controllers\site\HomeController\HomeControllerAdmin;
use Controllers\site\HomeController\HomeControllerStudent;

// Partners Controllers
use Controllers\PartnersController\PartnersControllerStudent;
use Controllers\PartnersController\PartnersControllerAdmin;

// WebPlan (Sitemap) Controllers
use Controllers\WebPlanController\WebPlanControllerAdmin;
use Controllers\WebPlanController\WebPlanControllerStudent;

// --- 5. Initialize Controllers ---

/**
 * List of available controllers.
 * The order is significant: the router stops at the first controller
 * that confirms it supports the requested page.
 */
$controllers = [
    new AuthController(),

    // Home Controllers (Admin first for security)
    new HomeControllerAdmin(),
    new HomeControllerStudent(),

    // Folder Controllers
    new FoldersControllerAdmin(),
    new FoldersControllerStudent(),

    // Partners Controllers
    new PartnersControllerAdmin(),
    new PartnersControllerStudent(),

    // WebPlan Controllers
    new WebPlanControllerAdmin(),
    new WebPlanControllerStudent(),

    // Generic Controllers
    new DashboardController(),
    new HelpController(),
    new SaveStudentController(),
];

// --- 6. Routing Logic ---

// Retrieve the 'page' parameter from the URL, or default to empty string
$page = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

/**
 * Root Path Redirection Logic.
 * * If the user arrives at the root ('', '/', 'index.php') or the old 'home' route,
 * we automatically redirect them based on their authentication status.
 */
if ($page === '' || $page === 'index.php' || $page === 'home') {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        // User is Admin -> Redirect to Admin Home
        header('Location: index.php?page=home-admin');
        exit;
    } elseif (isset($_SESSION['numetu'])) {
        // User is Student -> Redirect to Student Home
        header('Location: index.php?page=home-student');
        exit;
    } else {
        // User is Guest -> Redirect to Login
        header('Location: index.php?page=login');
        exit;
    }
}

// --- 7. Handle Special Actions ---

// Handle Logout
if ($page === 'logout') {
    // Destroy session and redirect to login
    session_unset();
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// --- 8. Dispatch Request ---

// Loop through controllers to find one that supports the request
foreach ($controllers as $controller) {
    if ($controller::support($page, $_SERVER['REQUEST_METHOD'])) {
        $controller->control();
        exit(); // Stop execution once the controller has handled the request
    }
}

// --- 9. Fallback (404) ---

// If no controller matched, show the 404 Not Found page
$notFound = new NotFoundController();
$notFound->control();
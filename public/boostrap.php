<?php

/**
 * --------------------------------------------------------------------------
 * Application Bootstrap
 * --------------------------------------------------------------------------
 * * This file bootstraps the application by setting up:
 * 1. Global Constants (ROOT_PATH).
 * 2. Autoloading (Composer & Custom).
 * 3. Environment Variables (.env).
 * 4. Error Reporting Configuration.
 * 5. Session Initialization.
 * * It ensures the environment is ready before any request is processed.
 */

// --- 1. Define Global Constants ---

// Define the absolute path to the project root directory
// Adjust dirname levels depending on where this file is located relative to project root
define('ROOT_PATH', dirname(__DIR__)); 

// --- 2. Autoloading ---

// Load Composer's autoloader
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// Load Custom Autoloader and Database Singleton
require_once ROOT_PATH . '/Autoloader.php';
require_once ROOT_PATH . '/Database.php';

// Register the custom autoloader implementation
Autoloader::register();

// --- 3. Environment Configuration (.env) ---

// Load environment variables securely
try {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
} catch (Exception $e) {
    // Log error if .env is missing, but allow execution to proceed (or die in strict mode)
    error_log("Environment configuration missing: " . $e->getMessage());
}

// --- 4. Error Reporting ---

// Configure error visibility based on the 'APP_DEBUG' environment variable
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    // Development Mode: Show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Production Mode: Hide errors from the user
    error_reporting(0);
    ini_set('display_errors', 0);
}

// --- 5. Session Management ---

// Start the PHP session if it hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
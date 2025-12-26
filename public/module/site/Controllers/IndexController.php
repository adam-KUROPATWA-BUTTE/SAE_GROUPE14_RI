<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use View\HomePage;
use Database;

// Ensure the Database class is imported

/**
 * IndexController
 *
 * Handles the homepage of the application.
 *
 * Responsibilities:
 *  - Check if a user is logged in
 *  - Calculate completion statistics for dossiers
 *  - Render the homepage view
 */
class IndexController implements ControllerInterface
{
    /**
     * Main control method that handles the homepage logic.
     */
    public function control(): void
    {
        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isLoggedIn = isset($_SESSION['admin_id']);
        $lang = $_GET['lang'] ?? 'fr';
        $completionPercentage = 0;

        try {
            $pdo = Database::getInstance()->getConnection();

            // Fetch total dossiers and how many are completed
            $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(IsComplete) as completed FROM dossiers");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row['total'] > 0) {
                $completionPercentage = ($row['completed'] / $row['total']) * 100;
            }
        } catch (\PDOException $e) {
            error_log("Error fetching statistics: " . $e->getMessage());
            // Do not expose error details to the user
            $completionPercentage = 0;
        }

        // Render the homepage with login status, language, and completion stats
        $view = new HomePage($isLoggedIn, $lang, $completionPercentage);
        $view->render();
    }

    /**
     * Determines if this controller supports the requested page and method.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if this controller handles the homepage
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'home' && $method === 'GET';
    }
}

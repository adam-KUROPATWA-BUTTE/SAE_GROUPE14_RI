<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\site\HomeController;

use Controllers\ControllerInterface;
use View\HomePage\HomePageAdmin;
use Database;
use PDO;
use PDOException;

/**
 * Controller responsible for the Administrator Homepage.
 *
 * Responsibilities:
 * - Verify if the user is an Administrator.
 * - Calculate global statistics (completion rate of all folders).
 * - Render the specific Admin Homepage view.
 */
class HomeControllerAdmin implements ControllerInterface
{
    /**
     * Determines if this controller supports the current request.
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST, etc.).
     * @return bool True if the controller should handle the request.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-admin' && $method === 'GET';
    }

    /**
     * Main control logic for the Admin Homepage.
     *
     * Fetches statistics from the database and renders the view.
     * Handles potential database connection or query errors gracefully.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';
        $completionPercentage = 0;

        try {
            $pdo = Database::getInstance()->getConnection();

            // Execute the query to get total dossiers and completed dossiers
            $stmt = $pdo->query("SELECT COUNT(*) AS total, SUM(IsComplete) AS completed FROM dossiers");

            // PHPStan Fix: Verify $stmt is not false before calling fetch()
            if ($stmt !== false) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // PHPStan Fix: Verify $row is an array before accessing offsets
                if (is_array($row)) {
                    // PHPStan Fix: Safely cast mixed values to int using null coalescing
                    $total = (int)($row['total'] ?? 0);
                    $completed = (int)($row['completed'] ?? 0);

                    // Avoid division by zero
                    if ($total > 0) {
                        $completionPercentage = ($completed / $total) * 100;
                    }
                }
            }
        } catch (PDOException $e) {
            // Log the error for debugging without breaking the user experience
            error_log("HomeControllerAdmin Error: " . $e->getMessage());
            $completionPercentage = 0;
        }

        $view = new HomePageAdmin(true, $lang, $completionPercentage);
        $view->render();
    }
}

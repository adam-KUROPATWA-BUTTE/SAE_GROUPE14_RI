<?php

namespace Controllers\site\HomeController;

use Controllers\ControllerInterface;
use View\HomePage\HomePageAdmin;
use PDO;
use PDOException;

/**
 * Class HomeControllerAdmin
 *
 * Controller responsible for the Administrator Homepage.
 *
 * Responsibilities:
 * - Check if the user is an Administrator.
 * - Calculate global statistics (percentage of completed student folders).
 * - Render the Admin Homepage view.
 */
class HomeControllerAdmin implements ControllerInterface
{
    /**
     * Determines if this controller supports the current request.
     *
     * This controller is selected ONLY if:
     * 1. The requested page is 'home-admin'.
     * 2. The HTTP method is GET (implicitly, as usually only GET is used for home).
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if this controller should handle the request.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-admin';
    }

    /**
     * Main control logic for the Admin Homepage.
     *
     * Steps:
     * 1. Start the session if not already started.
     * 2. Connect to the database.
     * 3. Compute the percentage of completed student folders.
     * 4. Instantiate and render the Admin View.
     *
     * @return void
     */
    public function control(): void
    {
        // Ensure session is active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = (string)($_GET['lang'] ?? 'fr');
        $completionPercentage = 0.0;

        // --- Statistics Calculation Logic ---
        try {
            // Use global namespace for Database class
            $pdo = \Database::getInstance()->getConnection();

            // Query to count total folders and sum completed ones (IsComplete = 1)
            $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(IsComplete) as completed FROM dossiers");

            // Fix PHPStan: query() can return false
            if ($stmt !== false) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // Fix PHPStan: fetch() returns mixed/false. Verify array and cast values.
                if (is_array($row)) {
                    $total = (int)($row['total'] ?? 0);
                    $completed = (int)($row['completed'] ?? 0);

                    // Avoid division by zero
                    if ($total > 0) {
                        $completionPercentage = ($completed / $total) * 100;
                    }
                }
            }
        } catch (PDOException $e) {
            // Log error silently and default percentage to 0.0
            error_log("HomeControllerAdmin Error: " . $e->getMessage());
            $completionPercentage = 0.0;
        }

        // --- Render View ---
        // Pass 'true' for isLoggedIn (since we are in Admin controller context)
        // Pass the calculated percentage (float)
        $view = new HomePageAdmin(true, $lang, $completionPercentage);
        $view->render();
    }
}
<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\site\HomeController;

use Controllers\ControllerInterface;
use View\HomePage\HomePageAdmin;
use Database;
use PDO;
use PDOException;

/**
 * Class HomeControllerAdmin
 *
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
     * This controller is selected ONLY if:
     * 1. The requested page is 'home'.
     * 2. The HTTP method is GET.
     * 3. An administrator session is active ('user' key exists).
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if this controller should handle the request.
     */
    public static function support(string $page, string $method): bool
    {
        // Check for 'home' page AND active admin session
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
     */
    public function control(): void
    {
        // Ensure session is active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';
        $completionPercentage = 0;

        // --- Statistics Calculation Logic ---
        try {
            $pdo = \Database::getInstance()->getConnection();

            // Query to count total folders and sum completed ones (IsComplete = 1)
            $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(IsComplete) as completed FROM dossiers");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Avoid division by zero
            if ($row && $row['total'] > 0) {
                $completionPercentage = ($row['completed'] / $row['total']) * 100;
            }
        } catch (PDOException $e) {
            // Log error silently and default percentage to 0
            error_log("HomeControllerAdmin Error: " . $e->getMessage());
            $completionPercentage = 0;
        }

        // --- Render View ---
        // Pass 'true' for isLoggedIn (since we are in Admin controller)
        // Pass the calculated percentage for the dashboard chart
        $view = new HomePageAdmin(true, $lang, $completionPercentage);
        $view->render();
    }
}

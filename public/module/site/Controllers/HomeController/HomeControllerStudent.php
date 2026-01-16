<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\site\HomeController;

use Controllers\ControllerInterface;
use View\HomePage\HomePageStudent;

/**
 * Controller responsible for the Student and Visitor Homepage.
 *
 * Responsibilities:
 * - Handle the 'home-student' route for non-admin users.
 * - Check if a student is logged in to adapt the view content.
 * - Render the Student/Public Homepage view.
 */
class HomeControllerStudent implements ControllerInterface
{
    /**
     * Determines if this controller supports the current request.
     *
     * This controller should be registered after HomeControllerAdmin in the router.
     *
     * @param string $page   Requested page identifier.
     * @param string $method HTTP method (GET, POST).
     * @return bool True if the page is 'home-student'.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-student' && $method === 'GET';
    }

    /**
     * Main control logic for the Student/Public Homepage.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';

        // Determine if a student is logged in
        $isStudentLoggedIn = isset($_SESSION['numetu']);

        // Render the view
        $view = new HomePageStudent($isStudentLoggedIn, $lang);
        $view->render();
    }
}

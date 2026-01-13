<?php

namespace Controllers\site\HomeController;

use Controllers\ControllerInterface;
use View\HomePage\HomePageStudent;

/**
 * Class HomeControllerStudent
 *
 * Controller responsible for the Student and Visitor Homepage.
 *
 * Responsibilities:
 * - Handle the 'home-student' route.
 * - Check if a student is logged in (to adapt the view content: Login vs Dashboard button).
 * - Render the Student/Public Homepage view.
 */
class HomeControllerStudent implements ControllerInterface
{
    /**
     * Determines if this controller supports the current request.
     *
     * This controller handles the 'home-student' page, which serves as the landing page
     * for students and unauthenticated visitors.
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if the page is 'home-student'.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-student';
    }

    /**
     * Main control logic for the Student/Public Homepage.
     *
     * Steps:
     * 1. Start the session if not already started.
     * 2. Check if a student session exists ('numetu').
     * 3. Instantiate and render the Student View.
     *
     * @return void
     */
    public function control(): void
    {
        // Ensure session is active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Cast to string to satisfy PHPStan strict typing
        $lang = (string)($_GET['lang'] ?? 'fr');

        // Check if the user is a logged-in student
        // This boolean is passed to the view to toggle "Log in" vs "My Folder" buttons
        $isStudentLoggedIn = isset($_SESSION['numetu']);

        // --- Render View ---
        // We do not pass statistics here, as students don't need global data
        $view = new HomePageStudent($isStudentLoggedIn, $lang);
        $view->render();
    }
}
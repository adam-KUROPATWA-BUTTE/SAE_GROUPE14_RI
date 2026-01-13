<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use View\Partners\PartnersPageStudent;

/**
 * Class PartnersControllerStudent
 *
 * Controller responsible for displaying the partner universities page for students.
 * This is a read-only view allowing students to browse available international partnerships.
 */
class PartnersControllerStudent implements ControllerInterface
{
    /**
     * Determines if this controller supports the current request.
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if this controller should handle the request.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-student';
    }

    /**
     * Main control logic.
     *
     * Steps:
     * 1. Start the session if not already active.
     * 2. Determine the current language.
     * 3. Instantiate and render the student-specific partners view.
     *
     * @return void
     */
    public function control(): void
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve current language with strict type casting for PHPStan
        $lang = (string)($_GET['lang'] ?? 'fr');

        // Set page title based on language
        $title = ($lang === 'en') ? 'Partner Universities' : 'Universités Partenaires';

        // Instantiate and render the view
        $view = new PartnersPageStudent($title, $lang);
        $view->render();
    }
}
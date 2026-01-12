<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use View\Partners\PartnersPageStudent;

/**
 * Class PartnersControllerStudent
 *
 * Controller responsible for displaying the partner universities
 * page for students.
 */
class PartnersControllerStudent implements ControllerInterface
{
    /**
     * Main controller logic.
     *
     * - Starts the session if necessary
     * - Determines the current language
     * - Creates and renders the student partners view
     *
     * @return void
     */
    public function control(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve current language (default: French)
        $lang = $_GET['lang'] ?? 'fr';

        // Set page title based on language
        $title = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';

        // Instantiate and render the student view
        $view = new PartnersPageStudent($title, $lang);
        $view->render();
    }

    /**
     * Checks whether this controller supports the given page and HTTP method.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if this controller supports the page, false otherwise
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-student';
    }
}

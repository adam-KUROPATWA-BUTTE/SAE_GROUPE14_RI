<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Help;
use View\HelpPage;

/**
 * Class HelpController
 *
 * Controller responsible for handling the Help / FAQ page.
 *
 * Responsibilities:
 * - Retrieve Frequently Asked Questions (FAQ) data from the Model.
 * - Instantiate and render the Help page View.
 */
class HelpController implements ControllerInterface
{
    /**
     * Determines if the controller supports the requested page and HTTP method.
     *
     * @param string $page   The requested page identifier (e.g., 'help').
     * @param string $method The HTTP method (e.g., 'GET').
     * @return bool True if the controller supports the request, false otherwise.
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['help', '/help'], true);
    }

    /**
     * Main control method.
     * Fetches data from the Help model and renders the view.
     *
     * @return void
     */
    public function control(): void
    {
        // Retrieve FAQ data from the Model (separation of concerns)
        // Ensure Model\Help exists and has the getFaq method
        $faq = class_exists(Help::class) ? Help::getFaq() : [];

        // Check if the View class exists before instantiating it
        // This prevents fatal errors if the View file is missing (as flagged by PHPStan)
        if (class_exists('View\HelpPage')) {
            $view = new HelpPage($faq);
            $view->render();
        } else {
            // Fallback for debugging if the View is missing
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            echo "<h1>Help Page</h1>";
            echo "<p>The View class 'View\HelpPage' was not found.</p>";
            echo "<pre>" . print_r($faq, true) . "</pre>";
        }
    }
}
<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use View\NotFoundPage;

/**
 * Class NotFoundController
 *
 * Handles 404 errors when a requested route does not correspond to any existing controller.
 * It acts as a fallback controller to display a user-friendly "Page Not Found" view.
 */
class NotFoundController implements ControllerInterface
{
    /**
     * Determines if this controller supports the requested page.
     *
     * Since this is a fallback controller used when no other controller matches,
     * this method always returns false. It is instantiated manually by the Router/Index.
     *
     * @param string $page   The requested page name.
     * @param string $method The HTTP method (GET, POST).
     * @return bool Always false.
     */
    public static function support(string $page, string $method): bool
    {
        return false;
    }

    /**
     * Main control method.
     * Sets the 404 HTTP header and renders the error view.
     *
     * @return void
     */
    public function control(): void
    {
        // Send proper 404 HTTP header
        http_response_code(404);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if the View class exists before instantiating
        // This avoids fatal errors if the View file is missing during development
        if (class_exists('View\NotFoundPage')) {
            $view = new NotFoundPage('Page Not Found');
            if (method_exists($view, 'render')) {
                $view->render();
            }
        } else {
            // Fallback text output
            echo "<h1>404 - Page Not Found</h1>";
            echo "<p>The requested resource could not be found.</p>";
        }
    }
}
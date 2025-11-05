<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\NotFoundPage;

/**
 * NotFoundController
 * 
 * Handles 404 pages when a requested route does not exist.
 * 
 * Responsibilities:
 *  - Render a "Page Not Found" view
 *  - Does not support any specific route
 */
class NotFoundController implements ControllerInterface
{
    /**
     * Main control method to render the 404 page.
     */
    public function control(): void
    {
        $view = new NotFoundPage('Page not found');
        $view->render();
    }

    /**
     * Determines if this controller supports the requested page and method.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool Always returns false because this controller is a fallback
     */
    public static function support(string $page, string $method): bool
    {
        // This controller does not handle any specific route
        return false;
    }
}

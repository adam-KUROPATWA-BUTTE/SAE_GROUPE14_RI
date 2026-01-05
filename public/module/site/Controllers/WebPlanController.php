<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\WebPlanPage;
use Model\WebPlan;

/**
 * WebPlanController
 *
 * Handles the "Web Plan" page of the application.
 *
 * Responsibilities:
 *  - Fetch links or items from the WebPlan model
 *  - Render the web plan page view
 *  - Support multilingual display based on 'lang' parameter
 */
class WebPlanController implements ControllerInterface
{
    /**
     * Main control method that retrieves data and renders the web plan page.
     */
    public function control(): void
    {
        // Get language from query parameter (default to French)
        $lang = $_GET['lang'] ?? 'fr';

        // Retrieve links or sections from the WebPlan model
        $links = WebPlan::getLinks();

        // Render the WebPlanPage view
        $view = new WebPlanPage($links, $lang);
        $view->render();
    }

    /**
     * Determines if this controller supports the requested page.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if this controller handles the web_plan page
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan';
    }
}

<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use View\WebPlan\WebPlanPageAdmin;
use Model\WebPlan;

/**
 * Class WebPlanControllerAdmin
 *
 * Controller responsible for displaying the website plan (sitemap)
 * for administrators.
 */
class WebPlanControllerAdmin implements ControllerInterface
{
    /**
     * Main controller logic.
     *
     * - Retrieves the current language
     * - Fetches admin-specific sitemap links
     * - Creates and renders the admin sitemap view
     *
     * @return void
     */
    public function control(): void
    {
        // Retrieve current language (default: French)
        $lang = $_GET['lang'] ?? 'fr';

        // Get sitemap links available for administrators
        $links = WebPlan::getLinksAdmin();

        // Instantiate and render the admin sitemap view
        $view = new WebPlanPageAdmin($links, $lang);
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
        return $page === 'web_plan-admin';
    }
}

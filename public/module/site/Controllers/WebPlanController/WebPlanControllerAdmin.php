<?php

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
     * Checks whether this controller supports the given page and HTTP method.
     *
     * @param string $page   The requested page identifier.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if this controller supports the page.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan-admin';
    }

    /**
     * Main controller logic.
     *
     * Steps:
     * 1. Ensure the session is started.
     * 2. Retrieve the current language.
     * 3. Fetch admin-specific sitemap links from the Model.
     * 4. Render the admin sitemap view.
     *
     * @return void
     */
    public function control(): void
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve current language (default: French) with strict casting
        $lang = (string)($_GET['lang'] ?? 'fr');

        // Get sitemap links available for administrators from the Model
        $links = WebPlan::getLinksAdmin();

        // Instantiate and render the admin sitemap view
        $view = new WebPlanPageAdmin($links, $lang);
        $view->render();
    }
}
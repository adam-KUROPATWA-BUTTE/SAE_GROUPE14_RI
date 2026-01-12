<?php

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use View\WebPlan\WebPLanPageStudent;
use Model\WebPlan;

/**
 * Class WebPlanControllerStudent
 *
 * Controller responsible for displaying the website plan (sitemap)
 * for students.
 */
class WebPlanControllerStudent implements ControllerInterface
{
    /**
     * Main controller logic.
     *
     * - Retrieves the current language
     * - Fetches student-accessible sitemap links
     * - Creates and renders the student sitemap view
     *
     * @return void
     */
    public function control(): void
    {
        // Retrieve current language (default: French)
        $lang = $_GET['lang'] ?? 'fr';

        // Get sitemap links available for students
        // You can further filter links here if needed
        $links = WebPlan::getLinksStudent();

        // Instantiate and render the student sitemap view
        $view = new WebPlanPageStudent($links, $lang);
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
        return $page === 'web_plan-student';
    }
}

<?php

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use View\WebPlan\WebPlanPageStudent;
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
     * Checks whether this controller supports the given page and HTTP method.
     *
     * @param string $page   The requested page identifier.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if this controller supports the page.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan-student';
    }

    /**
     * Main controller logic.
     *
     * Steps:
     * 1. Ensure the session is started.
     * 2. Retrieve the current language.
     * 3. Fetch student-specific sitemap links from the Model.
     * 4. Render the student sitemap view.
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

        // Get sitemap links available for students
        $links = WebPlan::getLinksStudent();

        // Instantiate and render the student sitemap view
        // Note: Corrected class name casing to WebPlanPageStudent
        $view = new WebPlanPageStudent($links, $lang);
        $view->render();
    }
}
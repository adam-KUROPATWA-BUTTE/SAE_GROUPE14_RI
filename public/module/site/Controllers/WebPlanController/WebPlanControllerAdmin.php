<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use View\WebPlan\WebPlanPageAdmin;
use Model\WebPlan;

class WebPlanControllerAdmin implements ControllerInterface
{
    public function control(): void
    {
        $lang = $_GET['lang'] ?? 'fr';

        $links = WebPlan::getLinksAdmin();

        $view = new WebPlanPageAdmin($links, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan-admin';
    }
}

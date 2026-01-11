<?php

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use View\WebPlan\WebPLanPageStudent;
use Model\WebPlan;

class WebPlanControllerStudent implements ControllerInterface
{
    public function control(): void
    {
        $lang = $_GET['lang'] ?? 'fr';
        $links = WebPlan::getLinksStudent(); // tu peux filtrer les liens pour student si besoin
        $view = new WebPlanPageStudent($links, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan-student';
    }
}
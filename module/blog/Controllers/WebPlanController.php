<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;
use View\WebPlanPage;
use Model\WebPlan;

class WebPlanController implements ControllerInterface
{
    public function control(): void
    {
        $lang = $_GET['lang'] ?? 'fr';

        // Récupère les liens depuis le modèle WebPlan
        $links = WebPlan::getLinks();

        $view = new WebPlanPage($links, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan';
    }
}

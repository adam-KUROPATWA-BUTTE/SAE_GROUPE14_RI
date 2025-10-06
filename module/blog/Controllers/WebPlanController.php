<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;
use Model\WebPlan; // Assure-toi que le modèle est bien namespace

class WebPlanController implements ControllerInterface
{
    // Méthode principale appelée par le routeur
    public function control()
    {
        $links = WebPlan::getLinks();
        require ROOT_PATH . '/module/blog/View/web_plan.php';
    }

    // Méthode obligatoire pour le routeur
    public static function support(string $page, string $method): bool
{
    return $page === 'web_plan';
}



}

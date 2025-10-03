<?php
require_once __DIR__ . '/../Model/WebPlan.php';

class WebPlanController
{
    public function index()
    {
        $links = WebPlan::getLinks();
        require __DIR__ . '/../View/web_plan.php';
    }
}
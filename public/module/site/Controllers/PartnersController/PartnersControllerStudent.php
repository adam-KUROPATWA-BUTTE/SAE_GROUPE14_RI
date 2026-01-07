<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use View\Partners\PartnersPageStudent;

class PartnersControllerStudent implements ControllerInterface
{
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';
        $titre = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';

        $view = new PartnersPageStudent($titre, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-student';
    }
}

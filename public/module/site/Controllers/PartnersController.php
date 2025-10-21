<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\PartnersPage;

class PartnersController implements ControllerInterface
{
    public function control(): void
    {
        $lang = $_GET['lang'] ?? 'fr';
        $titre = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';

        $view = new PartnersPage($titre, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'partners';
    }
}

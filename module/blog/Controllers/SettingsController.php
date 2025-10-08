<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;
use View\SettingsPage;
use Model\Universite;
use Model\Campagne;
use Model\Destination;
use Model\Partenaire;

class SettingsController implements ControllerInterface
{
    public function control(): void
    {
        $type = $_GET['type'] ?? 'universites';

        switch ($type) {
            case 'campagnes':
                $data = Campagne::getAll();
                $titre = "Campagnes";
                break;
            case 'partenaires':
                $data = Partenaire::getAll();
                $titre = "Partenaires";
                break;
            case 'destinations':
                $data = Destination::getAll();
                $titre = "Destinations";
                break;
            default:
                $data = Universite::getAll();
                $titre = "Paramètrage";
        }

        $view = new SettingsPage($titre, $data);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'settings';
    }
}

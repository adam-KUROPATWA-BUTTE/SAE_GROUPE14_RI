<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;
use Model\Universite; // <-- important !
use Model\Campagne;
use Model\Destination;
use Model\Partenaire;

class SettingsController implements ControllerInterface
{
    public function control()
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

        require __DIR__ . '/../View/settings.php';
    }

    public static function support(string $page, string $method): bool
{
    return $page === 'settings';
}

}

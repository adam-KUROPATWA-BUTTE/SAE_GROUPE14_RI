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
        $lang = $_GET['lang'] ?? 'fr';

        switch ($type) {
            case 'campagnes':
                $data = Campagne::getAll();
                $titre = $lang === 'en' ? 'Campaigns' : 'Campagnes';
                break;
            case 'partenaires':
                $data = Partenaire::getAll();
                $titre = $lang === 'en' ? 'Partners' : 'Partenaires';
                break;
            case 'destinations':
                $data = Destination::getAll();
                $titre = $lang === 'en' ? 'Destinations' : 'Destinations';
                break;
            default:
                $data = Universite::getAll();
                $titre = $lang === 'en' ? 'Settings' : 'Paramétrage';
        }

        $view = new SettingsPage($titre, $data, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'settings';
    }
}

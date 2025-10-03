<?php

require_once __DIR__ . '/../Model/Universite.php';
require_once __DIR__ . '/../Model/Campagne.php';
require_once __DIR__ . '/../Model/Partenaire.php';
require_once __DIR__ . '/../Model/Destination.php';
class SettingsController
{
    public function index()
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

}
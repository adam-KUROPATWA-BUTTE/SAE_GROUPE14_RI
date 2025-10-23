<?php
namespace Model;

class WebPlan
{
    public static function getLinks()
    {
        return [
            ['url' => '/', 'label' => 'Accueil'],
            ['url' => '/dashboard', 'label' => 'Tableau de bord'],
            ['url' => '/partners', 'label' => 'Partenaires'],
            ['url' => '/folders', 'label' => 'Dossiers'],
            ['url' => '/web_plan', 'label' => 'Plan du site'],
            ['url' => '/login', 'label' => 'Connexion / Inscription'],
        ];
    }
}

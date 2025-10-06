<?php
namespace Model;

class WebPlan
{
    public static function getLinks()
    {
        return [
            ['url' => 'index.php?page=home', 'label' => 'Accueil'],
            ['url' => 'index.php?page=dashboard', 'label' => 'Tableau de bord'],
            ['url' => 'index.php?page=settings', 'label' => 'Paramétrage'],
            ['url' => 'index.php?page=folders', 'label' => 'Dossiers'],
            ['url' => 'index.php?page=help', 'label' => 'Aide'],
            ['url' => 'index.php?page=web_plan', 'label' => 'Plan du site'],
            ['url' => 'index.php?page=login', 'label' => 'Connexion / Inscription'],
        ];
    }
}

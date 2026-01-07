<?php
namespace Model;

class WebPlan
{
    public static function getLinks(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $links = [
            ['url' => 'index.php?page=home', 'label' => 'Accueil'],
            ['url' => 'index.php?page=dashboard-admin', 'label' => 'Tableau de bord'],
            ['url' => 'index.php?page=partners', 'label' => 'Partenaires'],
            ['url' => 'index.php?page=folders', 'label' => 'Dossiers'],
            ['url' => 'index.php?page=login', 'label' => 'Connexion'],

        ];

        return $links;
    }
}

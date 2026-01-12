<?php

// phpcs:disable Generic.Files.LineLength

namespace Model;

class WebPlan
{
    public static function getLinksAdmin(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return [
            ['url' => 'index.php?page=home', 'label' => 'Accueil'],
            ['url' => 'index.php?page=dashboard-admin', 'label' => 'Tableau de bord'],
            ['url' => 'index.php?page=partners-admin', 'label' => 'Partenaires'],
            ['url' => 'index.php?page=folders-admin', 'label' => 'Dossier'],
            ['url' => 'index.php?page=logout', 'label' => 'Déconnexion'],
        ];
    }

    public static function getLinksStudent(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return [
            ['url' => 'index.php?page=home', 'label' => 'Accueil'],
            ['url' => 'index.php?page=dashboard-student', 'label' => 'Mon Tableau de bord'],
            ['url' => 'index.php?page=partners-student', 'label' => 'Partenaires'],
            ['url' => 'index.php?page=folders-student', 'label' => 'Mon Dossier'],
            ['url' => 'index.php?page=logout', 'label' => 'Déconnexion'],
        ];
    }
}

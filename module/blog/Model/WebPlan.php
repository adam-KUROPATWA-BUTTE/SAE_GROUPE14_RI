<?php

class WebPlan
{
    public static function getLinks()
    {
        return [
            ['url' => 'index.php', 'label' => 'Accueil'],
            ['url' => 'dashboard.php', 'label' => 'Tableau de bord'],
            ['url' => 'settings.php', 'label' => 'Paramètrage'],
            ['url' => 'folders.php', 'label' => 'Dossiers'],
            ['url' => 'help.php', 'label' => 'Aide'],
            ['url' => 'web_plan.php', 'label' => 'Plan du site'],
            ['url' => 'login.php', 'label' => 'Connexion / Inscription'],
        ];
    }
}
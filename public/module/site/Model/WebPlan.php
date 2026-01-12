<?php

namespace Model;

/**
 * Class WebPlan
 *
 * Model responsible for providing sitemap (website plan)
 * links depending on the user role (admin or student).
 */
class WebPlan
{
    /**
     * Returns the list of sitemap links available for administrators.
     *
     * @return array List of admin sitemap links (url + label)
     */
    public static function getLinksAdmin(): array
    {
        // Ensure the session is started
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

    /**
     * Returns the list of sitemap links available for students.
     *
     * @return array List of student sitemap links (url + label)
     */
    public static function getLinksStudent(): array
    {
        // Ensure the session is started
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

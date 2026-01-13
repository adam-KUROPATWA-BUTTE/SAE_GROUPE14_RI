<?php

namespace Model;

/**
 * Class WebPlan
 *
 * Model responsible for generating the sitemap (website plan) links.
 * It provides distinct navigation structures based on the user role (Administrator vs Student).
 */
class WebPlan
{
    /**
     * Returns the list of sitemap links available for administrators.
     *
     * @return array<int, array{url: string, label: string}> List of admin sitemap links.
     */
    public static function getLinksAdmin(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return [
            ['url' => 'index.php?page=home-admin', 'label' => 'Home'],
            ['url' => 'index.php?page=dashboard-admin', 'label' => 'Dashboard'],
            ['url' => 'index.php?page=partners-admin', 'label' => 'Partners'],
            ['url' => 'index.php?page=folders-admin', 'label' => 'Folders Management'],
            ['url' => 'index.php?page=logout', 'label' => 'Logout'],
        ];
    }

    /**
     * Returns the list of sitemap links available for students.
     *
     * @return array<int, array{url: string, label: string}> List of student sitemap links.
     */
    public static function getLinksStudent(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return [
            ['url' => 'index.php?page=home-student', 'label' => 'Home'],
            ['url' => 'index.php?page=dashboard-student', 'label' => 'My Dashboard'],
            ['url' => 'index.php?page=partners-student', 'label' => 'Partners'],
            ['url' => 'index.php?page=folders-student', 'label' => 'My Folder'],
            ['url' => 'index.php?page=logout', 'label' => 'Logout'],
        ];
    }
}
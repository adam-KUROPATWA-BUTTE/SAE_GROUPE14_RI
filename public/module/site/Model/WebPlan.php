<?php

namespace Model;

class WebPlan
{
    /**
     * Retrieve a list of main site links for the web plan / sitemap.
     *
     * Each link contains:
     * - url: string The path or URL of the page
     * - label: string The display name of the link
     *
     * @return array List of links
     */
    public static function getLinks()
    {
        return [
            ['url' => '/', 'label' => 'Home'],
            ['url' => '/dashboard', 'label' => 'Dashboard'],
            ['url' => '/partners', 'label' => 'Partners'],
            ['url' => '/folders', 'label' => 'Folders'],
            ['url' => '/web_plan', 'label' => 'Site Map'],
            ['url' => '/login', 'label' => 'Login / Register'],
        ];
    }
}

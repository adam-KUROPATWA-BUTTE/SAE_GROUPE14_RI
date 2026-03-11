<?php

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use Core\View;

class WebPlanControllerStudent implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan-student' && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['numetu'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = $_SESSION['lang'] ?? 'fr';

        $t = function(array $translations) use ($lang) {
            return $translations[$lang] ?? $translations['fr'];
        };

        $buildUrl = function(string $path, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            $url = 'index.php?page=' . $path;
            foreach ($params as $key => $value) {
                $url .= '&' . urlencode($key) . '=' . urlencode($value);
            }
            return $url;
        };

        // Fonction de traduction des labels
        $translateLabel = function(string $label) use ($lang) {
            $translations = [
                'Accueil' => 'Home',
                'Mon Tableau de bord' => 'My Dashboard',
                'Partenaires' => 'Partners',
                'Mon Dossier' => 'My Folder',
                'Contact' => 'Contact',
                'Plan du site' => 'Site Map',
            ];
            return $lang === 'en' ? ($translations[$label] ?? $label) : $label;
        };

        // Liste des liens pour les étudiants
        $links = [
            ['url' => 'home-student', 'label' => 'Accueil'],
            ['url' => 'dashboard-student', 'label' => 'Mon Tableau de bord'],
            ['url' => 'partners-student', 'label' => 'Partenaires'],
            ['url' => 'folders-student', 'label' => 'Mon Dossier'],
            ['url' => 'contact-student', 'label' => 'Contact'],
        ];

        // Utiliser View::render
        View::render('WebPlan/web_plan_student', [
            'lang' => $lang,
            't' => $t,
            'buildUrl' => $buildUrl,
            'links' => $links,
            'translateLabel' => $translateLabel
        ]);
    }
}
<?php

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use Core\View;

class WebPlanControllerAdmin implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan-admin' && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier l'authentification admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = $_SESSION['lang'] ?? 'fr';

        // Fonction de traduction
        $t = function(array $translations) use ($lang) {
            return $translations[$lang] ?? $translations['fr'];
        };

        // Construction d'URL
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
                'Tableau de bord' => 'Dashboard',
                'Partenaires' => 'Partners',
                'Dossiers' => 'Folders',
                'Plan du site' => 'Site Map',
            ];
            return $lang === 'en' ? ($translations[$label] ?? $label) : $label;
        };

        // Liste des liens pour les admins
        $links = [
            ['url' => 'home-admin', 'label' => 'Accueil'],
            ['url' => 'dashboard-admin', 'label' => 'Tableau de bord'],
            ['url' => 'partners-admin', 'label' => 'Partenaires'],
            ['url' => 'folders-admin', 'label' => 'Dossiers'],
            ['url' => 'messages-admin', 'label' => 'Messages'],
        ];

        // Utiliser View::render au lieu de require_once
        View::render('WebPlan/web_plan_admin', [
            'lang' => $lang,
            't' => $t,
            'buildUrl' => $buildUrl,
            'links' => $links,
            'translateLabel' => $translateLabel
        ]);
    }
}

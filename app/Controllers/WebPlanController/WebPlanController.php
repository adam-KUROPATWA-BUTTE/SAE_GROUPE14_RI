<?php

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;

/**
 * Contrôleur de redirection pour le plan du site
 * Redirige vers la version admin ou student selon le rôle
 */
class WebPlanController implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan' && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';

        $adminRoles = ['admin', 'super_admin', 'coordinateur', 'coordinateur_etude', 'coordinateur_stage', 'chef_departement'];

        // Redirection selon le rôle
        if (isset($_SESSION['role']) && in_array($_SESSION['role'], $adminRoles, true)) {
            header('Location: index.php?page=web_plan-admin&lang=' . urlencode($lang));
            exit;
        } elseif (isset($_SESSION['numetu'])) {
            header('Location: index.php?page=web_plan-student&lang=' . urlencode($lang));
            exit;
        } else {
            // Utilisateur non connecté -> redirection vers login
            header('Location: index.php?page=login');
            exit;
        }
    }
}
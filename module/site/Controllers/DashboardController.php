<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Folder;
use View\DashboardPage;

class DashboardController implements ControllerInterface
{
    public function control(): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /login');
            exit;
        }

        // Récupérer la langue depuis l'URL ou défaut 'fr'
        $lang = $_GET['lang'] ?? 'fr';

        // Récupérer les dossiers incomplets
        $dossiers = Folder::getDossiersIncomplets();

        // Créer et afficher la page avec la langue
        $page = new DashboardPage($dossiers, $lang);
        $page->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'dashboard' && $method === 'GET';
    }
}

<?php
namespace Controllers\Blog;


use Controllers\ControllerInterface;
use Model\Dossiers;
use View\DashBoardPage;

class DashboardController implements ControllerInterface 

{
    public function control()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /login');
            exit;
        }

         // Récupérer les dossiers incomplets
        $dossiers = Dossier::getDossiersIncomplets();
        
        // Créer et afficher la page
        $page = new DashboardPage($dossiers);
        $page->render();
    }

    public static function support(string $page, string $method): bool
{
    return $page === 'dashboard';
}



}
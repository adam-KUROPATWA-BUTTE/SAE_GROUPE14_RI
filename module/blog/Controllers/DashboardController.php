<?php
namespace Controllers\Blog;


use Controllers\ControllerInterface;

class DashboardController implements ControllerInterface 

{
    public function control()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /login');
            exit;
        }

        require ROOT_PATH . '/module/blog/Model/dashboard.php';


        $dossiers = \Dossier::getDossiersIncomplets();
        require ROOT_PATH . '/module/blog/View/dashboard.php';

;
    }

    public static function support(string $page, string $method): bool
{
    return $page === 'dashboard';
}



}
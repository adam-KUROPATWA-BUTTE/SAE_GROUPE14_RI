<?php
namespace Controllers\site;

require_once ROOT_PATH . '/public/module/site/Model/Folder/FolderAdmin.php';
require_once ROOT_PATH . '/public/module/site/Model/Folder/FolderStudent.php';

use Controllers\ControllerInterface;

class DashboardController implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['dashboard-admin', 'dashboard-student']) && $method === 'GET';
    }

    public function control(): void
    {
        $page = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        if ($page === 'dashboard-admin') {
            $this->showAdminDashboard();
        } elseif ($page === 'dashboard-student') {
            $this->showStudentDashboard();
        }
    }

    private function showAdminDashboard(): void
    {
        // Vérifier que l'utilisateur est admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        if (!isset($_SESSION['admin_id'])) {
            header('Location: /login');
            exit();
        }

        // Récupérer la langue
        $lang = $_GET['lang'] ?? 'fr';

        // Utiliser le nom de classe complet avec namespace
        $dossiers = \Model\Folder\FolderAdmin::getDossiersIncomplets() ?? [];

        // Charger et afficher la vue
        require_once ROOT_PATH . '/public/module/site/View/Dashboard/DashboardPageAdmin.php';
        $dashboardPage = new \View\DashboardPageAdmin($dossiers, $lang);
        $dashboardPage->render();
    }

    private function showStudentDashboard(): void
    {
        // Vérifier que l'utilisateur est étudiant
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
            header('Location: /login');
            exit();
        }

        if (!isset($_SESSION['etudiant_id'])) {
            header('Location: /login');
            exit();
        }

        $lang = $_GET['lang'] ?? 'fr';

        // Utiliser le nom de classe complet avec namespace
        $dossier = \Model\FolderStudent::getDossierByEtudiantId($_SESSION['etudiant_id']);

        // Charger et afficher la vue
        require_once ROOT_PATH . '/public/module/site/View/Dashboard/DashboardPageStudent.php';
        $dashboardPage = new \View\DashboardPageStudent($dossier, $lang);
        $dashboardPage->render();
    }
}
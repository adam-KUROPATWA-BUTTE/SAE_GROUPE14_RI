<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Folder\FolderAdmin;
use Model\Folder\FolderStudent;
use View\Dashboard\DashboardPageAdmin;
use View\Dashboard\DashboardPageStudent;

class DashboardController implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['dashboard-admin', 'dashboard-student']) && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        if ($page === 'dashboard-admin') {
            $this->showAdminDashboard();
        } elseif ($page === 'dashboard-student') {
            $this->showStudentDashboard();
        } else {
            http_response_code(404);
            echo "Page not found";
        }
    }

    private function showAdminDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        $dossiers = FolderAdmin::getDossiersIncomplets();
        $page = new DashboardPageAdmin($dossiers, $lang);
        $page->render();
    }

    private function showStudentDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
            header('Location: /login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        $dossier = FolderStudent::getMyFolder($_SESSION['etudiant_id'] ?? 0);

        $page = new DashboardPageStudent($dossier, $lang);
        $page->render();
    }
}

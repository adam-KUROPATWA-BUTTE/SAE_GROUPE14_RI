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
        return in_array($page, ['dashboard-admin', 'dashboard-student'], true) && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = $_GET['page'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $page = is_string($page) ? trim($page, '/') : '';

        switch ($page) {
            case 'dashboard-admin':
                $this->showAdminDashboard();
                break;

            case 'dashboard-student':
                $this->showStudentDashboard();
                break;

            default:
                http_response_code(404);
                echo "Page not found";
                break;
        }
    }

    private function showAdminDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login'); // ✅ Aussi corrigé le chemin
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        $lang = is_string($lang) ? $lang : 'fr';

        $folders = FolderAdmin::getAll();
        if (!is_array($folders)) {
            $folders = [];
        }

        $page = new DashboardPageAdmin($folders, $lang);
        $page->render();
    }

    private function showStudentDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login'); // ✅ Aussi corrigé le chemin
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        $lang = is_string($lang) ? $lang : 'fr';

        if (!isset($_SESSION['numetu'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $numetu = $_SESSION['numetu'];
        $folder = FolderStudent::getStudentDetails($numetu);

        if (!is_array($folder)) {
            $folder = [];
        }

        $page = new DashboardPageStudent($folder, $lang);
        $page->render();
    }
}

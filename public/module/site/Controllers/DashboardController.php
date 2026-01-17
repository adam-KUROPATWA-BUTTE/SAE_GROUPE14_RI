<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Folder\FolderAdmin;
use Model\Folder\FolderStudent;
use View\Dashboard\DashboardPageAdmin;
use View\Dashboard\DashboardPageStudent;

/**
 * Class DashboardController
 *
 * Handles the logic for rendering the dashboard pages.
 * Routes requests to Admin or Student dashboards based on the user's role.
 */
class DashboardController implements ControllerInterface
{
    /**
     * Checks if the controller supports the current request.
     *
     * @param string $page   The requested page name.
     * @param string $method The HTTP method (GET, POST, etc.).
     * @return bool True if supported, False otherwise.
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['dashboard-admin', 'dashboard-student'], true) && $method === 'GET';
    }

    /**
     * Main control method.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupère la page depuis GET ou depuis l'URL
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

    /**
     * Renders the Admin Dashboard.
     */
    private function showAdminDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
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

    /**
     * Renders the Student Dashboard.
     */
    private function showStudentDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
            header('Location: /login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        $lang = is_string($lang) ? $lang : 'fr';

        $studentId = $_SESSION['etudiant_id'] ?? 0;
        $folder = FolderStudent::getMyFolder((int)$studentId);
        if (!is_array($folder)) {
            $folder = [];
        }

        $page = new DashboardPageStudent($folder, $lang);
        $page->render();
    }
}

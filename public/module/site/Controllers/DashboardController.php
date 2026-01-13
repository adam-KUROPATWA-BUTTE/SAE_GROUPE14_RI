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
 * It routes requests to either the Admin dashboard or the Student dashboard
 * based on the user's role.
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
        return in_array($page, ['dashboard-admin', 'dashboard-student']) && $method === 'GET';
    }

    /**
     * Main control method.
     * Starts the session and dispatches the request to the specific dashboard method.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Determine the page from GET param or URL path
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

    /**
     * Renders the Admin Dashboard.
     * * Requirement: User must have 'admin' role.
     * Data: Fetches ALL folders (complete and incomplete) to provide a global view.
     */
    private function showAdminDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';

        $folders = FolderAdmin::getAll();

        $page = new DashboardPageAdmin($folders, $lang);
        $page->render();
    }

    /**
     * Renders the Student Dashboard.
     * * Requirement: User must have 'etudiant' role.
     * Data: Fetches only the connected student's folder.
     */
    private function showStudentDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
            header('Location: /login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        $studentId = $_SESSION['etudiant_id'] ?? 0;

        $folder = FolderStudent::getMyFolder($studentId);

        $page = new DashboardPageStudent($folder, $lang);
        $page->render();
    }
}

<?php

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
        return in_array($page, ['dashboard-admin', 'dashboard-student'], true) && $method === 'GET';
    }

    /**
     * Main control method.
     * Starts the session and dispatches the request to the specific dashboard method.
     *
     * @return void
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Determine the page from GET param or URL path
        // Fix: cast parse_url result to string to avoid null/false issues
        $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $page = $_GET['page'] ?? trim((string)$uriPath, '/');

        if ($page === 'dashboard-admin') {
            $this->showAdminDashboard();
        } elseif ($page === 'dashboard-student') {
            $this->showStudentDashboard();
        } else {
            // Fallback redirection if routing fails
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Renders the Admin Dashboard.
     * Requirements: User must have 'admin' role.
     * Data: Fetches ALL folders to provide a global view.
     */
    private function showAdminDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';

        // Retrieve all folders via the Admin Model
        $folders = FolderAdmin::getAll();

        $view = new DashboardPageAdmin($folders, (string)$lang);
        $view->render();
    }

    /**
     * Renders the Student Dashboard.
     * Requirements: User must have 'etudiant' role.
     * Data: Fetches only the connected student's folder.
     */
    private function showStudentDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        
        // Retrieve NumEtu from session (set during login in UserStudent::login)
        $numEtu = $_SESSION['numetu'] ?? '';

        // Fetch student details
        // Fix: Used getStudentDetails() with numetu instead of getMyFolder($id)
        // to match the existing FolderStudent model method.
        $folder = FolderStudent::getStudentDetails((string)$numEtu);

        $view = new DashboardPageStudent($folder, (string)$lang);
        $view->render();
    }
}
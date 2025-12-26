<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Folder\FolderAdmin;
use Model\Folder\FolderStudent;
use View\Dashboard\DashboardPageAdmin;
use View\Dashboard\DashboardPageStudent;

/**
 * DashboardController
 *
 * Handles rendering dashboards for both admin and student users.
 *
 * Responsibilities:
 *  - Display admin dashboard with incomplete folders
 *  - Display student dashboard with the user's own folder
 *  - Enforce role-based access control
 */
class DashboardController implements ControllerInterface
{
    /**
     * Determines if this controller supports the requested page and method.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if the page is handled by this controller
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['dashboard-admin', 'dashboard-student']) && $method === 'GET';
    }

    /**
     * Main control method to handle request logic.
     * Routes to the appropriate dashboard based on the page parameter.
     */
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

    /**
     * Displays the admin dashboard.
     * Ensures the user is an admin and fetches incomplete folders.
     */
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

    /**
     * Displays the student dashboard.
     * Ensures the user is a student and fetches their own folder.
     */
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

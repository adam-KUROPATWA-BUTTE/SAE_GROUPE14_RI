<?php

namespace Controllers\FolderController;

use Controllers\ControllerInterface;
use Model\Folder\FolderAdmin;
use View\Folder\FoldersPageAdmin;

/**
 * Class FoldersControllerAdmin
 *
 * Controller responsible for the administrative management of student folders.
 * It handles the listing, creation, modification, validation status, and advanced filtering of student files.
 */
class FoldersControllerAdmin implements ControllerInterface
{
    /**
     * Determines if the controller supports the requested page.
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if the controller should handle the request.
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders', 'save_student', 'folders-admin', 'toggle_complete', 'update_student'], true);
    }

    /**
     * Main control logic.
     * Handles authentication check, routing logic, data retrieval, and view rendering.
     *
     * @return void
     */
    public function control(): void
    {
        // Ensure session is started for flash messages
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Security Check: Only Admins allowed
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }

        // Retrieve parameters with default values
        $page = $_GET['page'] ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang = $_GET['lang'] ?? 'fr';

        // --- Action: Toggle Folder Completion Status ---
        if ($page === 'toggle_complete') {
            $this->handleToggleComplete((string)$lang);
            return;
        }

        // --- Action: Update Student Data (POST) ---
        if ($page === 'update_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateStudent((string)$lang);
            return;
        }

        // --- Default Action: List / View / Filter ---
        $this->handleListOrView($action, (string)$lang);
    }

    /**
     * Handles the toggling of the 'IsComplete' status for a folder.
     *
     * @param string $lang Current language code.
     * @return void
     */
    private function handleToggleComplete(string $lang): void
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Assuming FolderAdmin has a method to toggle status.
            // If not, this logic should be implemented in the Model.
            if (method_exists(FolderAdmin::class, 'toggleComplete')) {
                FolderAdmin::toggleComplete((string)$id);
            }
        }
        
        // Redirect back to the list
        header('Location: index.php?page=folders-admin&lang=' . $lang);
        exit;
    }

    /**
     * Handles the processing of the 'update student' form.
     *
     * @param string $lang Current language code.
     * @return void
     */
    private function handleUpdateStudent(string $lang): void
    {
        // 1. Retrieve and Sanitize POST Data
        $data = [
            'NumEtu' => $_POST['numetu'] ?? '',
            'Nom' => $_POST['nom'] ?? '',
            'Prenom' => $_POST['prenom'] ?? '',
            'EmailPersonnel' => $_POST['email_perso'] ?? '',
            'Telephone' => $_POST['telephone'] ?? '',
            'Adresse' => $_POST['adresse'] ?? '',
            'CodePostal' => $_POST['code_postal'] ?? '',
            'Ville' => $_POST['ville'] ?? '',
            'CodeDepartement' => $_POST['dept'] ?? '',
            'Type' => $_POST['type'] ?? 'etudes',
            'Zone' => $_POST['zone'] ?? 'europe'
        ];

        // 2. Handle File Uploads Safely
        // Using helper to prevent passing 'false' to the model
        $photoData = $this->readFile('photo');
        $cvData = $this->readFile('cv');
        $conventionData = $this->readFile('convention');
        $lettreData = $this->readFile('lettre_motivation');

        // 3. Update Database via Model
        $success = FolderAdmin::updateDossier($data, $photoData, $cvData, $conventionData, $lettreData);

        // 4. Set Flash Message
        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        // 5. Redirect
        header('Location: index.php?page=folders-admin&lang=' . $lang);
        exit;
    }

    /**
     * Handles the display of the folders list (with pagination/filters) or a specific folder details view.
     *
     * @param string $action Current action (list, view, create).
     * @param string $lang   Current language code.
     * @return void
     */
    private function handleListOrView(string $action, string $lang): void
    {
        // Retrieve flash message
        $message = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        // Prepare Filters
        $filters = [
            'search'     => $_GET['search'] ?? '',
            'type'       => $_GET['type'] ?? 'all',
            'zone'       => $_GET['zone'] ?? 'all',
            'complet'    => $_GET['complet'] ?? 'all',
            'dept'       => $_GET['dept'] ?? '',
            'year'       => $_GET['year'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin'   => $_GET['date_fin'] ?? '',
        ];

        // Pagination settings
        $pageNum = (int)($_GET['p'] ?? 1);
        $perPage = 10;

        // Fetch paginated data from Model
        $result = FolderAdmin::rechercherAvecPagination($filters, $pageNum, $perPage);
        $paginatedData = $result['data'] ?? [];
        $totalCount = (int)($result['total'] ?? 0);
        $totalPages = (int)($result['totalPages'] ?? 0);

        // Fetch specific student data if in View/Edit mode
        $studentData = null;
        if (($action === 'view' || $action === 'edit') && isset($_GET['id'])) {
            $studentData = FolderAdmin::getStudentDetails((string)$_GET['id']);
        }

        // Render the View
        $view = new FoldersPageAdmin(
            $action,
            $filters,
            $pageNum,
            $perPage,
            $message,
            $lang,
            $studentData,
            $paginatedData,
            $totalCount,
            $totalPages
        );
        $view->render();
    }

    /**
     * Helper to safely read uploaded file content.
     * Returns null if no file is uploaded or if reading fails, ensuring type safety.
     *
     * @param string $key The input name in $_FILES.
     * @return string|null Binary content or null.
     */
    private function readFile(string $key): ?string
    {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents($_FILES[$key]['tmp_name']);
            return ($content !== false) ? $content : null;
        }
        return null;
    }
}
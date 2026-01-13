<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\FolderController;

use Model\Folder\FolderAdmin;
use View\Folder\FoldersPageAdmin;

/**
 * Class FoldersControllerAdmin
 *
 * Controller responsible for the administrative management of student folders.
 * It handles the listing, creation, modification, and validation status of student files.
 *
 * VERSION AVEC DEBUG
 */
class FoldersControllerAdmin
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
        return in_array($page, ['folders', 'save_student', 'folders-admin', 'toggle_complete', 'update_student']);
    }

    /**
     * Main control logic.
     * Handles authentication check, routing logic, data retrieval, and view rendering.
     */
    public function control(): void
    {
        // Ensure session is started for flash messages
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve parameters with default values
        $page = $_GET['page'] ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang = $_GET['lang'] ?? 'fr';

        // Handle toggle complete status action
        if ($page === 'toggle_complete') {
            $numetu = $_GET['numetu'] ?? null;

            if ($numetu) {
                // Decode URL parameter to handle special characters in ID
                $numetu = urldecode($numetu);

                // Call Model to switch status in Database (0 <-> 1)
                $success = FolderAdmin::toggleCompleteStatus($numetu);

                // Set Flash Message for the user
                if ($success) {
                    $_SESSION['message'] = ($lang === 'fr')
                        ? "Statut du dossier mis à jour."
                        : "Folder status updated.";
                } else {
                    $_SESSION['message'] = ($lang === 'fr')
                        ? "Erreur lors de la mise à jour."
                        : "Error updating status.";
                }

                // Redirect back to the student view
                header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
                exit;
            }
        }

        // Handle POST requests (create/update)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($page === 'save_student') {
                $this->saveStudent($lang);
                return;
            }
            if ($page === 'update_student') {
                $this->updateStudent($lang);
                return;
            }
        }

        $studentData = null;
        // If we are in 'view' mode, fetch the specific student details
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = FolderAdmin::getStudentDetails($_GET['numetu']);
        }

        // Déterminer la page courante
        $currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;




        // 🔍 DEBUG - Commence ici


        $startTime = microtime(true);  // ← AJOUTE CETTE LIGNE



        error_log("=== FOLDERS CONTROLLER DEBUG ===");
        error_log("URL: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
        error_log("GET params: " . print_r($_GET, true));
        error_log("Current Page calculated: " . $currentPage);

        // Collect filters from GET parameters for the list view
        $filters = [
            'type'       => $_GET['Type'] ?? $_GET['type'] ?? 'all',
            'zone'       => $_GET['Zone'] ?? $_GET['zone'] ?? 'all',
            'search'     => $_GET['search'] ?? '',
            'complet'    => $_GET['complet'] ?? 'all',
        ];

        error_log("Filters: " . print_r($filters, true));

        $perPage = 10;

        // Utiliser la nouvelle méthode avec pagination SQL
        $result = FolderAdmin::rechercherAvecPagination($filters, $currentPage, $perPage);

        error_log("Result from Model: total=" . $result['total'] . ", totalPages=" . $result['totalPages'] . ", data count=" . count($result['data']));

        $endTime = microtime(true);  // ← AJOUTE CETTE LIGNE
        $executionTime = round(($endTime - $startTime) * 1000, 2);  // ← AJOUTE CETTE LIGNE
        error_log("⏱️ TEMPS D'EXÉCUTION: " . $executionTime . "ms");  // ← AJOUTE CETTE LIGNE


        error_log("=== END DEBUG ===\n");
        // 🔍 DEBUG - Fin

        // Retrieve and clear Flash Message
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $view = new FoldersPageAdmin(
            $action,
            $filters,
            $currentPage,
            $perPage,
            $message,
            $lang,
            $studentData,
            $result['data'],        // Données paginées
            $result['total'],       // Nombre total
            $result['totalPages']   // Nombre de pages
        );
        $view->render();
    }

    /**
     * Handles the creation of a new student folder.
     * Processes POST data, uploads files, and calls the Model.
     *
     * @param string $lang Language for error messages.
     */
    private function saveStudent(string $lang): void
    {
        // 1. Collect form data
        $data = [
            'NumEtu' => $_POST['numetu'] ?? '',
            'Nom' => $_POST['nom'] ?? '',
            'Prenom' => $_POST['prenom'] ?? '',
            'DateNaissance' => $_POST['naissance'] ?? null,
            'Sexe' => $_POST['sexe'] ?? null,
            'Adresse' => $_POST['adresse'] ?? null,
            'CodePostal' => $_POST['cp'] ?? null,
            'Ville' => $_POST['ville'] ?? null,
            'EmailPersonnel' => $_POST['email_perso'] ?? '',
            'EmailAMU' => $_POST['email_amu'] ?? null,
            'Telephone' => $_POST['telephone'] ?? '',
            'CodeDepartement' => $_POST['departement'] ?? null,
            'Type' => $_POST['type'] ?? null,
            'Zone' => $_POST['zone'] ?? 'europe'
        ];

        // 2. Validate required fields
        $errors = [];
        if (empty($data['NumEtu'])) {
            $errors[] = ($lang === 'fr') ? 'Numéro étudiant requis' : 'Student ID required';
        }
        if (empty($data['Nom'])) {
            $errors[] = ($lang === 'fr') ? 'Nom requis' : 'Name required';
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders-admin&action=create&lang=' . $lang);
            exit;
        }

        // 3. Check for duplicates in DB
        if (FolderAdmin::getByNumetu($data['NumEtu'])) {
            $_SESSION['message'] = ($lang === 'fr') ? 'Ce numéro étudiant existe déjà' : 'ID already exists';
            header('Location: index.php?page=folders-admin&action=create&lang=' . $lang);
            exit;
        }

        // 4. Handle File Uploads
        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $cvData = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvData = file_get_contents($_FILES['cv']['tmp_name']);
        }

        $conventionData = null;
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) {
            $conventionData = file_get_contents($_FILES['convention']['tmp_name']);
        }

        $lettreData = null;
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) {
            $lettreData = file_get_contents($_FILES['lettre_motivation']['tmp_name']);
        }

        // 5. Save to Database
        $success = FolderAdmin::creerDossier($data, $photoData, $cvData, $conventionData, $lettreData);
        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier créé avec succès' : 'Folder created successfully')
            : (($lang === 'fr') ? 'Erreur lors de la création' : 'Error creating folder');

        header('Location: index.php?page=folders-admin&lang=' . $lang);
        exit;
    }

    /**
     * Handles the update of an existing student folder.
     * Note: This does NOT update the "IsComplete" status.
     *
     * @param string $lang Language for error messages.
     */
    private function updateStudent(string $lang): void
    {
        // 1. Collect form data
        $data = [
            'NumEtu' => $_POST['numetu'] ?? '',
            'Nom' => $_POST['nom'] ?? '',
            'Prenom' => $_POST['prenom'] ?? '',
            'EmailPersonnel' => $_POST['email_perso'] ?? '',
            'Telephone' => $_POST['telephone'] ?? '',
            'Type' => $_POST['type'] ?? null,
            'DateNaissance' => $_POST['naissance'] ?? null,
            'Sexe' => $_POST['sexe'] ?? null,
            'Adresse' => $_POST['adresse'] ?? null,
            'CodePostal' => $_POST['cp'] ?? null,
            'Ville' => $_POST['ville'] ?? null,
            'EmailAMU' => $_POST['email_amu'] ?? null,
            'CodeDepartement' => $_POST['departement'] ?? null,
            'Zone' => $_POST['zone'] ?? 'europe'
        ];

        // 2. Handle File Uploads (Only if new files are provided)
        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $cvData = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvData = file_get_contents($_FILES['cv']['tmp_name']);
        }

        $conventionData = null;
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) {
            $conventionData = file_get_contents($_FILES['convention']['tmp_name']);
        }

        $lettreData = null;
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) {
            $lettreData = file_get_contents($_FILES['lettre_motivation']['tmp_name']);
        }

        // 3. Update Database
        $success = FolderAdmin::updateDossier($data, $photoData, $cvData, $conventionData, $lettreData);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        // Redirect back to the student's detail view
        header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
        exit;
    }
}

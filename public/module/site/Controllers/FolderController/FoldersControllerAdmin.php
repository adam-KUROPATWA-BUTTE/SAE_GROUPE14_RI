<?php

namespace Controllers\FolderController;

use Model\Folder\FolderAdmin;
use View\Folder\FoldersPageAdmin;

/**
 * Class FoldersControllerAdmin
 *
 * Controller responsible for the administrative management of student folders.
 * It handles the listing, creation, modification, and validation status of student files.
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

        // ---------------------------------------------------------------------
        // ACTION: TOGGLE COMPLETE STATUS
        // This block handles the "Mark as Complete/Incomplete" button click.
        // It must be placed BEFORE rendering the view to process the change.
        // ---------------------------------------------------------------------
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

                // Redirect back to the student view to prevent form re-submission
                // and to show the updated status immediately.
                header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
                exit; // Stop execution after redirect
            }
        }

        // ---------------------------------------------------------------------
        // POST REQUEST HANDLING (Create / Update)
        // ---------------------------------------------------------------------
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

        // ---------------------------------------------------------------------
        // DATA RETRIEVAL FOR VIEW
        // ---------------------------------------------------------------------
        $studentData = null;
        // If we are in 'view' mode, fetch the specific student details
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = FolderAdmin::getStudentDetails($_GET['numetu']);
        }

        // Collect filters from GET parameters for the list view
        $filters = [
            'type'       => $_GET['Type'] ?? 'all',       // entrant/sortant
            'zone'       => $_GET['Zone'] ?? 'all',       // europe/hors_europe
            'stage'      => $_GET['Stage'] ?? 'all',
            'etude'      => $_GET['etude'] ?? 'all',
            'search'     => $_GET['search'] ?? '',        // Text search
            'complet'    => $_GET['complet'] ?? 'all',    // Status filter
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin'   => $_GET['date_fin'] ?? null,
            'tri_date'   => $_GET['tri_date'] ?? 'DESC'
        ];

        // Handle Pagination
        $currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $perPage = 10;

        // Retrieve and clear Flash Message
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // ---------------------------------------------------------------------
        // RENDER VIEW
        // ---------------------------------------------------------------------
        $view = new FoldersPageAdmin($action, $filters, $currentPage, $perPage, $message, $lang, $studentData);
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
        if (empty($data['NumEtu'])) $errors[] = ($lang === 'fr') ? 'Numéro étudiant requis' : 'Student ID required';
        if (empty($data['Nom'])) $errors[] = ($lang === 'fr') ? 'Nom requis' : 'Name required';
        // Add more validations as needed...

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

        // 4. Handle File Uploads (Convert to Base64)
        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $cvData = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvData = file_get_contents($_FILES['cv']['tmp_name']);
        }

        // 5. Save to Database
        $success = FolderAdmin::creerDossier($data, $photoData, $cvData);
        
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

        // 3. Update Database
        $success = FolderAdmin::updateDossier($data, $photoData, $cvData);

        $_SESSION['message'] = $success 
            ? (($lang === 'fr') ? 'Dossier mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        // Redirect back to the student's detail view
        header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
        exit;
    }
}
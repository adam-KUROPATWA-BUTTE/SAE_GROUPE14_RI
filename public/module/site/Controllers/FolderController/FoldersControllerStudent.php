<?php

namespace Controllers\FolderController;

use Controllers\ControllerInterface;
use Model\Folder\FolderStudent;
use View\Folder\FoldersPageStudent;

/**
 * Class FoldersControllerStudent
 *
 * Controller for managing student folders (student side).
 *
 * Responsibilities:
 * - Display the student's folder information.
 * - Handle the creation of a new folder if none exists.
 * - Handle the update of personal information and documents.
 */
class FoldersControllerStudent implements ControllerInterface
{
    /**
     * Checks if this controller supports the given page and method.
     *
     * @param string $page   Requested page name.
     * @param string $method HTTP method used (GET, POST, etc.).
     * @return bool True if the page is handled by this controller, false otherwise.
     */
    public static function support(string $page, string $method): bool
    {
        // Supports both viewing/editing ('folders-student') and specific actions like 'update_my_folder'
        return in_array($page, ['folders-student', 'update_my_folder', 'create_folder'], true);
    }

    /**
     * Main control method to handle the application flow.
     * Manages session, authentication checks, and routing logic.
     *
     * @return void
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Security check: User must be a student
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = $_GET['lang'] ?? 'fr';
        // Cast to string to ensure strict typing for the model
        $numEtu = (string)($_SESSION['numetu'] ?? '');

        // Handle POST requests (Update/Create)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest($numEtu, (string)$lang);
            return;
        }

        // Handle GET requests (View)
        $this->handleGetRequest($numEtu, (string)$lang);
    }

    /**
     * Handles the display of the folder page.
     *
     * @param string $numEtu Student ID.
     * @param string $lang   Language code.
     * @return void
     */
    private function handleGetRequest(string $numEtu, string $lang): void
    {
        // Fetch folder data from the Model
        $dossier = FolderStudent::getStudentDetails($numEtu);

        // Retrieve flash message if any
        $message = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        // Determine action context: if no dossier exists, we treat it as creation
        $action = $dossier ? 'view' : 'create';
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
        }

        // Render the View
        $view = new FoldersPageStudent($dossier, $numEtu, $action, $message, $lang);
        $view->render();
    }

    /**
     * Handles form submissions for updating or creating the folder.
     *
     * @param string $numEtu Student ID.
     * @param string $lang   Language code.
     * @return void
     */
    private function handlePostRequest(string $numEtu, string $lang): void
    {
        // Sanitize and collect form data
        // We ensure NumEtu comes from the secure session, not the form
        $data = [
            'NumEtu' => $numEtu,
            'Nom' => $_SESSION['etudiant_nom'] ?? '',
            'Prenom' => $_SESSION['etudiant_prenom'] ?? '',
            'EmailPersonnel' => $_POST['email_perso'] ?? '',
            'Telephone' => $_POST['telephone'] ?? '',
            'Adresse' => $_POST['adresse'] ?? '',
            'CodePostal' => $_POST['code_postal'] ?? '',
            'Ville' => $_POST['ville'] ?? '',
            'CodeDepartement' => $_POST['dept'] ?? '',
            'Type' => $_POST['type'] ?? 'etudes', // Default type
            'Zone' => $_POST['zone'] ?? 'europe'   // Default zone
        ];

        // Retrieve files safely
        $photoData = $this->getFileData('photo');
        $cvData = $this->getFileData('cv');
        $conventionData = $this->getFileData('convention');
        $lettreData = $this->getFileData('lettre_motivation');

        // Update (or create) the folder record via the Model
        $success = FolderStudent::updateDossier($data, $photoData, $cvData, $conventionData, $lettreData);

        // Set flash message based on result and language
        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier enregistré avec succès.' : 'Folder saved successfully.')
            : ($lang === 'fr' ? 'Erreur lors de l\'enregistrement.' : 'Error saving folder.');

        // Redirect to the view page
        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    /**
     * Helper to retrieve uploaded file content safely.
     * Returns null if no file is uploaded or if reading fails, preventing type errors.
     *
     * @param string $inputName The name attribute of the file input field.
     * @return string|null The binary content of the file or null.
     */
    private function getFileData(string $inputName): ?string
    {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents($_FILES[$inputName]['tmp_name']);
            return ($content !== false) ? $content : null;
        }
        return null;
    }
}
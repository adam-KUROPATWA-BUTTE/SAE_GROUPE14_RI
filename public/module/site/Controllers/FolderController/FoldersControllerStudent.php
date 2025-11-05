<?php
namespace Controllers\site\FolderController;

use Controllers\Auth_Guard;
use View\Folder\FoldersPageStudent;
use Controllers\ControllerInterface;
use Model\Folder\FolderStudent;

/**
 * Controller for managing student folders (student side).
 * 
 * Responsibilities:
 *  - Display the student's folder information
 *  - Update personal information
 */
class FoldersControllerStudent implements ControllerInterface
{
    /**
     * Checks if this controller supports the given page and method.
     *
     * @param string $page   Requested page name
     * @param string $method HTTP method used (GET, POST, etc.)
     * @return bool true if the page is handled by this controller, false otherwise
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'folders-student' || $page === 'update_student';
    }

    /**
     * Main control method to handle the application flow.
     *
     * Actions:
     *  - Starts session if needed
     *  - Checks if the student is logged in
     *  - Processes folder update if POST request
     *  - Retrieves student information
     *  - Renders the appropriate view
     */
    public function control(): void
    {
        // --- Start session if not started ---
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // --- Check if the student is logged in ---
        if (empty($_SESSION['numetu'])) {
            header('Location: index.php?page=login&error=not_logged_in');
            exit;
        }

        $numetu = $_SESSION['numetu'];
        $lang = $_GET['lang'] ?? 'fr';

        // --- Case: folder update ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['page'] ?? '') === 'update_student') {
            $this->updateStudent($numetu, $lang);
            return;
        }

        // --- Get student folder details ---
        $studentData = FolderStudent::getStudentDetails($numetu) ?: null;

        // --- Flash message from session ---
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // --- Render the view ---
        $view = new FoldersPageStudent($studentData, $numetu, 'view', $message, $lang);
        $view->render();
    }

    /**
     * Updates the student's folder information.
     *
     * @param string $numetu Student number (unique identifier, not editable)
     * @param string $lang   Language for messages (fr or en)
     */
    private function updateStudent(string $numetu, string $lang): void
    {
        // --- Prepare updated data (some fields are not editable) ---
        $data = [
            'NumEtu'         => $numetu, // locked
            'Adresse'        => $_POST['adresse'] ?? null,
            'CodePostal'     => $_POST['cp'] ?? null,
            'Ville'          => $_POST['ville'] ?? null,
            'Telephone'      => $_POST['telephone'] ?? null,
            'EmailPersonnel' => $_POST['email_perso'] ?? null,
        ];

        // --- Minimal validation ---
        $errors = [];
        if (empty($data['EmailPersonnel'])) {
            $errors[] = $lang === 'fr' ? "L'email personnel est requis." : "Personal email is required.";
        }
        if (empty($data['Telephone'])) {
            $errors[] = $lang === 'fr' ? "Le téléphone est requis." : "Phone number is required.";
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // --- Handle uploaded files ---
        $photoData = isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK
            ? file_get_contents($_FILES['photo']['tmp_name'])
            : null;

        $cvData = isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK
            ? file_get_contents($_FILES['cv']['tmp_name'])
            : null;

        // --- Update the student folder ---
        $success = FolderStudent::updateDossier($data, $photoData, $cvData);

        // --- Flash message and redirect to student page ---
        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier mis à jour avec succès.' : 'Folder updated successfully.')
            : ($lang === 'fr' ? 'Erreur lors de la mise à jour du dossier.' : 'Error updating folder.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }
}

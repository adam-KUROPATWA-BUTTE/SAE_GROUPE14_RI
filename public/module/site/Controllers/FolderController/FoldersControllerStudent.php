<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\site\FolderController;

use Controllers\Auth_Guard;
use View\Folder\FoldersPageStudent;
use Controllers\ControllerInterface;
use Model\Folder\FolderStudent;

/**
 * Controller for managing student folders (student side).
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
        return in_array($page, ['folders-student', 'update_my_folder', 'create_folder']);
    }

    /**
     * Main control method to handle the application flow.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['numetu'])) {
            header('Location: index.php?page=login&error=not_logged_in');
            exit;
        }

        $numetu = (string)$_SESSION['numetu'];
        $lang = isset($_GET['lang']) ? (string)$_GET['lang'] : 'fr';
        $page = isset($_GET['page']) ? (string)$_GET['page'] : '';

        // --- Routing Actions (POST) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($page === 'update_my_folder') {
                $this->updateStudent($numetu, $lang);
                return;
            }
            if ($page === 'create_folder') {
                $this->createFolder($numetu, $lang);
                return;
            }
        }

        // --- Get student folder details ---
        $studentData = FolderStudent::getStudentDetails($numetu) ?: null;

        // --- Flash message from session ---
        $message = isset($_SESSION['message']) ? (string)$_SESSION['message'] : '';
        unset($_SESSION['message']);

        // --- Render the view ---
        // CORRECTION: Suppression du paramètre 'view' (argument #3) car le constructeur de la vue en attend 4
        $view = new FoldersPageStudent($studentData, $numetu, $message, $lang);
        $view->render();
    }

    /**
     * Creates a new folder for the student.
     *
     * @param string $numetu The student ID.
     * @param string $lang   The current language.
     */
    private function createFolder(string $numetu, string $lang): void
    {
        if (FolderStudent::getStudentDetails($numetu)) {
            $_SESSION['message'] = ($lang === 'fr')
                ? "Vous avez déjà déposé un dossier."
                : "You have already submitted an application.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        $data = [
            'NumEtu' => $numetu,
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
            'Zone' => $_POST['zone'] ?? null
        ];

        $errors = [];
        if (empty($data['Nom']) || empty($data['Prenom'])) {
            $errors[] = $lang === 'fr' ? "Nom et Prénom requis." : "Name and Firstname required.";
        }
        if (empty($data['Type']) || empty($data['Zone'])) {
            $errors[] = $lang === 'fr' ? "Type et Zone requis." : "Type and Zone required.";
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        $photoData = $this->getFileData('photo');
        $cvData = $this->getFileData('cv');
        $conventionData = $this->getFileData('convention');
        $lettreData = $this->getFileData('lettre_motivation');

        $success = FolderStudent::createDossier($data, $photoData, $cvData, $conventionData, $lettreData);

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Votre demande a été déposée avec succès.' : 'Application submitted successfully.')
            : ($lang === 'fr' ? 'Erreur lors du dépôt de la demande.' : 'Error submitting application.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    /**
     * Updates the student's folder information.
     *
     * @param string $numetu The student ID.
     * @param string $lang   The current language.
     */
    private function updateStudent(string $numetu, string $lang): void
    {
        $data = [
            'NumEtu'         => $numetu,
            'Adresse'        => $_POST['adresse'] ?? null,
            'CodePostal'     => $_POST['cp'] ?? null,
            'Ville'          => $_POST['ville'] ?? null,
            'Telephone'      => $_POST['telephone'] ?? null,
            'EmailPersonnel' => $_POST['email_perso'] ?? null,
        ];

        $errors = [];
        if (empty($data['EmailPersonnel'])) {
            $errors[] = $lang === 'fr' ? "L'email personnel est requis." : "Personal email is required.";
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        $photoData = $this->getFileData('photo');
        $cvData = $this->getFileData('cv');
        $conventionData = $this->getFileData('convention');
        $lettreData = $this->getFileData('lettre_motivation');

        $success = FolderStudent::updateDossier($data, $photoData, $cvData, $conventionData, $lettreData);

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier mis à jour avec succès.' : 'Folder updated successfully.')
            : ($lang === 'fr' ? 'Erreur lors de la mise à jour du dossier.' : 'Error updating folder.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    /**
     * Helper to retrieve file content if uploaded correctly.
     *
     * @param string $inputName The name attribute of the file input.
     * @return string|null The binary content of the file or null if not uploaded.
     */
    private function getFileData(string $inputName): ?string
    {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            // CORRECTION: file_get_contents peut renvoyer false
            $content = file_get_contents($_FILES[$inputName]['tmp_name']);
            return ($content === false) ? null : $content;
        }
        return null;
    }
}

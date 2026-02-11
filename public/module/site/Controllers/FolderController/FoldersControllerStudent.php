<?php

declare(strict_types=1);

namespace Controllers\site\FolderController;

use Controllers\ControllerInterface;
use Model\Folder\FolderStudent;
use View\Folder\FoldersPageStudent;

class FoldersControllerStudent implements ControllerInterface
{
    /**
     * Check if the controller supports the request.
     *
     * @param string $page
     * @param string $method
     * @return bool
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders-student', 'update_my_folder', 'create_folder'], true);
    }

    /**
     * Main control method.
     *
     * @return void
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
        $lang = isset($_GET['lang']) && is_string($_GET['lang']) ? $_GET['lang'] : 'fr';
        $page = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : '';

        // POST Routing
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($page === 'update_my_folder') {
                $this->handleUpdateFolder($numetu, $lang);
                return;
            }
            if ($page === 'create_folder') {
                $this->handleCreateFolder($numetu, $lang);
                return;
            }
        }

        // Display page
        $this->displayFolderPage($numetu, $lang);
    }

    /**
     * Display the student folder page.
     *
     * @param string $numetu
     * @param string $lang
     * @return void
     */
    private function displayFolderPage(string $numetu, string $lang): void
    {
        $studentData = FolderStudent::getStudentDetails($numetu);

        $message = isset($_SESSION['message']) && is_string($_SESSION['message'])
            ? $_SESSION['message']
            : '';
        unset($_SESSION['message']);

        // Ensure we pass a valid array to the view, even if null returned
        $data = is_array($studentData) ? $studentData : [];

        $view = new FoldersPageStudent($data, $numetu, $message, $lang);
        $view->render();
    }

    /**
     * Handle the creation of a new folder.
     *
     * @param string $numetu
     * @param string $lang
     * @return void
     */
    private function handleCreateFolder(string $numetu, string $lang): void
    {
        // Check if folder already exists
        if (FolderStudent::getStudentDetails($numetu)) {
            $_SESSION['message'] = $lang === 'fr'
                ? "Vous avez déjà déposé un dossier."
                : "You have already submitted an application.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Collect form data safely
        $data = [
            'NumEtu' => $numetu,
            'Nom' => isset($_POST['nom']) ? (string)$_POST['nom'] : '',
            'Prenom' => isset($_POST['prenom']) ? (string)$_POST['prenom'] : '',
            'DateNaissance' => isset($_POST['naissance']) ? (string)$_POST['naissance'] : null,
            'Sexe' => isset($_POST['sexe']) ? (string)$_POST['sexe'] : null,
            'Adresse' => isset($_POST['adresse']) ? (string)$_POST['adresse'] : null,
            'CodePostal' => isset($_POST['cp']) ? (string)$_POST['cp'] : null,
            'Ville' => isset($_POST['ville']) ? (string)$_POST['ville'] : null,
            'EmailPersonnel' => isset($_POST['email_perso']) ? (string)$_POST['email_perso'] : '',
            'EmailAMU' => isset($_POST['email_amu']) ? (string)$_POST['email_amu'] : null,
            'Telephone' => isset($_POST['telephone']) ? (string)$_POST['telephone'] : '',
            'CodeDepartement' => isset($_POST['departement']) ? (string)$_POST['departement'] : null,
            'Type' => isset($_POST['type']) ? (string)$_POST['type'] : null,
            'Zone' => isset($_POST['zone']) ? (string)$_POST['zone'] : null
        ];

        // Validation
        $errors = $this->validateFolderData($data, $lang);

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Handle uploaded files
        $photoData = $this->getUploadedFileContent('photo');
        $cvData = $this->getUploadedFileContent('cv');
        $conventionData = $this->getUploadedFileContent('convention');
        $lettreData = $this->getUploadedFileContent('lettre_motivation');

        // Create the folder
        $success = FolderStudent::createDossier(
            $data,
            $photoData,
            $cvData,
            $conventionData,
            $lettreData
        );

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Votre demande a été déposée avec succès.' : 'Application submitted successfully.')
            : ($lang === 'fr' ? 'Erreur lors du dépôt de la demande.' : 'Error submitting application.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    /**
     * Handle the update of an existing folder.
     *
     * @param string $numetu
     * @param string $lang
     * @return void
     */
    private function handleUpdateFolder(string $numetu, string $lang): void
    {
        $data = [
            'NumEtu' => $numetu,
            'Adresse' => isset($_POST['adresse']) ? (string)$_POST['adresse'] : null,
            'CodePostal' => isset($_POST['cp']) ? (string)$_POST['cp'] : null,
            'Ville' => isset($_POST['ville']) ? (string)$_POST['ville'] : null,
            'Telephone' => isset($_POST['telephone']) ? (string)$_POST['telephone'] : null,
            'EmailPersonnel' => isset($_POST['email_perso']) ? (string)$_POST['email_perso'] : null,
        ];

        // Basic validation for update
        if (empty($data['EmailPersonnel'])) {
            $_SESSION['message'] = $lang === 'fr'
                ? "L'email personnel est requis."
                : "Personal email is required.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Handle files
        $photoData = $this->getUploadedFileContent('photo');
        $cvData = $this->getUploadedFileContent('cv');
        $conventionData = $this->getUploadedFileContent('convention');
        $lettreData = $this->getUploadedFileContent('lettre_motivation');

        // Update
        $success = FolderStudent::updateDossier(
            $data,
            $photoData,
            $cvData,
            $conventionData,
            $lettreData
        );

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier mis à jour avec succès.' : 'Folder updated successfully.')
            : ($lang === 'fr' ? 'Erreur lors de la mise à jour du dossier.' : 'Error updating folder.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    /**
     * Validate folder data.
     *
     * @param array<string, mixed> $data
     * @param string $lang
     * @return array<string> List of error messages
     */
    private function validateFolderData(array $data, string $lang): array
    {
        $errors = [];

        if (empty($data['Nom']) || empty($data['Prenom'])) {
            $errors[] = $lang === 'fr'
                ? "Nom et Prénom requis."
                : "Name and Firstname required.";
        }

        if (
            empty($data['EmailPersonnel']) ||
            !is_string($data['EmailPersonnel']) ||
            !filter_var($data['EmailPersonnel'], FILTER_VALIDATE_EMAIL)
        ) {
            $errors[] = $lang === 'fr'
                ? "Email personnel valide requis."
                : "Valid personal email required.";
        }

        if (empty($data['Telephone'])) {
            $errors[] = $lang === 'fr'
                ? "Téléphone requis."
                : "Phone number required.";
        }

        if (empty($data['Type']) || empty($data['Zone'])) {
            $errors[] = $lang === 'fr'
                ? "Type et Zone requis."
                : "Type and Zone required.";
        }

        return $errors;
    }

    /**
     * Retrieve the content of an uploaded file.
     *
     * @param string $fieldName
     * @return string|null
     */
    private function getUploadedFileContent(string $fieldName): ?string
    {
        if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
            return null;
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmpName = $_FILES[$fieldName]['tmp_name'];

        if (!is_string($tmpName) || !file_exists($tmpName)) {
            return null;
        }

        $content = file_get_contents($tmpName);

        return $content !== false ? $content : null;
    }
}

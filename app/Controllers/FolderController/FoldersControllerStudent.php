<?php

declare(strict_types=1);

namespace Controllers\site\FolderController;

use Controllers\ControllerInterface;
use Model\UseCase\ManageFolderUseCase;
use View\Folder\FoldersPageStudent;

class FoldersControllerStudent implements ControllerInterface
{
    private ManageFolderUseCase $folderUseCase;

    public function __construct()
    {
        $this->folderUseCase = new ManageFolderUseCase();
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders-student', 'update_my_folder', 'create_folder'], true);
    }

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

        $this->displayFolderPage($numetu, $lang);
    }

    private function displayFolderPage(string $numetu, string $lang): void
    {
        $studentData = $this->folderUseCase->getStudentDetails($numetu);
        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        unset($_SESSION['message']);

        $data = is_array($studentData) ? $studentData : [];
        $view = new FoldersPageStudent($data, $numetu, $message, $lang);
        $view->render();
    }

    private function handleCreateFolder(string $numetu, string $lang): void
    {
        if ($this->folderUseCase->getStudentDetails($numetu)) {
            $_SESSION['message'] = $lang === 'fr' ? "Vous avez déjà déposé un dossier." : "You have already submitted an application.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

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

        $errors = $this->validateFolderData($data, $lang);

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        $success = $this->folderUseCase->creerDossier(
            $data,
            $this->getUploadedFileContent('photo'),
            $this->getUploadedFileContent('cv'),
            $this->getUploadedFileContent('convention'),
            $this->getUploadedFileContent('lettre_motivation')
        );

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Votre demande a été déposée avec succès.' : 'Application submitted successfully.')
            : ($lang === 'fr' ? 'Erreur lors du dépôt de la demande.' : 'Error submitting application.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

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

        if (empty($data['EmailPersonnel'])) {
            $_SESSION['message'] = $lang === 'fr' ? "L'email personnel est requis." : "Personal email is required.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        $success = $this->folderUseCase->updateDossier(
            $data,
            $this->getUploadedFileContent('photo'),
            $this->getUploadedFileContent('cv'),
            $this->getUploadedFileContent('convention'),
            $this->getUploadedFileContent('lettre_motivation')
        );

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier mis à jour avec succès.' : 'Folder updated successfully.')
            : ($lang === 'fr' ? 'Erreur lors de la mise à jour du dossier.' : 'Error updating folder.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    private function validateFolderData(array $data, string $lang): array
    {
        $errors = [];
        if (empty($data['Nom']) || empty($data['Prenom'])) {
            $errors[] = $lang === 'fr' ? "Nom et Prénom requis." : "Name and Firstname required.";
        }
        if (empty($data['EmailPersonnel']) || !is_string($data['EmailPersonnel']) || !filter_var($data['EmailPersonnel'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = $lang === 'fr' ? "Email personnel valide requis." : "Valid personal email required.";
        }
        if (empty($data['Telephone'])) {
            $errors[] = $lang === 'fr' ? "Téléphone requis." : "Phone number required.";
        }
        if (empty($data['Type']) || empty($data['Zone'])) {
            $errors[] = $lang === 'fr' ? "Type et Zone requis." : "Type and Zone required.";
        }
        return $errors;
    }

    private function getUploadedFileContent(string $fieldName): ?string
    {
        if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) return null;
        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return null;
        $tmpName = $_FILES[$fieldName]['tmp_name'];
        if (!is_string($tmpName) || !file_exists($tmpName)) return null;
        $content = file_get_contents($tmpName);
        return $content !== false ? $content : null;
    }
}
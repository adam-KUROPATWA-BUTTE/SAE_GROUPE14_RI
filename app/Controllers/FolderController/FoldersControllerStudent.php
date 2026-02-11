<?php

namespace Controllers\site\FolderController;

use Controllers\ControllerInterface;
use Model\Folder\FolderStudent;
use View\Folder\FoldersPageStudent;

class FoldersControllerStudent implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders-student', 'update_my_folder', 'create_folder']);
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
        $lang = $_GET['lang'] ?? 'fr';
        $page = $_GET['page'] ?? '';

        // Routing POST
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

        // Afficher la page
        $this->displayFolderPage($numetu, $lang);
    }

    /**
     * Afficher la page du dossier étudiant
     */
    private function displayFolderPage(string $numetu, string $lang): void
    {
        $studentData = FolderStudent::getStudentDetails($numetu);

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $view = new FoldersPageStudent($studentData, $numetu, $message, $lang);
        $view->render();
    }

    /**
     * Créer un nouveau dossier
     */
    private function handleCreateFolder(string $numetu, string $lang): void
    {
        // Vérifier si le dossier existe déjà
        if (FolderStudent::getStudentDetails($numetu)) {
            $_SESSION['message'] = $lang === 'fr'
                ? "Vous avez déjà déposé un dossier."
                : "You have already submitted an application.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Récupérer les données du formulaire
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

        // Validation
        $errors = $this->validateFolderData($data, $lang);

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Gérer les fichiers uploadés
        $photoData = $this->getUploadedFileContent('photo');
        $cvData = $this->getUploadedFileContent('cv');
        $conventionData = $this->getUploadedFileContent('convention');
        $lettreData = $this->getUploadedFileContent('lettre_motivation');

        // Créer le dossier
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
     * Mettre à jour un dossier existant
     */
    private function handleUpdateFolder(string $numetu, string $lang): void
    {
        $data = [
            'NumEtu' => $numetu,
            'Adresse' => $_POST['adresse'] ?? null,
            'CodePostal' => $_POST['cp'] ?? null,
            'Ville' => $_POST['ville'] ?? null,
            'Telephone' => $_POST['telephone'] ?? null,
            'EmailPersonnel' => $_POST['email_perso'] ?? null,
        ];

        // Validation
        if (empty($data['EmailPersonnel'])) {
            $_SESSION['message'] = $lang === 'fr'
                ? "L'email personnel est requis."
                : "Personal email is required.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Gérer les fichiers
        $photoData = $this->getUploadedFileContent('photo');
        $cvData = $this->getUploadedFileContent('cv');
        $conventionData = $this->getUploadedFileContent('convention');
        $lettreData = $this->getUploadedFileContent('lettre_motivation');

        // Mettre à jour
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
     * Valider les données du dossier
     *
     * @return array<string> Liste des erreurs
     */
    private function validateFolderData(array $data, string $lang): array
    {
        $errors = [];

        if (empty($data['Nom']) || empty($data['Prenom'])) {
            $errors[] = $lang === 'fr'
                ? "Nom et Prénom requis."
                : "Name and Firstname required.";
        }

        if (empty($data['EmailPersonnel']) || !filter_var($data['EmailPersonnel'], FILTER_VALIDATE_EMAIL)) {
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
     * Récupérer le contenu d'un fichier uploadé
     */
    private function getUploadedFileContent(string $fieldName): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
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
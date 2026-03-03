<?php

declare(strict_types=1);

namespace Controllers\FolderController;

use Controllers\ControllerInterface;
use Model\UseCase\ManageFolderUseCase;
 
/**
 * Class FoldersControllerStudent
 * Handles the HTTP requests and routing for student-facing folder operations.
 */
class FoldersControllerStudent implements ControllerInterface
{
    private ManageFolderUseCase $folderUseCase;

    public function __construct()
    {
        $this->folderUseCase = new ManageFolderUseCase();
    }

    /**
     * Determines if this controller supports the requested page.
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders-student', 'update_my_folder', 'create_folder'], true);
    }

    /**
     * Main control entry point for the student folder module.
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

    /**
     * Renders the student folder management view.
     */
    private function displayFolderPage(string $numetu, string $lang): void
    {
        $studentData = $this->folderUseCase->getStudentDetails($numetu);
        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        unset($_SESSION['message']);

        $data = is_array($studentData) ? $studentData : [];
        
        // Appel à la vue via la classe Core\View
        \Core\View::render('Folder/folders_student', [
            'dossier'   => $data,
            'studentId' => $numetu,
            'message'   => $message,
            'lang'      => $lang
        ]);
    }

    /**
     * Method to process file uploads safely and catch any server limits/errors
     */
    private function handleFileUploads(array &$data, string $lang): array
    {
        $errors = [];
        $fileFields = ['photo', 'cv', 'convention', 'lettre_motivation', 'langues_file'];
        
        foreach ($fileFields as $field) {
            if (isset($_FILES[$field])) {
                $error = $_FILES[$field]['error'];
                
                if ($error === UPLOAD_ERR_OK) {
                    $content = file_get_contents($_FILES[$field]['tmp_name']);
                    if ($content !== false) {
                        $data[$field] = $content;
                    } else {
                        $errors[] = ($lang === 'fr') ? "Impossible de lire le fichier '$field'." : "Cannot read file '$field'.";
                    }
                } elseif ($error !== UPLOAD_ERR_NO_FILE) {
                    $msg = ($lang === 'fr') ? "Erreur upload pour '$field' (Code: $error)" : "Upload error for '$field' (Code: $error)";
                    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                        $msg .= ($lang === 'fr') ? " : Le fichier est trop lourd (limite dépassée)." : " : File is too large.";
                    }
                    $errors[] = $msg;
                }
            }
        }
        return $errors;
    }

    /**
     * Processes the creation of a new student folder.
     */
    private function handleCreateFolder(string $numetu, string $lang): void
    {
        if ($this->folderUseCase->getStudentDetails($numetu)) {
            $_SESSION['message'] = $lang === 'fr' ? "Vous avez déjà déposé un dossier." : "You have already submitted an application.";
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
            'Composante' => $_POST['composante'] ?? null,
            'Discipline' => $_POST['discipline'] ?? null,
            'Formation' => $_POST['formation'] ?? null,
            'Pays' => $_POST['pays'] ?? null,
            'Type' => $_POST['type'] ?? null,
            'Zone' => $_POST['zone'] ?? null,
            // SÉCURITÉ : Les champs administratifs sont forcés à null pour ne pas lire d'éventuelles valeurs injectées
            'Campus' => null,
            'NiveauEtude' => null,
            'MoyenneBac' => null,
            'MoyenneSansBac' => null,
            'DateDebut' => null,
            'MobiliteAnterieure' => null,
        ];

        $errors = $this->validateFolderData($data, $lang);

        if (!empty($errors)) {
            $_SESSION['message'] = implode('<br>', $errors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Process file uploads safely and catch limits
        $uploadErrors = $this->handleFileUploads($data, $lang);
        if (!empty($uploadErrors)) {
            $_SESSION['message'] = implode('<br>', $uploadErrors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        $success = $this->folderUseCase->creerDossier($data);

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Votre demande a été déposée avec succès.' : 'Application submitted successfully.')
            : ($lang === 'fr' ? 'Erreur lors du dépôt de la demande.' : 'Error submitting application.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    /**
     * Processes the update of an existing student folder.
     */
    private function handleUpdateFolder(string $numetu, string $lang): void
    {
        $data = [
            'NumEtu' => $numetu,
            'Nom' => $_POST['nom'] ?? null,
            'Prenom' => $_POST['prenom'] ?? null,
            'DateNaissance' => $_POST['naissance'] ?? null,
            'Sexe' => $_POST['sexe'] ?? null,
            'Adresse' => $_POST['adresse'] ?? null,
            'CodePostal' => $_POST['cp'] ?? null,
            'Ville' => $_POST['ville'] ?? null,
            'EmailPersonnel' => $_POST['email_perso'] ?? null,
            'EmailAMU' => $_POST['email_amu'] ?? null,
            'Telephone' => $_POST['telephone'] ?? null,
            'CodeDepartement' => $_POST['departement'] ?? null,
            'Composante' => $_POST['composante'] ?? null,
            'Discipline' => $_POST['discipline'] ?? null,
            'Formation' => $_POST['formation'] ?? null,
            'Pays' => $_POST['pays'] ?? null,
            'Type' => $_POST['type'] ?? null,
            'Zone' => $_POST['zone'] ?? null,
            // SÉCURITÉ : La fonction Update utilisera la commande SQL 'COALESCE' qui conservera
            // les anciennes valeurs pour ces champs administratifs puisqu'on passe null.
        ];

        if (empty($data['EmailPersonnel'])) {
            $_SESSION['message'] = $lang === 'fr' ? "L'email personnel est requis." : "Personal email is required.";
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        // Process file uploads safely and catch limits
        $uploadErrors = $this->handleFileUploads($data, $lang);
        if (!empty($uploadErrors)) {
            $_SESSION['message'] = implode('<br>', $uploadErrors);
            header('Location: index.php?page=folders-student&lang=' . $lang);
            exit;
        }

        $success = $this->folderUseCase->updateDossier($data);

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier mis à jour avec succès.' : 'Folder updated successfully.')
            : ($lang === 'fr' ? 'Erreur lors de la mise à jour du dossier.' : 'Error updating folder.');

        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    /**
     * Validates required student data.
     */
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
}
<?php
namespace Controllers\FolderController;

use Model\Folder\FolderAdmin;
use View\Folder\FoldersPageAdmin;

/**
 * Controller for managing student folders (admin side).
 * 
 * Responsibilities:
 *  - Display lists of student folders
 *  - Create new student folders
 *  - Update existing student folders
 */
class FoldersControllerAdmin
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
        return in_array($page, ['folders', 'save_student', 'folders-admin']);
    }

    /**
     * Main control method to handle the admin folder flow.
     *
     * Actions:
     *  - Start session if not already started
     *  - Handle POST requests for creating or updating students
     *  - Retrieve student data for view or edit
     *  - Set filters and pagination
     *  - Render the admin view
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = $_GET['page'] ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang = $_GET['lang'] ?? 'fr';

        // Handle POST requests
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

        // Retrieve student data for view or edit
        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = FolderAdmin::getStudentDetails($_GET['numetu']);
        }

        // Filters for listing students

        $filters = [
            'type'   => $_GET['Type'] ?? 'all',
            'zone'   => $_GET['Zone'] ?? 'all',
            'stage'  => $_GET['Stage'] ?? 'all',
            'etude'  => $_GET['etude'] ?? 'all',
            'search' => $_GET['search'] ?? '',
            'complet'    => $_GET['complet'] ?? 'all',
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin'   => $_GET['date_fin'] ?? null,
            'tri_date'   => $_GET['tri_date'] ?? 'DESC'
        ];

        // Pagination
        $currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $perPage = 10;

        // Flash message
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // Render admin view
        $view = new FoldersPageAdmin($action, $filters, $currentPage, $perPage, $message, $lang, $studentData);
        $view->render();
    }

    /**
     * Creates a new student folder.
     *
     * @param string $lang Language for messages (fr or en)
     */
    private function saveStudent(string $lang): void
    {
        // Prepare data from POST
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

        // Basic validation
        $errors = [];
        if (empty($data['NumEtu'])) $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        if (empty($data['Nom'])) $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        if (empty($data['Prenom'])) $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        if (empty($data['EmailPersonnel'])) $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        if (empty($data['Telephone'])) $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';
        if (empty($data['Type'])) $errors[] = $lang === 'fr' ? 'Le type est requis' : 'Type is required';
        if (empty($data['Zone'])) $errors[] = $lang === 'fr' ? 'La zone est requise' : 'Zone is required';

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Check for existing student
        if (FolderAdmin::getByNumetu($data['NumEtu'])) {
            $_SESSION['message'] = $lang === 'fr' ? 'Un étudiant avec ce numéro existe déjà' : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        if (FolderAdmin::getByEmail($data['EmailPersonnel'])) {
            $_SESSION['message'] = $lang === 'fr' ? 'Un étudiant avec cet email existe déjà' : 'A student with this email already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Handle uploaded files
        $photoData = isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK ? file_get_contents($_FILES['photo']['tmp_name']) : null;
        $cvData = isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK ? file_get_contents($_FILES['cv']['tmp_name']) : null;

        // Create folder in database
        $success = FolderAdmin::creerDossier($data, $photoData, $cvData);

        // Flash message and redirect
        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier créé avec succès' : 'Folder created successfully')
            : ($lang === 'fr' ? 'Erreur lors de la création du dossier' : 'Error creating folder');

        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }

    /**
     * Updates an existing student folder.
     *
     * @param string $lang Language for messages (fr or en)
     */
    private function updateStudent(string $lang): void
    {
        // Prepare data from POST
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

        // Basic validation
        $errors = [];
        if (empty($data['NumEtu'])) $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        if (empty($data['Nom'])) $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        if (empty($data['Prenom'])) $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        if (empty($data['EmailPersonnel'])) $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        if (empty($data['Telephone'])) $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
            exit;
        }

        // Handle uploaded files
        $photoData = isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK ? file_get_contents($_FILES['photo']['tmp_name']) : null;
        $cvData = isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK ? file_get_contents($_FILES['cv']['tmp_name']) : null;

        // Update folder in database
        $success = FolderAdmin::updateDossier($data, $photoData, $cvData);

        // Flash message and redirect
        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier modifié avec succès' : 'Folder updated successfully')
            : ($lang === 'fr' ? 'Erreur lors de la modification du dossier' : 'Error updating folder');

        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }
}

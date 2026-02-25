<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\FolderController;

use Model\UseCase\ManageFolderUseCase;

/**
 * Controller handling the administrative actions for student folders.
 */
class FoldersControllerAdmin
{
    private ManageFolderUseCase $folderUseCase;

    public function __construct()
    {
        $this->folderUseCase = new ManageFolderUseCase();
    }

    /**
     * Checks if this controller supports the requested page and method.
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders', 'save_student', 'folders-admin', 'toggle_complete', 'update_student', 'import_folders']);
    }

    /**
     * Main control function to route requests.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = $_GET['page'] ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang = $_GET['lang'] ?? 'fr';

        // Toggle folder completion status
        if ($page === 'toggle_complete') {
            $numetu = $_GET['numetu'] ?? null;
            if ($numetu) {
                $numetu = urldecode($numetu);
                $success = $this->folderUseCase->toggleCompleteStatus($numetu);
                $_SESSION['message'] = $success
                    ? (($lang === 'fr') ? "Statut du dossier mis à jour." : "Folder status updated.")
                    : (($lang === 'fr') ? "Erreur lors de la mise à jour." : "Error updating status.");
                header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
                exit;
            }
        }

        // Handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($page === 'import_folders') {
                $this->importFolders($lang);
                return;
            }
            if ($page === 'save_student') {
                $this->saveStudent($lang);
                return;
            }
            if ($page === 'update_student') {
                $this->updateStudent($lang);
                return;
            }
        }

        // Fetch student data for view mode
        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = $this->folderUseCase->getStudentDetails($_GET['numetu']);
        }

        // Setup filter array based on GET parameters
        $filters = [
            'type'    => $_GET['type'] ?? 'all',   
            'zone'    => $_GET['zone'] ?? 'all',   
            'search'  => $_GET['search'] ?? '',    
            'complet' => $_GET['complet'] ?? 'all',
            'composante' => $_GET['composante'] ?? 'all',
            'accord'  => $_GET['accord'] ?? 'all',
        ];

        // Retrieve all records without pagination (handled by JS)
        $result = $this->folderUseCase->searchWithoutPagination($filters);
 
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // Render the view
        \Core\View::render('Folder/folders_admin', [
            'action'        => $action,
            'filters'       => $filters,
            'page'          => 1,
            'message'       => $message,
            'lang'          => $lang,
            'studentData'   => $studentData,
            'paginatedData' => $result['data'],
            'totalCount'    => $result['total'],
            'totalPages'    => 1 
        ]);
    }

    /**
     * Imports folders from an uploaded file.
     */
    private function importFolders(string $lang): void
    {
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $filePath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['csv', 'xlsx', 'xls'];
            
            if (!in_array($ext, $allowedExtensions)) {
                $_SESSION['message'] = ($lang === 'fr') 
                    ? 'Erreur : Format non supporté. Utilisez .csv ou .xlsx' 
                    : 'Error: Unsupported format. Use .csv or .xlsx';
            } else {
                $success = $this->folderUseCase->importFoldersFromCSV($filePath); 

                $_SESSION['message'] = $success
                    ? (($lang === 'fr') ? 'Importation réussie' : 'Import successful')
                    : (($lang === 'fr') ? 'Erreur lors de l\'importation (fichier vide ou format invalide)' : 'Error during import');
            }
        } else {
            $_SESSION['message'] = ($lang === 'fr') ? 'Erreur lors du téléchargement du fichier.' : 'File upload error.';
        }
        
        header('Location: index.php?page=folders-admin&lang=' . $lang);
        exit;
    }

    /**
     * Validates and saves a new student folder.
     */
    private function saveStudent(string $lang): void
    {
        // TOUS les champs sont désormais récupérés correctement
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
            'Zone' => $_POST['zone'] ?? 'europe',
            'Composante' => $_POST['composante'] ?? null,
            'Pays' => $_POST['pays'] ?? null,
            'Campus' => $_POST['campus'] ?? null,
            'Discipline' => $_POST['discipline'] ?? null,
            'NiveauEtude' => $_POST['niveau_etude'] ?? null,
            'Formation' => $_POST['formation'] ?? null,
            'MoyenneBac' => $_POST['moyenne_bac'] ?? null,
            'MoyenneSansBac' => $_POST['moyenne_sans_bac'] ?? null,
            'AvisDRI' => $_POST['avis_dri'] ?? null,
            'DateDebut' => $_POST['date_debut'] ?? null,
            'MobiliteAnterieure' => $_POST['mobilite_anterieure'] ?? null,
        ];

        $errors = [];
        if (empty($data['NumEtu'])) $errors[] = ($lang === 'fr') ? 'Numéro étudiant requis' : 'Student ID required';
        if (empty($data['Nom'])) $errors[] = ($lang === 'fr') ? 'Nom requis' : 'Name required';

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders-admin&action=create&lang=' . $lang);
            exit;
        }

        if ($this->folderUseCase->getByNumetu($data['NumEtu'])) {
            $_SESSION['message'] = ($lang === 'fr') ? 'Ce numéro étudiant existe déjà' : 'ID already exists';
            header('Location: index.php?page=folders-admin&action=create&lang=' . $lang);
            exit;
        }

        // Process file uploads (y compris l'attestation de langues)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) $data['photo'] = file_get_contents($_FILES['photo']['tmp_name']);
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) $data['cv'] = file_get_contents($_FILES['cv']['tmp_name']);
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) $data['convention'] = file_get_contents($_FILES['convention']['tmp_name']);
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) $data['lettre_motivation'] = file_get_contents($_FILES['lettre_motivation']['tmp_name']);
        if (isset($_FILES['langues_file']) && $_FILES['langues_file']['error'] === UPLOAD_ERR_OK) $data['langues_file'] = file_get_contents($_FILES['langues_file']['tmp_name']);

        $success = $this->folderUseCase->creerDossier($data);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier créé avec succès' : 'Folder created successfully')
            : (($lang === 'fr') ? 'Erreur lors de la création' : 'Error creating folder');

        header('Location: index.php?page=folders-admin&lang=' . $lang);
        exit;
    }

    /**
     * Validates and updates an existing student folder.
     */
    private function updateStudent(string $lang): void
    {
        // TOUS les champs sont désormais récupérés correctement
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
            'Zone' => $_POST['zone'] ?? 'europe',
            'Composante' => $_POST['composante'] ?? null,
            'Pays' => $_POST['pays'] ?? null,
            'Campus' => $_POST['campus'] ?? null,
            'Discipline' => $_POST['discipline'] ?? null,
            'NiveauEtude' => $_POST['niveau_etude'] ?? null,
            'Formation' => $_POST['formation'] ?? null,
            'MoyenneBac' => $_POST['moyenne_bac'] ?? null,
            'MoyenneSansBac' => $_POST['moyenne_sans_bac'] ?? null,
            'AvisDRI' => $_POST['avis_dri'] ?? null,
            'DateDebut' => $_POST['date_debut'] ?? null,
            'MobiliteAnterieure' => $_POST['mobilite_anterieure'] ?? null,
        ];

        // Process file uploads (y compris l'attestation de langues)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) $data['photo'] = file_get_contents($_FILES['photo']['tmp_name']);
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) $data['cv'] = file_get_contents($_FILES['cv']['tmp_name']);
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) $data['convention'] = file_get_contents($_FILES['convention']['tmp_name']);
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) $data['lettre_motivation'] = file_get_contents($_FILES['lettre_motivation']['tmp_name']);
        if (isset($_FILES['langues_file']) && $_FILES['langues_file']['error'] === UPLOAD_ERR_OK) $data['langues_file'] = file_get_contents($_FILES['langues_file']['tmp_name']);

        $success = $this->folderUseCase->updateDossier($data);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
        exit;
    }
}
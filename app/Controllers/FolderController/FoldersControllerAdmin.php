<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\FolderController;

use Model\UseCase\ManageFolderUseCase;
use Core\View;

class FoldersControllerAdmin
{
    private ManageFolderUseCase $folderUseCase;

    public function __construct()
    {
        $this->folderUseCase = new ManageFolderUseCase();
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders', 'save_student', 'folders-admin', 'toggle_complete', 'update_student', 'import_folders', 'update_document_status', 'update_global_status']);
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = $_GET['page'] ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang = $_GET['lang'] ?? 'fr';

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($page === 'update_global_status') {
                $this->updateGlobalStatus();
                return;
            }
            if ($page === 'update_document_status') {
                $this->updateDocumentStatus();
                return;
            }
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

        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = $this->folderUseCase->getStudentDetails($_GET['numetu']);
        }

        $filters = [
            'type'    => $_GET['type'] ?? 'all',
            'zone'    => $_GET['zone'] ?? 'all',
            'search'  => $_GET['search'] ?? '',
            'complet' => $_GET['complet'] ?? 'all',
            'composante' => $_GET['composante'] ?? 'all',
            'accord'  => $_GET['accord'] ?? 'all',
        ];

        $result = $this->folderUseCase->searchWithoutPagination($filters);
 
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        View::render('Folder/folders_admin', [
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

    private function updateGlobalStatus(): void
    {
        header('Content-Type: application/json');
        
        $numEtu = $_POST['numetu'] ?? '';
        $status = $_POST['status'] ?? 'depot';
        
        if (empty($numEtu)) {
            echo json_encode(['success' => false, 'message' => 'Paramètre manquant']);
            exit;
        }
        
        $success = $this->folderUseCase->setFolderStatus($numEtu, $status);
        echo json_encode(['success' => $success]);
        exit;
    }

    private function updateDocumentStatus(): void
    {
        header('Content-Type: application/json');
        
        $numEtu = $_POST['numetu'] ?? '';
        $docType = $_POST['doc_type'] ?? '';
        $status = $_POST['status'] ?? 'pending';
        $comment = $_POST['comment'] ?? '';
        
        if (empty($numEtu) || empty($docType)) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            exit;
        }
        
        $success = $this->folderUseCase->updateDocumentStatus($numEtu, $docType, $status, $comment);
        echo json_encode(['success' => $success]);
        exit;
    }

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
                // SÉCURITÉ : On envoie bien le $fileName au UseCase pour activer le lecteur CSV !
                $success = $this->folderUseCase->importFoldersFromCSV($filePath, $fileName); 

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

    private function saveStudent(string $lang): void
    {
        $data = [
            'NumEtu' => $_POST['numetu'] ?? '', 'Nom' => $_POST['nom'] ?? '', 'Prenom' => $_POST['prenom'] ?? '',
            'DateNaissance' => $_POST['naissance'] ?? null, 'Sexe' => $_POST['sexe'] ?? null,
            'Adresse' => $_POST['adresse'] ?? null, 'CodePostal' => $_POST['cp'] ?? null,
            'Ville' => $_POST['ville'] ?? null, 'EmailPersonnel' => $_POST['email_perso'] ?? '',
            'EmailAMU' => $_POST['email_amu'] ?? null, 'Telephone' => $_POST['telephone'] ?? '',
            'CodeDepartement' => $_POST['departement'] ?? null, 'Composante' => $_POST['composante'] ?? null,
            'Type' => $_POST['type'] ?? null, 'Zone' => $_POST['zone'] ?? 'europe',
            'Pays' => $_POST['pays'] ?? null, 'Campus' => $_POST['campus'] ?? null,
            'Discipline' => $_POST['discipline'] ?? null, 'NiveauEtude' => $_POST['niveau_etude'] ?? null,
            'Formation' => $_POST['formation'] ?? null, 'MoyenneBac' => $_POST['moyenne_bac'] ?? null,
            'MoyenneSansBac' => $_POST['moyenne_sans_bac'] ?? null, 'AvisDRI' => $_POST['avis_dri'] ?? null,
            'DateDebut' => $_POST['date_debut'] ?? null, 'MobiliteAnterieure' => $_POST['mobilite_anterieure'] ?? null,
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

        $uploadErrors = $this->handleFileUploads($data, $lang);
        if (!empty($uploadErrors)) {
            $_SESSION['message'] = implode('<br>', $uploadErrors);
            header('Location: index.php?page=folders-admin&action=create&lang=' . $lang);
            exit;
        }

        $success = $this->folderUseCase->creerDossier($data);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier créé avec succès' : 'Folder created successfully')
            : (($lang === 'fr') ? 'Erreur lors de la création' : 'Error creating folder');

        header('Location: index.php?page=folders-admin&lang=' . $lang);
        exit;
    }

    private function updateStudent(string $lang): void
    {
        $data = [
            'NumEtu' => $_POST['numetu'] ?? '', 'Nom' => $_POST['nom'] ?? '', 'Prenom' => $_POST['prenom'] ?? '',
            'EmailPersonnel' => $_POST['email_perso'] ?? '', 'Telephone' => $_POST['telephone'] ?? '',
            'Type' => $_POST['type'] ?? null, 'DateNaissance' => $_POST['naissance'] ?? null,
            'Sexe' => $_POST['sexe'] ?? null, 'Adresse' => $_POST['adresse'] ?? null,
            'CodePostal' => $_POST['cp'] ?? null, 'Ville' => $_POST['ville'] ?? null,
            'EmailAMU' => $_POST['email_amu'] ?? null, 'CodeDepartement' => $_POST['departement'] ?? null,
            'Zone' => $_POST['zone'] ?? 'europe', 'Composante' => $_POST['composante'] ?? null,
            'Pays' => $_POST['pays'] ?? null, 'Campus' => $_POST['campus'] ?? null,
            'Discipline' => $_POST['discipline'] ?? null, 'NiveauEtude' => $_POST['niveau_etude'] ?? null,
            'Formation' => $_POST['formation'] ?? null, 'MoyenneBac' => $_POST['moyenne_bac'] ?? null,
            'MoyenneSansBac' => $_POST['moyenne_sans_bac'] ?? null, 'AvisDRI' => $_POST['avis_dri'] ?? null,
            'DateDebut' => $_POST['date_debut'] ?? null, 'MobiliteAnterieure' => $_POST['mobilite_anterieure'] ?? null,
        ];

        $uploadErrors = $this->handleFileUploads($data, $lang);
        if (!empty($uploadErrors)) {
            $_SESSION['message'] = implode('<br>', $uploadErrors);
            header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
            exit;
        }

        $success = $this->folderUseCase->updateDossier($data);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
        exit;
    }
}
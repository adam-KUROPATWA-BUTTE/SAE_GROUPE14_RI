<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\FolderController;

use Model\UseCase\ManageFolderUseCase;

class FoldersControllerAdmin
{
    private ManageFolderUseCase $folderUseCase;

    public function __construct()
    {
        $this->folderUseCase = new ManageFolderUseCase();
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders', 'save_student', 'folders-admin', 'toggle_complete', 'validate_piece', 'update_student', 'import_folders']);
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = $_GET['page'] ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang = $_GET['lang'] ?? 'fr';

        if ($page === 'validate_piece') {
            $numetu = $_GET['numetu'] ?? null;
            $pieceType = $_GET['piece'] ?? null;
            
            if ($numetu && $pieceType) {
                $numetu = urldecode($numetu);
                $studentData = $this->folderUseCase->getStudentDetails($numetu);
                
                if ($studentData) {
                    $email = $studentData['EmailPersonnel'] ?? '';
                    $nom = $studentData['Nom'] ?? '';
                    $prenom = $studentData['Prenom'] ?? '';
                    $studentName = trim($prenom . ' ' . $nom);
                    
                    // Check if piece exists
                    $pieces = is_array($studentData['pieces'] ?? null) ? $studentData['pieces'] : [];
                    
                    if (!empty($pieces[$pieceType]) && !empty($email)) {
                        // Send validation email for this specific piece
                        $success = \Service\Email\EmailReminderService::sendDocumentValidated(
                            $email,
                            $studentName,
                            $pieceType,
                            $numetu
                        );
                        
                        $_SESSION['message'] = $success
                            ? (($lang === 'fr') ? "Email de validation envoyé pour la pièce." : "Validation email sent for document.")
                            : (($lang === 'fr') ? "Erreur lors de l'envoi de l'email." : "Error sending email.");
                    } else {
                        $_SESSION['message'] = ($lang === 'fr') ? "Pièce non trouvée ou email manquant." : "Document not found or email missing.";
                    }
                } else {
                    $_SESSION['message'] = ($lang === 'fr') ? "Étudiant non trouvé." : "Student not found.";
                }
                
                header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
                exit;
            }
        }

        if ($page === 'toggle_complete') {
            $numetu = $_GET['numetu'] ?? null;
            if ($numetu) {
                $numetu = urldecode($numetu);

                // Get student details before toggling to check current status
                $studentData = $this->folderUseCase->getStudentDetails($numetu);
                $wasIncomplete = $studentData && (intval($studentData['IsComplete'] ?? 0) === 0);

                error_log("🔍 Debug toggle_complete: numetu=$numetu, wasIncomplete=" . ($wasIncomplete ? 'yes' : 'no'));

                $success = $this->folderUseCase->toggleCompleteStatus($numetu);

                // If successfully marked as complete (was incomplete before), send validation email
                if ($success && $wasIncomplete && $studentData) {
                    $email = $studentData['EmailPersonnel'] ?? '';
                    $nom = $studentData['Nom'] ?? '';
                    $prenom = $studentData['Prenom'] ?? '';
                    $studentName = trim($prenom . ' ' . $nom);

                    // Get validated documents
                    $pieces = is_array($studentData['pieces'] ?? null) ? $studentData['pieces'] : [];
                    $validatedDocs = array_keys(array_filter($pieces, fn($v) => !empty($v)));

                    error_log("🔍 Debug email: email=$email, studentName=$studentName, docs=" . json_encode($validatedDocs));

                    // Send email even if no documents uploaded (folder marked complete by admin)
                    if (!empty($email)) {
                        // If no documents, list all expected documents as validated
                        if (empty($validatedDocs)) {
                            $validatedDocs = ['photo', 'cv', 'convention', 'lettre_motivation'];
                            error_log("ℹ️ No documents found, using default list for email");
                        }
                        
                        \Service\Email\EmailReminderService::sendValidationConfirmation(
                            $email,
                            $studentName,
                            $validatedDocs,
                            $numetu
                        );
                    } else {
                        error_log("⚠️ Email not sent: missing student email");
                    }
                } else {
                    error_log("⚠️ Email not sent: success=$success, wasIncomplete=" . ($wasIncomplete ? 'yes' : 'no'));
                }

                $_SESSION['message'] = $success
                    ? (($lang === 'fr') ? "Statut du dossier mis à jour." : "Folder status updated.")
                    : (($lang === 'fr') ? "Erreur lors de la mise à jour." : "Error updating status.");
                header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
                exit;
            }
        }

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

        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = $this->folderUseCase->getStudentDetails($_GET['numetu']);
        }

        $currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $filters = [
            'type'    => $_GET['type'] ?? 'all',
            'zone'    => $_GET['zone'] ?? 'all',
            'search'  => $_GET['search'] ?? '',
            'complet' => $_GET['complet'] ?? 'all',
        ];

        $perPage = 10;
        $result = $this->folderUseCase->rechercherAvecPagination($filters, $currentPage, $perPage);

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // Appel à la vue via la classe Core\View
        \Core\View::render('Folder/folders_admin', [
            'action'        => $action,
            'filters'       => $filters,
            'page'          => $currentPage,
            'message'       => $message,
            'lang'          => $lang,
            'studentData'   => $studentData,
            'paginatedData' => $result['data'],
            'totalCount'    => $result['total'],
            'totalPages'    => $result['totalPages']
        ]);
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

    private function saveStudent(string $lang): void
    {
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

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) $data['photo'] = file_get_contents($_FILES['photo']['tmp_name']);
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) $data['cv'] = file_get_contents($_FILES['cv']['tmp_name']);
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) $data['convention'] = file_get_contents($_FILES['convention']['tmp_name']);
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) $data['lettre_motivation'] = file_get_contents($_FILES['lettre_motivation']['tmp_name']);

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

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) $data['photo'] = file_get_contents($_FILES['photo']['tmp_name']);
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) $data['cv'] = file_get_contents($_FILES['cv']['tmp_name']);
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) $data['convention'] = file_get_contents($_FILES['convention']['tmp_name']);
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) $data['lettre_motivation'] = file_get_contents($_FILES['lettre_motivation']['tmp_name']);

        $success = $this->folderUseCase->updateDossier($data);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
        exit;
    }
}
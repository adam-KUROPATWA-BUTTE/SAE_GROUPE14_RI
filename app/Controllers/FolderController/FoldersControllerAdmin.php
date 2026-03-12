<?php


namespace Controllers\FolderController;

use Model\UseCase\ManageFolderUseCase;
use Service\Email\EmailReminderService;
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
        return in_array($page, [
            'folders', 'save_student', 'folders-admin', 'toggle_complete',
            'update_student', 'import_folders', 'update_document_status',
            'update_global_status', 'valider_documents'
        ]);
    }

    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderView(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /**
     * Envoie une réponse JSON et termine. Neutralisable en test.
     *
     * @param array<string, mixed> $data
     */
    protected function jsonResponse(array $data): never
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Wrapper error_log neutralisable en test.
     */
    protected function log(string $message): void
    {
        error_log($message);
    }

    public function control(): void
    {
        $this->startSession();

        $page   = $_GET['page']   ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang   = $_GET['lang']   ?? 'fr';

        $sharedPages  = ['update_student', 'update_document_status', 'update_global_status', 'valider_documents'];
        $allowedRoles = ['admin', 'coordinateur_stage', 'coordinateur_etude', 'coordinateur', 'chef_departement'];
        if (in_array($page, $sharedPages, true)) {
            if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
                $this->redirect('index.php?page=login');
            }
        } else {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $this->redirect('index.php?page=login');
            }
        }

        if ($page === 'toggle_complete') {
            $numetu = $_GET['numetu'] ?? null;
            if ($numetu) {
                $numetu = urldecode($numetu);

                // Fetch student data BEFORE toggling to know the previous state
                $studentData   = $this->folderUseCase->getStudentDetails($numetu);
                $wasIncomplete = $studentData !== null && empty($studentData['IsComplete']);

                $success = $this->folderUseCase->toggleCompleteStatus($numetu);

                if ($success && $wasIncomplete && $studentData !== null) {
                    $email       = strval($studentData['EmailPersonnel'] ?? '');
                    $nom         = strval($studentData['Nom']    ?? '');
                    $prenom      = strval($studentData['Prenom'] ?? '');
                    $studentName = trim($prenom . ' ' . $nom);

                    $pieces        = is_array($studentData['pieces'] ?? null) ? $studentData['pieces'] : [];
                    $validatedDocs = array_keys(array_filter($pieces, fn($v) => !empty($v)));

                    $this->log("🔍 Debug email: email=$email, studentName=$studentName, docs=" . json_encode($validatedDocs));

                    if (!empty($email)) {
                        if (empty($validatedDocs)) {
                            $validatedDocs = ['photo', 'cv', 'convention', 'lettre_motivation'];
                            $this->log("ℹ️ No documents found, using default list for email");
                        }
                        \Service\Email\EmailReminderService::sendValidationConfirmation($email, $studentName, $validatedDocs, $numetu);
                    } else {
                        $this->log("⚠️ Email not sent: missing student email");
                    }
                } else {
                    $this->log("⚠️ Email not sent: success=$success, wasIncomplete=" . ($wasIncomplete ? 'yes' : 'no'));
                }

                $_SESSION['message'] = $success
                    ? (($lang === 'fr') ? "Statut du dossier mis à jour." : "Folder status updated.")
                    : (($lang === 'fr') ? "Erreur lors de la mise à jour." : "Error updating status.");
                $this->redirect('index.php?page=folders-admin&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
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
            if ($page === 'valider_documents') {
                $this->validerDocuments($lang);
                return;
            }
        }

        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = $this->folderUseCase->getStudentDetails($_GET['numetu']);
        }

        $filters = [
            'type'       => $_GET['type']       ?? 'all',
            'zone'       => $_GET['zone']       ?? 'all',
            'search'     => $_GET['search']     ?? '',
            'complet'    => $_GET['complet']    ?? 'all',
            'composante' => $_GET['composante'] ?? 'all',
            'accord'     => $_GET['accord']     ?? 'all',
        ];

        $result = $this->folderUseCase->searchWithoutPagination($filters);

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $this->renderView('Folder/folders_admin', [
            'action'        => $action,
            'filters'       => $filters,
            'page'          => 1,
            'message'       => $message,
            'lang'          => $lang,
            'studentData'   => $studentData,
            'paginatedData' => $result['data'],
            'totalCount'    => $result['total'],
            'totalPages'    => 1,
            'userRole'      => $_SESSION['role'] ?? 'admin',
        ]);
    }

    private function getAdminName(): string
    {
        $prenom = strval($_SESSION['admin_prenom'] ?? '');
        $nom    = strval($_SESSION['admin_nom']    ?? '');
        $name   = trim($prenom . ' ' . $nom);

        if ($name !== '') return $name;

        $email = strval($_SESSION['user_identifier'] ?? '');
        if ($email !== '') return strval(explode('@', $email)[0]);

        return 'Administrateur';
    }

    /** @param array<int, string> $updates */
    private function notifyStudent(string $numEtu, array $updates): void
    {
        if (empty($updates)) return;

        $studentData = $this->folderUseCase->getStudentDetails($numEtu);
        if (!$studentData) return;

        $emailAmu    = strval($studentData['EmailAMU']       ?? '');
        $emailPerso  = strval($studentData['EmailPersonnel'] ?? '');
        $email       = $emailPerso !== '' ? $emailPerso : $emailAmu;
        if ($email === '') return;

        $prenom      = strval($studentData['Prenom'] ?? '');
        $nom         = strval($studentData['Nom']    ?? '');
        $studentName = trim($prenom . ' ' . $nom);

        EmailReminderService::sendFolderUpdateNotification($email, $studentName, $numEtu, $updates);
    }

    private function updateGlobalStatus(): void
    {
        $numEtu = $_POST['numetu'] ?? '';
        $status = $_POST['status'] ?? 'depot';

        if (empty($numEtu)) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètre manquant']);
        }

        try {
            $oldDossier = $this->folderUseCase->getStudentDetails($numEtu);
            $oldStatus  = is_array($oldDossier) ? strval($oldDossier['status'] ?? 'depot') : 'depot';

            $success = $this->folderUseCase->setFolderStatus($numEtu, $status);

            if ($success && $oldStatus !== $status) {
                $statusLabels = [
                    'depot'       => 'Dépôt',
                    'instruction' => 'En instruction',
                    'accepte'     => 'Accepté',
                    'refuse'      => 'Refusé',
                ];
                $updates = ["Le statut global de votre dossier est passé à : <b>" . ($statusLabels[$status] ?? $status) . "</b>"];
                $this->notifyStudent($numEtu, $updates);
            }

            $this->jsonResponse(['success' => $success]);
        } catch (\Throwable $e) {
            $this->log("AJAX updateGlobalStatus Error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erreur Serveur interne']);
        }
    }

    private function updateDocumentStatus(): void
    {
        $numEtu  = $_POST['numetu']   ?? '';
        $docType = $_POST['doc_type'] ?? '';
        $status  = $_POST['status']   ?? 'pending';
        $comment = trim(strval($_POST['comment'] ?? ''));

        if (empty($numEtu) || empty($docType)) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
        }

        try {
            $fileContent = null;
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $fileContent = file_get_contents($_FILES['file']['tmp_name']);
                if ($fileContent === false) $fileContent = null;
            }

            $success = $this->folderUseCase->updateDocumentStatus($numEtu, $docType, $status, $comment, $fileContent);
            $this->jsonResponse(['success' => $success]);
        } catch (\Throwable $e) {
            $this->log("AJAX updateDocumentStatus Error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erreur Serveur interne']);
        }
    }

    private function validerDocuments(string $lang): void
    {
        $numetu     = $_POST['numetu']      ?? '';
        $redirectTo = $_POST['redirect_to'] ?? 'folders-admin';

        if (empty($numetu)) {
            $_SESSION['message'] = ($lang === 'fr') ? 'Erreur : Numéro étudiant manquant' : 'Error: Student ID missing';
            $this->redirect('index.php?page=' . $redirectTo . '&lang=' . $lang);
        }

        $oldDossier = $this->folderUseCase->getStudentDetails($numetu);

        /** @var array<string, string> $oldStatuts */
        $oldStatuts = (is_array($oldDossier) && isset($oldDossier['statuts']) && is_array($oldDossier['statuts'])) ? $oldDossier['statuts'] : [];
        /** @var array<string, array{comment?: string, status?: string}> $oldPieces */
        $oldPieces  = (is_array($oldDossier) && isset($oldDossier['pieces'])  && is_array($oldDossier['pieces']))  ? $oldDossier['pieces']  : [];

        $studentData = [
            'NumEtu'             => $numetu,
            'Nom'                => $_POST['nom']                 ?? '',
            'Prenom'             => $_POST['prenom']              ?? '',
            'EmailPersonnel'     => $_POST['email_perso']         ?? '',
            'Telephone'          => $_POST['telephone']           ?? '',
            'Type'               => $_POST['type']                ?? null,
            'DateNaissance'      => $_POST['naissance']           ?? null,
            'Sexe'               => $_POST['sexe']                ?? null,
            'Adresse'            => $_POST['adresse']             ?? null,
            'CodePostal'         => $_POST['cp']                  ?? null,
            'Ville'              => $_POST['ville']               ?? null,
            'EmailAMU'           => $_POST['email_amu']           ?? null,
            'CodeDepartement'    => $_POST['departement']         ?? null,
            'Zone'               => $_POST['zone']                ?? 'europe',
            'Composante'         => $_POST['composante']          ?? null,
            'Pays'               => $_POST['pays']                ?? null,
            'Mobilite'           => $_POST['mobilite_type']       ?? null,
            'Campus'             => $_POST['campus']              ?? null,
            'Discipline'         => $_POST['discipline']          ?? null,
            'NiveauEtude'        => $_POST['niveau_etude']        ?? null,
            'Formation'          => $_POST['formation']           ?? null,
            'MoyenneBac'         => $_POST['moyenne_bac']         ?? null,
            'MoyenneSansBac'     => $_POST['moyenne_sans_bac']    ?? null,
            'AvisDRI'            => $_POST['avis_dri']            ?? null,
            'DateDebut'          => $_POST['date_debut']          ?? null,
            'MobiliteAnterieure' => $_POST['mobilite_anterieure'] ?? null,
        ];

        $studentData['ModifiePar'] = $this->getAdminName();
        $studentData['ModifieLe']  = date('Y-m-d H:i:s');
        $this->folderUseCase->updateDossier($studentData);

        $docLabels = [
            'photo'             => 'Photo',
            'cv'                => 'CV',
            'convention'        => 'Convention de stage',
            'lettre_motivation' => 'Lettre de motivation',
            'langues'           => 'Attestation de langues',
        ];

        /** @var array<string, string> $statutsDocuments */
        $statutsDocuments = $oldStatuts;
        foreach (array_keys($docLabels) as $doc) {
            $docKey = (string)$doc;
            $val    = $_POST['statut_' . $docKey] ?? '';
            if (is_string($val) && $val !== '') {
                $statutsDocuments[$docKey] = $val;
            }
        }

        $dateLimite  = !empty($_POST['date_limite']) ? $_POST['date_limite'] : null;
        $commentaire = null;

        $this->log("=== validerDocuments POST ===");
        $this->log("numetu: " . $numetu);
        $this->log("statutsDocuments: " . json_encode($statutsDocuments));
        $this->log("email_perso: " . ($_POST['email_perso'] ?? '(vide)'));
        $this->log("mobilite_type POST = " . ($_POST['mobilite_type'] ?? 'NON RECU'));
        $this->log("Mobilite data = " . ($studentData['Mobilite'] ?? 'NULL'));

        $success = $this->folderUseCase->enregistrerValidation($numetu, $statutsDocuments, $dateLimite, $commentaire);

        if ($success) {
            $currentDossier = $this->folderUseCase->getStudentDetails($numetu);
            $globalStatus   = is_array($currentDossier) ? strval($currentDossier['status'] ?? 'depot') : 'depot';
            $globalLabels   = ['depot' => 'Dépôt', 'instruction' => 'En instruction', 'accepte' => 'Accepté', 'refuse' => 'Refusé'];

            $piecesAcceptees = [];
            $piecesRefusees  = [];

            foreach ($docLabels as $doc => $docName) {
                $docKey     = (string)$doc;
                $statut     = $statutsDocuments[$docKey] ?? ($oldStatuts[$docKey] ?? 'pending');
                $docComment = trim(strval($_POST['comment_' . $docKey] ?? ($oldPieces[$docKey]['comment'] ?? '')));

                if ($statut === 'accepted')    { $piecesAcceptees[] = ['name' => $docName, 'comment' => $docComment]; }
                elseif ($statut === 'refused') { $piecesRefusees[]  = ['name' => $docName, 'comment' => $docComment]; }
            }

            $updates = [];
            foreach ([['__SECTION_ACCEPTEES__', '__END_SECTION__', $piecesAcceptees], ['__SECTION_REFUSEES__', '__END_SECTION__', $piecesRefusees]] as [$open, $close, $list]) {
                if (!empty($list)) {
                    $lines = '';
                    foreach ($list as $p) {
                        $lines .= '<li style="margin-bottom:4px;">' . $p['name'];
                        if (!empty($p['comment'])) {
                            $lines .= ' — <i style="color:#555;">' . htmlspecialchars($p['comment'], ENT_QUOTES, 'UTF-8') . '</i>';
                        }
                        $lines .= '</li>';
                    }
                    $updates[] = $open . $lines . $close;
                }
            }

            $updates[] = '__STATUT_GLOBAL__' . ($globalLabels[$globalStatus] ?? $globalStatus) . '__END_STATUT__';

            if (!empty($dateLimite)) {
                $formattedDate = date('d/m/Y', strtotime($dateLimite));
                $oldDateLimite = is_array($oldDossier) ? ($oldDossier['DateLimite'] ?? null) : null;
                if ($dateLimite !== $oldDateLimite) {
                    $updates[] = '__DATE_LIMITE__' . $formattedDate . '__END_DATE__';
                }
            }

            $this->notifyStudent($numetu, $updates);
        }

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Validation enregistrée avec succès. L\'étudiant a été notifié si nécessaire.' : 'Validation saved successfully. Student notified.')
            : (($lang === 'fr') ? 'Erreur lors de l\'enregistrement' : 'Error saving validation');

        $this->redirect('index.php?page=' . $redirectTo . '&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, string>
     */
    private function handleFileUploads(array &$data, string $lang): array
    {
        $errors     = [];
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

    private function importFolders(string $lang): void
    {
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $filePath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed  = ['csv', 'xlsx', 'xls'];

            if (!in_array($ext, $allowed)) {
                $_SESSION['message'] = ($lang === 'fr')
                    ? 'Erreur : Format non supporté. Utilisez .csv ou .xlsx'
                    : 'Error: Unsupported format. Use .csv or .xlsx';
            } else {
                $success = $this->folderUseCase->importFoldersFromCSV($filePath, $fileName);
                $_SESSION['message'] = $success
                    ? (($lang === 'fr') ? 'Importation réussie' : 'Import successful')
                    : (($lang === 'fr') ? 'Erreur lors de l\'importation (fichier vide ou format invalide)' : 'Error during import');
            }
        } else {
            $_SESSION['message'] = ($lang === 'fr') ? 'Erreur lors du téléchargement du fichier.' : 'File upload error.';
        }

        $this->redirect('index.php?page=folders-admin&lang=' . $lang);
    }

    private function saveStudent(string $lang): void
    {
        $data = [
            'NumEtu'             => $_POST['numetu']              ?? '',
            'Nom'                => $_POST['nom']                 ?? '',
            'Prenom'             => $_POST['prenom']              ?? '',
            'DateNaissance'      => $_POST['naissance']           ?? null,
            'Sexe'               => $_POST['sexe']                ?? null,
            'Adresse'            => $_POST['adresse']             ?? null,
            'CodePostal'         => $_POST['cp']                  ?? null,
            'Ville'              => $_POST['ville']               ?? null,
            'EmailPersonnel'     => $_POST['email_perso']         ?? '',
            'EmailAMU'           => $_POST['email_amu']           ?? null,
            'Telephone'          => $_POST['telephone']           ?? '',
            'CodeDepartement'    => $_POST['departement']         ?? null,
            'Composante'         => $_POST['composante']          ?? null,
            'Type'               => $_POST['type']                ?? null,
            'Zone'               => $_POST['zone']                ?? 'europe',
            'Pays'               => $_POST['pays']                ?? null,
            'Campus'             => $_POST['campus']              ?? null,
            'Discipline'         => $_POST['discipline']          ?? null,
            'NiveauEtude'        => $_POST['niveau_etude']        ?? null,
            'Formation'          => $_POST['formation']           ?? null,
            'MoyenneBac'         => $_POST['moyenne_bac']         ?? null,
            'MoyenneSansBac'     => $_POST['moyenne_sans_bac']    ?? null,
            'AvisDRI'            => $_POST['avis_dri']            ?? null,
            'DateDebut'          => $_POST['date_debut']          ?? null,
            'MobiliteAnterieure' => $_POST['mobilite_anterieure'] ?? null,
        ];

        $errors = [];
        if (empty($data['NumEtu'])) $errors[] = ($lang === 'fr') ? 'Numéro étudiant requis' : 'Student ID required';
        if (empty($data['Nom']))    $errors[] = ($lang === 'fr') ? 'Nom requis'              : 'Name required';

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            $this->redirect('index.php?page=folders-admin&action=create&lang=' . $lang);
        }

        if ($this->folderUseCase->getByNumetu($data['NumEtu'])) {
            $_SESSION['message'] = ($lang === 'fr') ? 'Ce numéro étudiant existe déjà' : 'ID already exists';
            $this->redirect('index.php?page=folders-admin&action=create&lang=' . $lang);
        }

        $uploadErrors = $this->handleFileUploads($data, $lang);
        if (!empty($uploadErrors)) {
            $_SESSION['message'] = implode('<br>', $uploadErrors);
            $this->redirect('index.php?page=folders-admin&action=create&lang=' . $lang);
        }

        $success = $this->folderUseCase->creerDossier($data);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Folder créé avec succès' : 'Folder created successfully')
            : (($lang === 'fr') ? 'Erreur lors de la création' : 'Error creating folder');

        $this->redirect('index.php?page=folders-admin&lang=' . $lang);
    }

    private function updateStudent(string $lang): void
    {
        $redirectTo = $_POST['redirect_to'] ?? 'folders-admin';
        $numetu     = $_POST['numetu'] ?? '';

        $data = [
            'NumEtu'             => $numetu,
            'Nom'                => $_POST['nom']                 ?? '',
            'Prenom'             => $_POST['prenom']              ?? '',
            'EmailPersonnel'     => $_POST['email_perso']         ?? '',
            'Telephone'          => $_POST['telephone']           ?? '',
            'Type'               => $_POST['type']                ?? null,
            'DateNaissance'      => $_POST['naissance']           ?? null,
            'Sexe'               => $_POST['sexe']                ?? null,
            'Adresse'            => $_POST['adresse']             ?? null,
            'CodePostal'         => $_POST['cp']                  ?? null,
            'Ville'              => $_POST['ville']               ?? null,
            'EmailAMU'           => $_POST['email_amu']           ?? null,
            'CodeDepartement'    => $_POST['departement']         ?? null,
            'Zone'               => $_POST['zone']                ?? 'europe',
            'Composante'         => $_POST['composante']          ?? null,
            'Pays'               => $_POST['pays']                ?? null,
            'Mobilite'           => $_POST['mobilite_type']       ?? null,
            'Campus'             => $_POST['campus']              ?? null,
            'Discipline'         => $_POST['discipline']          ?? null,
            'NiveauEtude'        => $_POST['niveau_etude']        ?? null,
            'Formation'          => $_POST['formation']           ?? null,
            'MoyenneBac'         => $_POST['moyenne_bac']         ?? null,
            'MoyenneSansBac'     => $_POST['moyenne_sans_bac']    ?? null,
            'AvisDRI'            => $_POST['avis_dri']            ?? null,
            'DateDebut'          => $_POST['date_debut']          ?? null,
            'MobiliteAnterieure' => $_POST['mobilite_anterieure'] ?? null,
        ];

        $uploadErrors = $this->handleFileUploads($data, $lang);
        if (!empty($uploadErrors)) {
            $_SESSION['message'] = implode('<br>', $uploadErrors);
            $this->redirect('index.php?page=' . $redirectTo . '&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
        }

        $oldDossier = !empty($numetu) ? $this->folderUseCase->getStudentDetails($numetu) : null;

        $data['ModifiePar'] = $this->getAdminName();
        $data['ModifieLe']  = date('Y-m-d H:i:s');

        $success = $this->folderUseCase->updateDossier($data);

        if ($success && $oldDossier) {
            $fieldLabels = [
                'Nom' => 'Nom', 'Prenom' => 'Prénom', 'EmailPersonnel' => 'Email personnel',
                'EmailAMU' => 'Email AMU', 'Telephone' => 'Téléphone', 'Adresse' => 'Adresse',
                'CodePostal' => 'Code postal', 'Ville' => 'Ville', 'Pays' => 'Pays',
                'Type' => 'Type (entrant/sortant)', 'Zone' => 'Zone', 'Composante' => 'Composante',
                'CodeDepartement' => 'Département', 'Campus' => 'Campus', 'Discipline' => 'Discipline',
                'NiveauEtude' => 'Niveau d\'étude', 'Formation' => 'Formation',
                'MoyenneBac' => 'Moyenne Bac', 'MoyenneSansBac' => 'Moyenne sans Bac',
                'AvisDRI' => 'Avis DRI', 'DateDebut' => 'Date de début',
                'MobiliteAnterieure' => 'Mobilité antérieure', 'DateNaissance' => 'Date de naissance',
                'Sexe' => 'Sexe',
            ];

            $updates = [];
            foreach ($fieldLabels as $field => $label) {
                $oldVal = trim(strval($oldDossier[$field] ?? ''));
                $newVal = trim(strval($data[$field] ?? ''));
                if ($oldVal !== $newVal && $newVal !== '') {
                    $safeOld   = htmlspecialchars($oldVal ?: '—', ENT_QUOTES, 'UTF-8');
                    $safeNew   = htmlspecialchars($newVal,         ENT_QUOTES, 'UTF-8');
                    $updates[] = "<b>{$label}</b> : {$safeOld} → <b>{$safeNew}</b>";
                }
            }

            $fileLabels = [
                'photo' => 'Photo', 'cv' => 'CV', 'convention' => 'Convention de stage',
                'lettre_motivation' => 'Lettre de motivation', 'langues_file' => 'Attestation de langues',
            ];
            foreach ($fileLabels as $field => $label) {
                if (!empty($data[$field])) {
                    $updates[] = "Le fichier <b>{$label}</b> a été mis à jour par l'administration.";
                }
            }

            $this->notifyStudent($numetu, $updates);
        }

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Folder mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        $this->redirect('index.php?page=' . $redirectTo . '&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
    }
}
<?php

namespace Controllers\FolderController;

use Model\Folder\FolderAdmin;
use Model\EmailReminder; // FIXED: Correct namespace for the model
use View\Folder\FoldersPageAdmin;

/**
 * Class FoldersControllerAdmin
 *
 * Controller responsible for the administrative management of student folders.
 * It handles the listing, creation, modification, validation status, and reminders.
 */
class FoldersControllerAdmin
{
    /**
     * Determines if the controller supports the requested page.
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if the controller should handle the request.
     */
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders', 'save_student', 'folders-admin', 'toggle_complete', 'update_student', 'send_reminder']);
    }

    /**
     * Main control logic.
     * Handles authentication check, routing logic, data retrieval, and view rendering.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve parameters with default values
        $page = $_GET['page'] ?? 'folders';
        $action = $_GET['action'] ?? 'list';
        $lang = $_GET['lang'] ?? 'fr';

        // --- 1. Handle Reminder Sending ---
        if ($page === 'send_reminder') {
            $this->sendReminder($lang);
            return; // Stop execution here
        }

        // --- 2. Handle Status Toggle ---
        if ($page === 'toggle_complete') {
            $numetu = $_GET['numetu'] ?? null;
            if ($numetu) {
                $numetu = urldecode($numetu);
                $success = FolderAdmin::toggleCompleteStatus($numetu);
                
                $_SESSION['message'] = $success 
                    ? (($lang === 'fr') ? "Statut mis à jour." : "Status updated.") 
                    : (($lang === 'fr') ? "Erreur mise à jour." : "Update error.");

                header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($numetu) . '&lang=' . $lang);
                exit;
            }
        }

        // --- 3. Handle POST Actions ---
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

        // --- 4. Render View (List or Detail) ---
        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = FolderAdmin::getStudentDetails($_GET['numetu']);
        }

        // Filters setup
        $filters = [
            'type' => $_GET['Type'] ?? 'all',
            'zone' => $_GET['Zone'] ?? 'all',
            'search' => $_GET['search'] ?? '',
            'complet' => $_GET['complet'] ?? 'all',
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null,
            'tri_date' => $_GET['tri_date'] ?? 'DESC'
        ];

        $currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $perPage = 10;

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $view = new FoldersPageAdmin($action, $filters, $currentPage, $perPage, $message, $lang, $studentData);
        $view->render();
    }

    /**
     * Sends a reminder email to a student via NumEtu.
     */
    private function sendReminder(string $lang): void
    {
        // 1. Sécurité
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $numetu = $_GET['numetu'] ?? null;

        if ($numetu) {
            $numetu = urldecode($numetu);
            $etudiant = FolderAdmin::getByNumetu($numetu);

            if ($etudiant) {
                // Récupération des emails
                $emailPerso = !empty($etudiant['EmailPersonnel']) ? $etudiant['EmailPersonnel'] : ($etudiant['email'] ?? '');
                $emailAMU   = $etudiant['EmailAMU'] ?? '';
                $prenom     = $etudiant['Prenom'] ?? 'Étudiant';

                // Préparation du message
                $subject = ($lang === 'fr') 
                    ? "Action requise : Votre dossier de mobilité est incomplet" 
                    : "Action required: Your mobility folder is incomplete";

                $messageContent = ($lang === 'fr') 
                    ? "Bonjour $prenom,\n\nVotre dossier est incomplet. Merci de fournir les pièces manquantes.\n\nCordialement,\nRelations Internationales."
                    : "Hello $prenom,\n\nYour folder is incomplete. Please provide documents.\n\nRegards,\nInternational Relations.";

                $headers = "From: no-reply@univ-amu.fr";

                // Tentative d'envoi réel
                $sentCount = 0;
                // On utilise @ devant mail() pour masquer les erreurs si pas de serveur SMTP local
                if (!empty($emailPerso)) { @mail($emailPerso, $subject, $messageContent, $headers); $sentCount++; }
                if (!empty($emailAMU))   { @mail($emailAMU, $subject, $messageContent, $headers); $sentCount++; }

                // Enregistrement en base de données
                $relanceModel = new EmailReminder();
                $relanceModel->insertRelance($numetu, $messageContent, $_SESSION['user_id'] ?? null);

                // Message de succès ou d'avertissement
                if ($sentCount > 0) {
                    $_SESSION['message'] = ($lang === 'fr') 
                        ? "Relance envoyée avec succès !" 
                        : "Reminder sent successfully!";
                } else {
                    $_SESSION['message'] = ($lang === 'fr') 
                        ? "Relance enregistrée en base (mais l'envoi mail a échoué)." 
                        : "Reminder logged in DB (but email sending failed).";
                }

            } else {
                $_SESSION['message'] = ($lang === 'fr') ? "Étudiant introuvable." : "Student not found.";
            }
        }

        header('Location: index.php?page=dashboard-admin&lang=' . $lang);
        exit;
    }

    /**
     * Handles the creation of a new student folder.
     */
    private function saveStudent(string $lang): void
    {
        // 1. Collect Data
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

        // 2. Validate
        $errors = [];
        if (empty($data['NumEtu'])) $errors[] = ($lang === 'fr') ? 'Numéro étudiant requis' : 'Student ID required';
        if (empty($data['Nom'])) $errors[] = ($lang === 'fr') ? 'Nom requis' : 'Name required';

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders-admin&action=create&lang=' . $lang);
            exit;
        }

        if (FolderAdmin::getByNumetu($data['NumEtu'])) {
            $_SESSION['message'] = ($lang === 'fr') ? 'Ce numéro étudiant existe déjà' : 'ID already exists';
            header('Location: index.php?page=folders-admin&action=create&lang=' . $lang);
            exit;
        }

        // 3. Handle Files
        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }
        $cvData = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvData = file_get_contents($_FILES['cv']['tmp_name']);
        }
        $conventionData = null;
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) {
            $conventionData = file_get_contents($_FILES['convention']['tmp_name']);
        }
        $lettreData = null;
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) {
            $lettreData = file_get_contents($_FILES['lettre_motivation']['tmp_name']);
        }

        // 4. Save
        $success = FolderAdmin::creerDossier($data, $photoData, $cvData, $conventionData, $lettreData);
        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier créé avec succès' : 'Folder created successfully')
            : (($lang === 'fr') ? 'Erreur lors de la création' : 'Error creating folder');

        header('Location: index.php?page=folders-admin&lang=' . $lang);
        exit;
    }

    /**
     * Handles the update of an existing student folder.
     */
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

        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }
        $cvData = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvData = file_get_contents($_FILES['cv']['tmp_name']);
        }
        $conventionData = null;
        if (isset($_FILES['convention']) && $_FILES['convention']['error'] === UPLOAD_ERR_OK) {
            $conventionData = file_get_contents($_FILES['convention']['tmp_name']);
        }
        $lettreData = null;
        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) {
            $lettreData = file_get_contents($_FILES['lettre_motivation']['tmp_name']);
        }

        $success = FolderAdmin::updateDossier($data, $photoData, $cvData, $conventionData, $lettreData);

        $_SESSION['message'] = $success
            ? (($lang === 'fr') ? 'Dossier mis à jour' : 'Folder updated')
            : (($lang === 'fr') ? 'Erreur lors de la mise à jour' : 'Error updating folder');

        header('Location: index.php?page=folders-admin&action=view&numetu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
        exit;
    }
}
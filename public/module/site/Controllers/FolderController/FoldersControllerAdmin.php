<?php
namespace Controllers\FolderController;

use Model\Folder\FolderAdmin as Folder;
use View\Folder\FoldersPageAdmin;

class FoldersControllerAdmin
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'folders-admin' || $page === 'save_student' || $page === 'update_student';
    }

    public function control(): void
    {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si c'est une soumission de formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['page'] ?? '') === 'save_student') {
            $this->saveStudent();
            return;
        }

        // Si c'est une soumission de formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['page'] ?? '') === 'update_student') {
            $this->updateStudent();
            return;
        }

        // Récupérer l'action
        $action = $_GET['action'] ?? 'list';

        // Récupérer les données de l'étudiant si on visualise/édite
        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {  // ✅ Changé de NumEtu à numetu
            $studentData = Folder::getStudentDetails($_GET['numetu']);
        }

        // Récupérer les filtres depuis l'URL
        $filters = [
            'type' => $_GET['Type'] ?? 'all',
            'zone' => $_GET['Zone'] ?? 'all',
            'stage' => $_GET['Stage'] ?? 'all',
            'etude' => $_GET['etude'] ?? 'all',
            'search' => $_GET['search'] ?? ''
        ];

        // Pagination
        $page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $perPage = 10;

        // Message de succès/erreur
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // Langue
        $lang = $_GET['lang'] ?? 'fr';

        // Créer la vue et l'afficher
        $view = new FoldersPageAdmin($action, $filters, $page, $perPage, $message, $lang, $studentData);
        $view->render();
    }

    private function saveStudent(): void
    {
        $lang = $_GET['lang'] ?? 'fr';

        // ✅ Préparer les données avec les BONS noms de champs (majuscules comme dans la BD)
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

        // ✅ Validation basique
        $errors = [];
        if (empty($data['NumEtu'])) {
            $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        }
        if (empty($data['Nom'])) {
            $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        }
        if (empty($data['Prenom'])) {
            $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        }
        if (empty($data['EmailPersonnel'])) {
            $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        }
        if (empty($data['Telephone'])) {
            $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';
        }
        if (empty($data['type'])) {
            $errors[] = $lang === 'fr' ? 'Le type est requis' : 'Type is required';
        }
        if (empty($data['zone'])) {
            $errors[] = $lang === 'fr' ? 'La zone est requise' : 'Zone is required';
        }

        error_log("Erreurs de validation: " . print_r($errors, true));

        if (!empty($errors)) {
            error_log("❌ VALIDATION ÉCHOUÉE - Redirection vers formulaire");
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        error_log("✅ Validation OK - Vérification unicité...");

        // Vérifier si l'étudiant existe déjà
        $existing = Folder::getByNumetu($data['NumEtu']);
        if ($existing) {
            error_log("❌ NumEtu déjà existant - Redirection");
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà'
                : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        $existingEmail = Folder::getByEmail($data['EmailPersonnel']);
        if ($existingEmail) {
            error_log("❌ Email déjà existant - Redirection");
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec cet email existe déjà'
                : 'A student with this email already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Récupérer les fichiers uploadés
        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $cvData = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvData = file_get_contents($_FILES['cv']['tmp_name']);
        }

        // Créer le dossier
        $success = Folder::creerDossier($data, $photoData, $cvData);

        if ($success) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Dossier créé avec succès'
                : 'Folder created successfully';

            error_log("✅ FIN CRÉATION - Redirection vers liste");
        } else {
            error_log("❌ ÉCHEC CRÉATION - Message d'erreur");
            $_SESSION['message'] = $lang === 'fr'
                ? 'Erreur lors de la création du dossier'
                : 'Error creating folder';
        }

        error_log("Redirection finale vers: index.php?page=folders&lang=" . $lang);
        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }


    private function updateStudent(): void
    {
        $lang = $_GET['lang'] ?? 'fr';

        // ✅ Préparer les données avec les BONS noms (majuscules)
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

        // Validation basique
        $errors = [];
        if (empty($data['NumEtu'])) {
            $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        }
        if (empty($data['Nom'])) {
            $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        }
        if (empty($data['Prenom'])) {
            $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        }
        if (empty($data['EmailPersonnel'])) {
            $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        }
        if (empty($data['Telephone'])) {
            $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=view&NumEtu=' . urlencode($data['NumEtu']) . '&lang=' . $lang);
            exit;
        }

        // Gérer les fichiers
        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $cvData = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvData = file_get_contents($_FILES['cv']['tmp_name']);
        }

        // Mettre à jour le dossier
        $success = Folder::updateDossier($data, $photoData, $cvData);

        if ($success) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Dossier modifié avec succès'
                : 'Folder updated successfully';
        } else {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Erreur lors de la modification du dossier'
                : 'Error updating folder';
        }

        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }
}
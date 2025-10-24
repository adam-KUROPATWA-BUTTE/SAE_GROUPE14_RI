<?php
namespace Controllers\site;

use Model\Folder;
use View\FoldersPage;

class FoldersController
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'folders' || $page === 'save_student' || $page === 'update_student';
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
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = Folder::getStudentDetails($_GET['numetu']);
        }

        // Récupérer les filtres depuis l'URL
        $filters = [
            'type' => $_GET['type'] ?? 'all',
            'zone' => $_GET['zone'] ?? 'all',
            'stage' => $_GET['stage'] ?? 'all',
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
        $view = new FoldersPage($action, $filters, $page, $perPage, $message, $lang, $studentData);
        $view->render();
    }

    private function saveStudent(): void
    {
        $lang = $_GET['lang'] ?? 'fr';

        // ✅ DEBUG TEMPORAIRE
        error_log("=== DÉBUT CRÉATION ÉTUDIANT ===");
        error_log("POST reçu: " . print_r($_POST, true));
        error_log("FILES reçu: " . print_r($_FILES, true));

        // Préparer les données de l'étudiant
        $data = [
            'numetu' => $_POST['numetu'] ?? '',
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email_perso'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'type' => !empty($_POST['type']) ? $_POST['type'] : null,
            'zone' => !empty($_POST['zone']) ? $_POST['zone'] : null,
            'naissance' => !empty($_POST['naissance']) ? $_POST['naissance'] : null,
            'sexe' => !empty($_POST['sexe']) ? $_POST['sexe'] : null,
            'adresse' => !empty($_POST['adresse']) ? $_POST['adresse'] : null,
            'cp' => !empty($_POST['cp']) ? $_POST['cp'] : null,
            'ville' => !empty($_POST['ville']) ? $_POST['ville'] : null,
            'email_amu' => !empty($_POST['email_amu']) ? $_POST['email_amu'] : null,
            'departement' => !empty($_POST['departement']) ? $_POST['departement'] : null
        ];

        error_log("Données préparées: " . print_r($data, true));

        // Validation basique
        $errors = [];
        if (empty($data['numetu'])) {
            $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        }
        if (empty($data['nom'])) {
            $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        }
        if (empty($data['prenom'])) {
            $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        }
        if (empty($data['email'])) {
            $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        }
        if (empty($data['telephone'])) {
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
        $existing = Folder::getByNumetu($data['numetu']);
        error_log("Étudiant existant (numetu): " . ($existing ? "OUI" : "NON"));

        if ($existing) {
            error_log("❌ NumEtu déjà existant - Redirection");
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà'
                : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        $existingEmail = Folder::getByEmail($data['email']);
        error_log("Étudiant existant (email): " . ($existingEmail ? "OUI" : "NON"));

        if ($existingEmail) {
            error_log("❌ Email déjà existant - Redirection");
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec cet email existe déjà'
                : 'A student with this email already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        error_log("✅ Unicité OK - Création du dossier...");

        // Créer le dossier
        $success = Folder::creerDossier($data);

        error_log("Résultat création: " . ($success ? "SUCCÈS" : "ÉCHEC"));

        if ($success) {
            error_log("✅ Dossier créé - Gestion des fichiers...");

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                error_log("Upload photo...");
                Folder::uploadPhoto($data['numetu'], $_FILES['photo']);
            }

            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
                error_log("Upload CV...");
                Folder::uploadCV($data['numetu'], $_FILES['cv']);
            }

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

        // Préparer les données de l'étudiant
        $data = [
            'numetu' => $_POST['numetu'] ?? '',
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email_perso'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'type' => !empty($_POST['type']) ? $_POST['type'] : null,
            'zone' => !empty($_POST['zone']) ? $_POST['zone'] : null,
            'naissance' => !empty($_POST['naissance']) ? $_POST['naissance'] : null,
            'sexe' => !empty($_POST['sexe']) ? $_POST['sexe'] : null,
            'adresse' => !empty($_POST['adresse']) ? $_POST['adresse'] : null,
            'cp' => !empty($_POST['cp']) ? $_POST['cp'] : null,
            'ville' => !empty($_POST['ville']) ? $_POST['ville'] : null,
            'email_amu' => !empty($_POST['email_amu']) ? $_POST['email_amu'] : null,
            'departement' => !empty($_POST['departement']) ? $_POST['departement'] : null
        ];

        // Validation basique
        $errors = [];
        if (empty($data['numetu'])) {
            $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        }
        if (empty($data['nom'])) {
            $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        }
        if (empty($data['prenom'])) {
            $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        }
        if (empty($data['email'])) {
            $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        }
        if (empty($data['telephone'])) {
            $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=view&numetu=' . urlencode($data['numetu']) . '&lang=' . $lang);
            exit;
        }

        // Mettre à jour le dossier
        $success = Folder::updateStudent($data);

        if ($success) {
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                Folder::uploadPhoto($data['numetu'], $_FILES['photo']);
            }

            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
                Folder::uploadCV($data['numetu'], $_FILES['cv']);
            }

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
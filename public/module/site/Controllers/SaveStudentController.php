<?php
namespace Controllers\site;

use Model\Folder;

/**
 * SaveStudentController
 * 
 * Handles the creation of a new student folder.
 * 
 * Responsibilities:
 *  - Validate submitted student data
 *  - Check for existing student ID or email
 *  - Create a new student folder
 *  - Handle file uploads (photo and CV)
 *  - Provide feedback via session messages
 */
class SaveStudentController
{
    /**
     * Determines if this controller supports the requested page and method.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if this controller handles POST requests to 'save_student'
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'save_student' && $method === 'POST';
    }

    /**
     * Main control method that processes the student creation.
     */
    public function control(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get language parameter (default to French)
        $lang = $_GET['lang'] ?? 'fr';

        // Prepare student data from POST request
        $data = [
            'numetu' => $_POST['numetu'] ?? '',
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email_perso'] ?? '', // Personal email used as primary
            'telephone' => $_POST['telephone'] ?? '',
            'type' => $_POST['type'] ?? null,
            'password' => 'default123' // Default password
        ];

        // Basic validation
        $errors = [];
        if (empty($data['numetu'])) $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        if (empty($data['nom'])) $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        if (empty($data['prenom'])) $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        if (empty($data['email'])) $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        if (empty($data['telephone'])) $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';

        // If there are errors, redirect back with error message
        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Check if student ID already exists
        $existing = Folder::getByNumetu($data['numetu']);
        if ($existing) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà'
                : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Check if email already exists
        $existingEmail = Folder::getByEmail($data['email']);
        if ($existingEmail) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec cet email existe déjà'
                : 'A student with this email already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Create the student folder
        $success = Folder::creerDossier($data);

        if ($success) {
            // Handle uploaded files (photo and CV)
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                Folder::uploadPhoto($data['numetu'], $_FILES['photo']);
            }

            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
                Folder::uploadCV($data['numetu'], $_FILES['cv']);
            }

            $_SESSION['message'] = $lang === 'fr'
                ? 'Dossier créé avec succès'
                : 'Folder created successfully';
        } else {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Erreur lors de la création du dossier'
                : 'Error creating folder';
        }

        // Redirect to the folders list
        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }
}

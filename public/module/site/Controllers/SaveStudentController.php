<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\site;

use Model\Folder\FolderAdmin;

/**
 * SaveStudentController
 *
 * Handles the creation of a new student folder.
 */
class SaveStudentController
{
    /**
     * Determines if this controller supports the requested page and method.
     *
     * @param string $page
     * @param string $method
     * @return bool
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';

        // --- Collect POST data safely ---
        $data = [
            'numetu' => (string) ($_POST['numetu'] ?? ''),
            'nom' => (string) ($_POST['nom'] ?? ''),
            'prenom' => (string) ($_POST['prenom'] ?? ''),
            'email' => (string) ($_POST['email_perso'] ?? ''),
            'telephone' => (string) ($_POST['telephone'] ?? ''),
            'type' => (string) ($_POST['type'] ?? ''),
            'password' => 'default123',
        ];

        // --- Basic validation ---
        $errors = [];
        if ($data['numetu'] === '') {
            $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        }
        if ($data['nom'] === '') {
            $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        }
        if ($data['prenom'] === '') {
            $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        }
        if ($data['email'] === '') {
            $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        }
        if ($data['telephone'] === '') {
            $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // --- Check for existing student ---

        /** @var array<string, mixed>|null $existing */
        $existing = FolderAdmin::getByNumetu($data['numetu']);

        if ($existing !== null) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà'
                : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        /** @var array<string, mixed>|null $existingEmail */
        $existingEmail = FolderAdmin::getByEmail($data['email']);

        if ($existingEmail !== null) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec cet email existe déjà'
                : 'A student with this email already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // --- Create the student folder ---

        $success = FolderAdmin::creerDossier($data);

        if ($success) {
            $this->handleFileUpload($data['numetu'], 'photo', 'uploadPhoto');
            $this->handleFileUpload($data['numetu'], 'cv', 'uploadCV');

            $_SESSION['message'] = $lang === 'fr'
                ? 'Dossier créé avec succès'
                : 'Folder created successfully';
        } else {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Erreur lors de la création du dossier'
                : 'Error creating folder';
        }

        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }

    /**
     * Handle file upload safely.
     *
     * @param string $numetu
     * @param string $inputName
     * @param string $uploadMethod
     * @return void
     */
    private function handleFileUpload(string $numetu, string $inputName, string $uploadMethod): void
    {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$inputName];
            if (is_array($file)) {
                // CORRECTION : Appel de la méthode d'upload sur FolderAdmin
                FolderAdmin::$uploadMethod($numetu, $file);
            }
        }
    }
}

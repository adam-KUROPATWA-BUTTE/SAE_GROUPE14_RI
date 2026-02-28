<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers;

use Model\UseCase\ManageFolderUseCase;

/**
 * Class SaveStudentController
 *
 * Handles the administrative creation of a new student folder via POST requests.
 */
class SaveStudentController
{
    /**
     * Determines if this controller supports the requested page and method.
     *
     * @param string $page
     * @param string $method
     * @return bool True if supported, false otherwise.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'save_student' && $method === 'POST';
    }

    /**
     * Main control method that processes the student folder creation.
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';

        // Extract and sanitize POST data
        $data = [
            'NumEtu' => (string) ($_POST['numetu'] ?? ''),
            'Nom' => (string) ($_POST['nom'] ?? ''),
            'Prenom' => (string) ($_POST['prenom'] ?? ''),
            'EmailPersonnel' => (string) ($_POST['email_perso'] ?? ''),
            'Telephone' => (string) ($_POST['telephone'] ?? ''),
            'Type' => (string) ($_POST['type'] ?? ''),
        ];

        // Basic input validation
        $errors = [];
        if ($data['NumEtu'] === '') {
            $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        }
        if ($data['Nom'] === '') {
            $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        }
        if ($data['Prenom'] === '') {
            $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        }
        if ($data['EmailPersonnel'] === '') {
            $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        }
        if ($data['Telephone'] === '') {
            $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        $useCase = new ManageFolderUseCase();
        
        // Prevent duplicate student IDs
        $existing = $useCase->getByNumetu($data['NumEtu']);

        if ($existing !== null) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà'
                : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Process uploaded files securely
        $photoData = null;
        if (isset($_FILES['photo']) && is_array($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = (string) $_FILES['photo']['tmp_name'];
            $photoData = file_get_contents($tmpPath) ?: null;
        }

        $cvData = null;
        if (isset($_FILES['cv']) && is_array($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = (string) $_FILES['cv']['tmp_name'];
            $cvData = file_get_contents($tmpPath) ?: null;
        }

        // Execute creation logic
        $success = $useCase->creerDossier($data, $photoData, $cvData);

        if ($success) {
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
}
<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers;

use Model\UseCase\ManageFolderUseCase;

/**
 * Class SaveStudentController
 * Handles the administrative creation of a new student folder via POST requests.
 */
class SaveStudentController
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'save_student' && $method === 'POST';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';

        $data = [
            'NumEtu'         => (string)($_POST['numetu']     ?? ''),
            'Nom'            => (string)($_POST['nom']        ?? ''),
            'Prenom'         => (string)($_POST['prenom']     ?? ''),
            'EmailPersonnel' => (string)($_POST['email_perso'] ?? ''),
            'Telephone'      => (string)($_POST['telephone']  ?? ''),
            'Type'           => (string)($_POST['type']       ?? ''),
        ];

        $errors = [];
        if ($data['NumEtu']         === '') $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        if ($data['Nom']            === '') $errors[] = $lang === 'fr' ? 'Le nom est requis'             : 'Last name is required';
        if ($data['Prenom']         === '') $errors[] = $lang === 'fr' ? 'Le prénom est requis'          : 'First name is required';
        if ($data['EmailPersonnel'] === '') $errors[] = $lang === 'fr' ? "L'email est requis"            : 'Email is required';
        if ($data['Telephone']      === '') $errors[] = $lang === 'fr' ? 'Le téléphone est requis'       : 'Phone is required';

        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        $useCase  = new ManageFolderUseCase();
        $existing = $useCase->getByNumetu($data['NumEtu']);

        if ($existing !== null) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà'
                : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Merge uploaded file contents directly into $data so creerDossier()
        // can pick them up — this avoids passing extra parameters that the
        // method signature does not accept (FIX line 100: invoked with 3 params, 1 required).
        if (isset($_FILES['photo']) && is_array($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents((string)$_FILES['photo']['tmp_name']);
            if ($content !== false) $data['photo'] = $content;
        }

        if (isset($_FILES['cv']) && is_array($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents((string)$_FILES['cv']['tmp_name']);
            if ($content !== false) $data['cv'] = $content;
        }

        // FIX: creerDossier() accepts exactly 1 parameter (array $data).
        // File data is now part of $data, so no extra arguments are needed.
        $success = $useCase->creerDossier($data);

        $_SESSION['message'] = $success
            ? ($lang === 'fr' ? 'Dossier créé avec succès'           : 'Folder created successfully')
            : ($lang === 'fr' ? 'Erreur lors de la création du dossier' : 'Error creating folder');

        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }
}
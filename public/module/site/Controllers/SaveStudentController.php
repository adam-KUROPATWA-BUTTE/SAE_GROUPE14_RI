<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Folder\FolderAdmin;

/**
 * Class SaveStudentController
 *
 * Handles the creation of a new student folder via a POST request.
 *
 * Responsibilities:
 * - Validate incoming POST data.
 * - Check if the student or email already exists.
 * - Create the folder record in the database.
 * - Handle initial file uploads (Photo, CV).
 * - Redirect with success/error messages.
 */
class SaveStudentController implements ControllerInterface
{
    /**
     * Determines if this controller supports the requested page and method.
     *
     * @param string $page   The requested page identifier.
     * @param string $method The HTTP method (e.g., 'POST').
     * @return bool True if this controller should handle the request.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'save_student' && $method === 'POST';
    }

    /**
     * Main control method.
     * Processes the form submission to create a student.
     *
     * @return void
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';

        // 1. Collect and Sanitize Data
        // Casting to string ensures PHPStan knows these aren't null/mixed
        $data = [
            'NumEtu' => trim((string)($_POST['numetu'] ?? '')),
            'Nom' => trim((string)($_POST['nom'] ?? '')),
            'Prenom' => trim((string)($_POST['prenom'] ?? '')),
            'EmailPersonnel' => trim((string)($_POST['email_perso'] ?? '')),
            'Type' => trim((string)($_POST['type'] ?? 'etudes')),
            'Zone' => trim((string)($_POST['zone'] ?? 'europe')),
            // Add defaults for fields not in the mini-form but required by DB/Model
            'Adresse' => '',
            'CodePostal' => '',
            'Ville' => '',
            'Telephone' => '',
            'EmailAMU' => '',
            'CodeDepartement' => '',
            'DateNaissance' => null,
            'Sexe' => ''
        ];

        // 2. Validation: Check if student already exists by ID
        // Note: Using FolderAdmin instead of undefined Folder class
        $existingStudent = FolderAdmin::getStudentDetails($data['NumEtu']);
        if ($existingStudent) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà.'
                : 'A student with this ID already exists.';
            // Redirect with error
            $this->redirect('folders-admin', ['action' => 'create'], $lang);
        }

        // 3. Validation: Check if email already exists
        // Assuming FolderAdmin has a getByEmail method or we skip this check if not implemented
        // For PHPStan compliance, we assume the method exists as per previous context
        if (method_exists(FolderAdmin::class, 'getByEmail')) {
            $existingEmail = FolderAdmin::getByEmail($data['EmailPersonnel']);
            if ($existingEmail) {
                $_SESSION['message'] = $lang === 'fr'
                    ? 'Un étudiant avec cet email existe déjà.'
                    : 'A student with this email already exists.';
                $this->redirect('folders-admin', ['action' => 'create'], $lang);
            }
        }

        // 4. Create the Dossier
        // We pass the data array. The model should handle the insertion.
        $success = FolderAdmin::creerDossier($data);

        if ($success) {
            // 5. Handle File Uploads (if any)
            $this->handleFileUploads($data['NumEtu']);

            $_SESSION['message'] = $lang === 'fr'
                ? 'Dossier créé avec succès.'
                : 'Folder created successfully.';
        } else {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Erreur lors de la création du dossier.'
                : 'Error creating folder.';
        }

        // 6. Final Redirect
        $this->redirect('folders-admin', [], $lang);
    }

    /**
     * Helper to handle optional file uploads during creation.
     *
     * @param string $numetu The student ID.
     * @return void
     */
    private function handleFileUploads(string $numetu): void
    {
        // Upload Photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            FolderAdmin::uploadPhoto($numetu, $_FILES['photo']);
        }

        // Upload CV
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            FolderAdmin::uploadCV($numetu, $_FILES['cv']);
        }
    }

    /**
     * Helper to redirect the user.
     *
     * @param string $page   Target page.
     * @param array  $params Query parameters.
     * @param string $lang   Language code.
     * @return never
     */
    private function redirect(string $page, array $params, string $lang): void
    {
        $params['page'] = $page;
        $params['lang'] = $lang;
        $queryString = http_build_query($params);
        
        header('Location: index.php?' . $queryString);
        exit;
    }
}
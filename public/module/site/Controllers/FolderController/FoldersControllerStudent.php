<?php
namespace Controllers\site\FolderController;

use Controllers\Auth_Guard;
use View\Folder\FoldersPageStudent;
use Controllers\ControllerInterface;
use Model\Folder\FolderStudent;

class FoldersControllerStudent implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'folders-student' || $page === 'update_student';
    }

    public function control(): void
    {
        // --- Démarrage de session ---
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // --- Vérifie que l'étudiant est connecté ---
        if (empty($_SESSION['numetu'])) {
            header('Location: index.php?page=login&error=not_logged_in');
            exit;
        }

        $numetu = $_SESSION['numetu'];
        $lang = $_GET['lang'] ?? 'fr';

        // --- Cas : mise à jour du dossier ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['page'] ?? '') === 'update_student') {
            $this->updateStudent($numetu, $lang);
            return;
        }

        // --- Récupère les informations du dossier étudiant ---
        $studentData = FolderStudent::getStudentDetails($numetu) ?: null;

        // --- Message flash depuis la session ---
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // --- Affichage de la vue ---
        $view = new FoldersPageStudent($studentData, $numetu, 'view', $message, $lang);
        $view->render();
    }

    private function updateStudent(string $numetu, string $lang): void
{
    // --- Préparer les données mises à jour (certains champs ne sont pas modifiables) ---
    $data = [
        'NumEtu'         => $numetu, // verrouillé
        'Adresse'        => $_POST['adresse'] ?? null,
        'CodePostal'     => $_POST['cp'] ?? null,
        'Ville'          => $_POST['ville'] ?? null,
        'Telephone'      => $_POST['telephone'] ?? null,
        'EmailPersonnel' => $_POST['email_perso'] ?? null,
    ];

    // --- Validation minimale ---
    $errors = [];
    if (empty($data['EmailPersonnel'])) {
        $errors[] = $lang === 'fr' ? "L'email personnel est requis." : "Personal email is required.";
    }
    if (empty($data['Telephone'])) {
        $errors[] = $lang === 'fr' ? "Le téléphone est requis." : "Phone number is required.";
    }

    if (!empty($errors)) {
        $_SESSION['message'] = implode(' ', $errors);
        header('Location: index.php?page=folders-student&lang=' . $lang);
        exit;
    }

    // --- Gestion des fichiers uploadés ---
    $photoData = isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK
        ? file_get_contents($_FILES['photo']['tmp_name'])
        : null;

    $cvData = isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK
        ? file_get_contents($_FILES['cv']['tmp_name'])
        : null;

    // --- Mise à jour du dossier ---
    $success = FolderStudent::updateDossier($data, $photoData, $cvData);

    // --- Message flash et redirection vers la page étudiant ---
    $_SESSION['message'] = $success
        ? ($lang === 'fr' ? 'Dossier mis à jour avec succès.' : 'Folder updated successfully.')
        : ($lang === 'fr' ? 'Erreur lors de la mise à jour du dossier.' : 'Error updating folder.');

    header('Location: index.php?page=folders-student&lang=' . $lang);
    exit;
}

}

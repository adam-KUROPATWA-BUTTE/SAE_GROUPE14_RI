<?php
namespace Controllers\site\FolderController;

use Controllers\Auth_Guard;
use View\Folder\FoldersPageStudent;
use Controllers\ControllerInterface;
use Model\FolderStudent;

class FoldersControllerStudent implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'folders-student';
    }

    public function control(): void
    {
        // Vérifier que c'est un étudiant
        Auth_Guard::requireStudent();

        // Récupérer l'ID de l'étudiant connecté
        $studentId = $_SESSION['etudiant_id'] ?? null;
        
        if (!$studentId) {
            header('Location: /login');
            exit();
        }

        // Récupérer l'action
        $action = $_GET['action'] ?? 'view';

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $lang = $_GET['lang'] ?? 'fr';

        // Récupérer le dossier de l'étudiant
        $dossier = FolderStudent::getDossierByEtudiantId($studentId);

        // Charger la vue
        require_once ROOT_PATH . '/public/module/site/View/Folder/FoldersPageStudent.php';
        $view = new \View\FoldersPageStudent($dossier, $studentId, $action, $message, $lang);
        $view->render();
    }
}
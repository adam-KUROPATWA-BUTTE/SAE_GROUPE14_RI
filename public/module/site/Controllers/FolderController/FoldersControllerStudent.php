<?php
namespace Controllers\site\FolderController;

use Controllers\ControllerInterface;
use Controllers\Auth_Guard;
use Model\Folder\FolderStudent;
use View\Folder\FoldersPageStudent;

class FoldersControllerStudent implements ControllerInterface
{
    public function control(): void
    {
        // Vérifier que c'est un étudiant
        Auth_Guard::requireStudent();

        // Récupérer l'ID de l'étudiant connecté
        $studentId = $_SESSION['user_id'] ?? null;
        
        if (!$studentId) {
            header('Location: ?page=login-student');
            exit;
        }

        // Récupérer l'action
        $action = $_GET['action'] ?? 'view';

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $lang = $_GET['lang'] ?? 'fr';

        // L'étudiant ne voit que SON dossier
        $view = new FoldersPageStudent($studentId, $action, $message, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['folders', 'folders-student']) && in_array($method, ['GET', 'POST']);
    }
}
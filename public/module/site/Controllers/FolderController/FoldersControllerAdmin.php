<?php
namespace Controllers\site\FolderController;

use Controllers\ControllerInterface;
use Controllers\Auth_Guard;
use Model\Folder\FolderAdmin;
use View\Folder\FoldersPageAdmin;

class FoldersController
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'folders';
    }

    public function control(): void
    {
        // Récupérer l'action (si l'utilisateur clique sur "créer un dossier")
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

        $view = new FoldersPage($action, $filters, $page, $perPage, $message, $lang);
        $view->render();
    }
}
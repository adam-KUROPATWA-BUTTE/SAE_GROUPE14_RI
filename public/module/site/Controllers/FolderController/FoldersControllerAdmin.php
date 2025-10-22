<?php
namespace Controllers\site\FolderController;

use Controllers\ControllerInterface;
use Controllers\Auth_Guard;
use Model\Folder\FolderAdmin;
use View\Folder\FoldersPageAdmin;

class FoldersControllerAdmin implements ControllerInterface
{
    public function control(): void
    {
        // Vérifier que c'est un admin
        Auth_Guard::requireAdmin();

        // Récupérer l'action
        $action = $_GET['action'] ?? 'list';

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

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $lang = $_GET['lang'] ?? 'fr';

        $view = new FoldersPageAdmin($action, $filters, $page, $perPage, $message, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'folders-admin' && in_array($method, ['GET', 'POST']);
    }
}
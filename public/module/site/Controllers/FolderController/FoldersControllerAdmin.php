<?php
namespace Controllers\site;

use Model\Folder;
use View\FoldersPage;

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

        // Récupérer les filtres depuis l'URL
        $filters = [
            'type' => $_GET['type'] ?? 'all', // all, entrant, sortant
            'zone' => $_GET['zone'] ?? 'all', // all, europe, hors_europe
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

        $view = new FoldersPage($action, $filters, $page, $perPage, $message, $lang);
        $view->render();
    }
}
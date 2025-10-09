<?php
namespace Controllers\Blog;

use Model\Dossier;
use View\FoldersPage;

class FoldersController
{
    public static function support(string $page, string $method): bool {
        return $page === 'folders';
    }

    public function control(): void {
        // On suppose que session_start() a déjà été appelé dans index.php
        $dossiers = Dossier::getAll();
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $view = new FoldersPage($dossiers, $message);
        $view->render();
    }
}

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

        $voeux = []; 
        
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $lang = $_GET['lang'] ?? 'fr';

        $view = new FoldersPage($voeux, $message, $lang); 
        $view->render();
    }
}

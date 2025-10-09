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

        // Le constructeur de FoldersPage attend un tableau de vœux.
        $view = new FoldersPage($voeux, $message); 
        $view->render();
    }
}
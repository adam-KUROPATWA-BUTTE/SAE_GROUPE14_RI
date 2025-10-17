<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;
use View\HomePage;

class IndexController implements ControllerInterface
{
    public function control(): void
    {
        $isLoggedIn = isset($_SESSION['admin_id']);
        $lang = $_GET['lang'] ?? 'fr';  // récupère la langue ou défaut 'fr'

        // Passe la langue au constructeur de la vue
        $view = new HomePage($isLoggedIn, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'home' && $method === 'GET';
    }
}

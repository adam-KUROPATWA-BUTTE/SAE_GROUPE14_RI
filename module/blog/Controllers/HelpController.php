<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;

class HelpController implements ControllerInterface
{
    public function control()
    {
        // Récupérer un éventuel message de session
        $message = $_SESSION['message'] ?? '';
        if ($message) unset($_SESSION['message']);

        // Charger la vue correctement
        require ROOT_PATH . '/module/blog/View/help.php';
    }

    public static function support(string $page, string $method): bool
    {
        // Retourne true si l'URL correspond à /help
        return $page === '/help' || $page === 'help';
    }
}

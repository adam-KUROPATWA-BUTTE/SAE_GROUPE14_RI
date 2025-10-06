<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;

class IndexController implements ControllerInterface
{
    // Méthode principale appelée par le routeur
    public function control()
    {
        require __DIR__ . '/../View/index.php';
    }

    // Méthode obligatoire pour le routeur
    public static function support(string $page, string $method): bool
{
    return $page === 'home';
}


}

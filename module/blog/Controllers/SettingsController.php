<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;
use Model\Universite; // <-- important !

class SettingsController implements ControllerInterface
{
    public function control()
    {
        $universites = Universite::getAll();
        require ROOT_PATH . '/module/blog/View/settings.php';
    }

    public static function support(string $page, string $method): bool
{
    return $page === 'settings';
}

}

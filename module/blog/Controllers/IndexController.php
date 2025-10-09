<?php
namespace Controllers\Blog;

use Controllers\ControllerInterface;
use View\HomePage;

class IndexController implements ControllerInterface
{
    public function control(): void
    {
        $isLoggedIn = isset($_SESSION['admin_id']);
        $view = new HomePage($isLoggedIn);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'home' && $method === 'GET';
    }
}

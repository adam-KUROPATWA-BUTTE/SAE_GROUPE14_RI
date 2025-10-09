<?php
namespace Controllers;

use Controllers\ControllerInterface;
use View\HomePage;

class IndexController implements ControllerInterface
{
    public function control(): void
    {
        $isLoggedIn = isset($_SESSION['admin_id']);

        $percentage = $this->getCompletePercentage();

        $view = new HomePage(isLoggedIn: $isLoggedIn, completePercentage: $percentage);
        echo $view->render();
    }

    private function getCompletePercentage(): int
    {
        // TODO: Récupérer depuis la base de données
        return 0;
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'home' && $method === 'GET';
    }
}
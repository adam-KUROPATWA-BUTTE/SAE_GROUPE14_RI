<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\NotFoundPage;

class NotFoundController implements ControllerInterface
{
    public function control(): void
    {
        $view = new NotFoundPage('Page non trouvée');
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        // Ce contrôleur ne gère aucune route spécifique
        return false;
    }
}

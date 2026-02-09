<?php


namespace Controllers\site\HomeController;

use Controllers\ControllerInterface;
use PDOException;
use Site\UseCase\GetAdminStatsUseCase;
use Model\Persistence\DossierRepositoryPDO;
use View\HomePage\HomePageAdmin;

class HomeControllerAdmin implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-admin' && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';
        $completionPercentage = 0;

        try {

            $repository = new DossierRepositoryPDO();
            $useCase = new GetAdminStatsUseCase($repository);

            $completionPercentage = $useCase->execute();

        } catch (PDOException $e) {

            error_log("HomeControllerAdmin Error: " . $e->getMessage());
            $completionPercentage = 0;
        }

        $view = new HomePageAdmin(true, $lang, $completionPercentage);
        $view->render();
    }
}

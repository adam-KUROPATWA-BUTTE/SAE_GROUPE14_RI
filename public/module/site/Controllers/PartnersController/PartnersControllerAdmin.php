<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use Site\UseCase\AddPartnerUseCase;
use Model\Persistence\PartnerRepositoryPDO;
use Model\Entity\Partner;
use View\Partners\PartnersPageAdmin;
use PDOException;

class PartnersControllerAdmin implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-admin';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $continent   = trim($_POST['continent'] ?? '');
            $country     = trim($_POST['country'] ?? '');
            $city        = trim($_POST['city'] ?? '');
            $institution = trim($_POST['institution'] ?? '');

            if ($continent && $country && $city && $institution) {
                try {
                    $repository = new PartnerRepositoryPDO();
                    $useCase = new AddPartnerUseCase($repository);

                    $partner = new Partner($continent, $country, $city, $institution);
                    $useCase->execute($partner);

                    header('Location: index.php?page=partners-admin&success=1&lang=' . $lang);
                    exit;
                } catch (PDOException $e) {
                    error_log("Partner insertion error: " . $e->getMessage());
                    $errorMessage = $e->getMessage();
                }
            } else {
                $errorMessage = $lang === 'fr'
                    ? 'Tous les champs sont requis.'
                    : 'All fields are required.';
            }
        }

        $title = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';
        $view  = new PartnersPageAdmin($title, $lang);

        if ($errorMessage) {
            $view->errorMessage = $errorMessage;
        }

        $view->render();
    }
}

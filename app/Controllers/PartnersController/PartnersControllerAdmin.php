<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use Model\UseCase\AddPartnerUseCase;
use Model\Persistence\PartnerRepositoryPDO;
use Model\Entity\Partner;
use Core\View;
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

        if (isset($_GET['lang'])) {
            $langParam = strval($_GET['lang']);
            if (in_array($langParam, ['fr', 'en'], true)) {
                $_SESSION['lang'] = $langParam;
            }
        }
        $lang = $_SESSION['lang'] ?? 'fr';

        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = (strval($_GET['tritanopia']) === '1');
        }

        $errorMessage = '';
        $success = isset($_GET['success']);

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

        $titre = $lang === 'en' ? 'Destinaions Universities' : 'Universités Destinations';

        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        View::render('Partners/partners_admin', [
            'titre'        => $titre,
            'lang'         => $lang,
            'errorMessage' => $errorMessage,
            'success'      => $success,
            't'            => $t,
            'buildUrl'     => $buildUrl
        ]);
    }
}
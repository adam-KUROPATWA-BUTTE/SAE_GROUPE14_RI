<?php

namespace Controllers\HomeController;

use Controllers\ControllerInterface;
use PDOException;
use Model\UseCase\GetAdminStatsUseCase;
use Model\Persistence\DossierRepositoryPDO;
use Core\View;

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

        // Vérification rôle
        $allowedRoles = ['admin'];
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
            header('Location: index.php?page=login');
            exit;
        }

        // LANGUE
        if (isset($_GET['lang'])) {
            $langParam = strval($_GET['lang']);
            if (in_array($langParam, ['fr', 'en'], true)) {
                $_SESSION['lang'] = $langParam;
            }
        }
        $lang = $_SESSION['lang'] ?? 'fr';

        // TRITANOPIA
        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = (strval($_GET['tritanopia']) === '1');
        }

        // FILTRES
        $mobiliteFilter = null;
        if (isset($_GET['mobilite']) && in_array($_GET['mobilite'], ['etude', 'stage'], true)) {
            $mobiliteFilter = $_GET['mobilite'];
        }

        $departementFilter = null;
        if (isset($_GET['departement']) && !empty($_GET['departement'])) {
            $departementFilter = strval($_GET['departement']);
        }

        // RÉCUPÉRER LA LISTE DES DÉPARTEMENTS
        $allDepartements = [];
        try {
            $repository = new DossierRepositoryPDO();
            $allDepartements = $repository->getAllDepartements(); // Méthode à créer
        } catch (PDOException $e) {
            error_log("Error fetching departments: " . $e->getMessage());
        }

        // STATISTIQUES
        $stats = null;
        $completionPercentage = 0.0;

        try {
            $repository = new DossierRepositoryPDO();
            $useCase = new GetAdminStatsUseCase($repository);
            $stats = $useCase->execute($mobiliteFilter, $departementFilter); // Ajouter le filtre dept

            $dossierStats = $stats->getDossierStats();
            $completionPercentage = $dossierStats->getTotal() > 0
                ? round($dossierStats->getCompleted() / $dossierStats->getTotal() * 100, 1)
                : 0.0;
        } catch (PDOException $e) {
            error_log("HomeControllerAdmin Error: " . $e->getMessage());
        }

        // HELPERS VUE
        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = str_contains($path, '?') ? '&' : '?';
            return $path . $separator . http_build_query($params);
        };

        // RENDU
        View::render('HomePage/home_admin', [
            'isLoggedIn' => true,
            'userRole' => $_SESSION['role'] ?? null,
            'lang' => $lang,
            'completionPercentage' => $completionPercentage,
            'stats' => $stats,
            'mobiliteFilter' => $mobiliteFilter,
            'departementFilter' => $departementFilter,
            'allDepartements' => $allDepartements,
            't' => $t,
            'buildUrl' => $buildUrl,
        ]);
    }
}
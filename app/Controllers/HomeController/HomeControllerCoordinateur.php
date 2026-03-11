<?php

namespace Controllers\HomeController;

use Controllers\ControllerInterface;
use PDOException;
use Model\UseCase\GetAdminStatsUseCase;
use Core\View;

class HomeControllerCoordinateur implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-coordinateur';
    }

    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function renderView(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function log(string $message): void
    {
        error_log($message);
    }

    protected function makeUseCase(): GetAdminStatsUseCase
    {
        $repo = new \Model\Persistence\DossierRepositoryPDO();
        return new GetAdminStatsUseCase($repo);
    }

    protected function fetchDepartements(): array
    {
        return (new \Model\Persistence\DossierRepositoryPDO())->getAllDepartements();
    }

    public function control(): void
    {
        $this->startSession();

        $allowedRoles = ['coordinateur', 'coordinateur_etude', 'coordinateur_stage', 'chef_departement'];
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
            $this->redirect('index.php?page=login');
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $lang = $_SESSION['lang'] ?? 'fr';

        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = (strval($_GET['tritanopia']) === '1');
        }

        $mobiliteFilter = null;
        if (isset($_GET['mobilite']) && in_array($_GET['mobilite'], ['etude', 'stage'], true)) {
            $mobiliteFilter = $_GET['mobilite'];
        }

        $departementFilter = null;
        if (isset($_GET['departement']) && !empty($_GET['departement'])) {
            $departementFilter = strval($_GET['departement']);
        }

        $allDepartements = [];
        try {
            $allDepartements = $this->fetchDepartements();
        } catch (PDOException $e) {
            $this->log('HomeControllerCoordinateur - getAllDepartements Error: ' . $e->getMessage());
        }

        $stats                = null;
        $completionPercentage = 0.0;

        try {
            $useCase = $this->makeUseCase();
            $stats   = $useCase->execute($mobiliteFilter, $departementFilter);

            $dossierStats         = $stats->getDossierStats();
            $completionPercentage = $dossierStats->getTotal() > 0
                ? round($dossierStats->getCompleted() / $dossierStats->getTotal() * 100, 1)
                : 0.0;
        } catch (PDOException $e) {
            $this->log('HomeControllerCoordinateur Error: ' . $e->getMessage());
        }

        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator      = str_contains($path, '?') ? '&' : '?';
            return $path . $separator . http_build_query($params);
        };

        $this->renderView('HomePage/home_coordinateur', [
            'isLoggedIn'           => true,
            'lang'                 => $lang,
            'userRole'             => $_SESSION['role'],
            'completionPercentage' => $completionPercentage,
            'stats'                => $stats,
            'mobiliteFilter'       => $mobiliteFilter,
            'departementFilter'    => $departementFilter,
            'allDepartements'      => $allDepartements,
            't'                    => $t,
            'buildUrl'             => $buildUrl,
        ]);
    }
}
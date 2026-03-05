<?php

namespace Controllers\HomeController;

use Controllers\ControllerInterface;
use PDOException;
use Model\UseCase\GetAdminStatsUseCase;
use Model\Persistence\DossierRepositoryPDO;
use Core\View;

class HomeControllerCoordinateur implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-coordinateur';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérification rôle
        $allowedRoles = ['coordinateur', 'coordinateur_etude', 'coordinateur_stage', 'chef_departement'];
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
            header('Location: index.php?page=login');
            exit;
        }

        // --- LANGUE ---
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $lang = $_SESSION['lang'] ?? 'fr';

        // --- TRITANOPIA ---
        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = (strval($_GET['tritanopia']) === '1');
        }

        // --- FILTRE MOBILITE ---
        $mobiliteFilter = null;
        if (isset($_GET['mobilite']) && in_array($_GET['mobilite'], ['etude', 'stage'], true)) {
            $mobiliteFilter = $_GET['mobilite'];
        }

        // --- STATISTIQUES (même use case que l'admin) ---
        $stats                = null;
        $completionPercentage = 0.0;

        try {
            $repository = new DossierRepositoryPDO();
            $useCase    = new GetAdminStatsUseCase($repository);
            $stats      = $useCase->execute($mobiliteFilter);

            $dossierStats         = $stats->getDossierStats();
            $completionPercentage = $dossierStats->getTotal() > 0
                ? round($dossierStats->getCompleted() / $dossierStats->getTotal() * 100, 1)
                : 0.0;
        } catch (PDOException $e) {
            error_log('HomeControllerCoordinateur Error: ' . $e->getMessage());
        }

        // --- HELPERS VUE ---
        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator      = str_contains($path, '?') ? '&' : '?';
            return $path . $separator . http_build_query($params);
        };

        // --- RENDU ---
        View::render('HomePage/home_coordinateur', [
            'isLoggedIn'           => true,
            'lang'                 => $lang,
            'userRole'             => $_SESSION['role'],
            'completionPercentage' => $completionPercentage,
            'stats'                => $stats,
            'mobiliteFilter'       => $mobiliteFilter,
            't'                    => $t,
            'buildUrl'             => $buildUrl,
        ]);
    }
}
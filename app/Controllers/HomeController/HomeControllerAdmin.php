<?php

namespace Controllers\site\HomeController;

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

        // --- GESTION DE LA LANGUE ---
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

        // Valeurs par défaut
        $statistics = [
            'complete_folders' => 0,
            'incomplete_folders' => 0,
            'total_folders' => 0,
            'top_countries' => [],
            'gender' => ['male' => 0, 'female' => 0],
            'departments' => []
        ];
        $completionPercentage = 0.0;

        try {
            $repository = new DossierRepositoryPDO();
            $useCase = new GetAdminStatsUseCase($repository);

            $adminStats = $useCase->execute();
            $statistics = $adminStats->toArray();
            $completionPercentage = $adminStats->getDossierStats()->getCompletionPercentage();
        } catch (PDOException $e) {
            error_log("HomeControllerAdmin Error: " . $e->getMessage());
        }

        // --- HELPERS VUE ---
        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        // --- RENDU ---
        View::render('HomePage/home_admin', [
            'isLoggedIn' => true,
            'lang' => $lang,
            'completionPercentage' => $completionPercentage,
            'statistics' => $statistics,
            't' => $t,
            'buildUrl' => $buildUrl
        ]);
    }
}
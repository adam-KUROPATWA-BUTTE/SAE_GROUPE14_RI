<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\WebPlanController;

use Controllers\ControllerInterface;
use Core\View; // Use the view engine
use Model\WebPlan;

/**
 * Class WebPlanControllerAdmin
 *
 * Controller responsible for displaying the website plan (sitemap)
 * for administrators.
 */
class WebPlanControllerAdmin implements ControllerInterface
{
    /**
     * Checks whether this controller supports the given page and HTTP method.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if this controller supports the page, false otherwise
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'web_plan-admin';
    }

    /**
     * Main controller logic.
     *
     * - Retrieves the current language
     * - Fetches admin-specific sitemap links
     * - Creates and renders the admin sitemap view
     *
     * @return void
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // --- SESSION MANAGEMENT ---
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

        // Get sitemap links available for administrators
        /** @var array<int, array{url: string, label: string}> $links */
        $links = WebPlan::getLinksAdmin();

        // --- HELPER FUNCTIONS FOR THE VIEW ---
        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $url) use ($lang): string {
            $sep = (strpos($url, '?') === false) ? '?' : '&';
            return $url . $sep . 'lang=' . urlencode($lang);
        };

        $translateLabel = function (string $label): string {
            $map = [
                'Accueil' => 'Home',
                'Tableau de bord' => 'Dashboard',
                'Partenaires' => 'Partners',
                'Dossiers' => 'Folders',
                'Connexion / Inscription' => 'Login / Register',
            ];
            return $map[$label] ?? $label;
        };

        // --- RENDER VIEW ---
        View::render('WebPlan/web_plan_admin', [
            'links'          => $links,
            'lang'           => $lang,
            't'              => $t,
            'buildUrl'       => $buildUrl,
            'translateLabel' => $translateLabel
        ]);
    }
}
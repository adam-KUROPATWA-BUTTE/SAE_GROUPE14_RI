<?php

namespace Controllers\HomeController;

use Controllers\ControllerInterface;
use Core\View;

class HomeControllerStudent implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'home-student' && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // --- GESTION DE LA SESSION ---
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

        $isStudentLoggedIn = isset($_SESSION['numetu']);

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
        View::render('HomePage/home_student', [
            'isLoggedIn' => $isStudentLoggedIn,
            'lang'       => $lang,
            't'          => $t,
            'buildUrl'   => $buildUrl
        ]);
    }
}
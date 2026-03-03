<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use Core\View;

class PartnersControllerStudent implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-student';
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

        $partner = isset($_GET['partner']) && $_GET['partner'] === 'iut' ? 'iut' : 'amu';


        $titre = match(true) {
            $partner === 'amu' && $lang === 'fr' => 'Universités Partenaires AMU',
            $partner === 'amu' && $lang === 'en' => 'AMU Partner Universities',
            $partner === 'iut' && $lang === 'fr' => 'Universités Partenaires IUT',
            $partner === 'iut' && $lang === 'en' => 'IUT Partner Universities',
        };

        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        View::render('Partners/partners_student', [
            'titre'    => $titre,
            'lang'     => $lang,
            'partner'  => $partner,
            't'        => $t,
            'buildUrl' => $buildUrl
        ]);
    }
}
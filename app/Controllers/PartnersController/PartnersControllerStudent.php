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

    /**
     * @param array<string, mixed> $data
     */
    protected function renderView(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    public function control(): void
    {
        $this->startSession();

        if (empty($_SESSION['numetu'])) {
            $this->redirect('index.php?page=login&error=not_logged_in');
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
            $partner === 'amu' && $lang === 'fr' => 'Universités Destinations AMU',
            $partner === 'amu' && $lang === 'en' => 'AMU Destinations Universities',
            $partner === 'iut' && $lang === 'fr' => 'Universités Destinations IUT',
            $partner === 'iut' && $lang === 'en' => 'IUT Destinations Universities',
            default                              => 'Universités Destinations AMU',
        };

        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        $this->renderView('Partners/partners_student', [
            'titre'    => $titre,
            'lang'     => $lang,
            'partner'  => $partner,
            't'        => $t,
            'buildUrl' => $buildUrl,
        ]);
    }
}
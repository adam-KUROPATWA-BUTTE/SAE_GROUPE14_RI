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

        $isStudentLoggedIn = isset($_SESSION['numetu']);

        $t = function (array $frEn) use ($lang): string {
            return $lang === 'en' ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        $this->renderView('HomePage/home_student', [
            'isLoggedIn' => $isStudentLoggedIn,
            'lang'       => $lang,
            't'          => $t,
            'buildUrl'   => $buildUrl,
        ]);
    }
}
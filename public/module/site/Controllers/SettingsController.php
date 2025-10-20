<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\SettingsPage;

class SettingsController implements ControllerInterface
{
    public function control(): void
    {
        $lang = $_GET['lang'] ?? 'fr';
        $titre = $lang === 'en' ? 'Universities Partner' : 'Universités Partenaire';

        $view = new SettingsPage($titre, $lang);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'settings';
    }
}

<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\PartnersPage;

/**
 * PartnersController
 * 
 * Handles the partners page of the application.
 * 
 * Responsibilities:
 *  - Display a list of partner universities
 *  - Support multilingual titles based on the 'lang' parameter
 */
class PartnersController implements ControllerInterface
{
    /**
     * Main control method that renders the partners page.
     */
    public function control(): void
    {
        // Get language from query parameter (default to French)
        $lang = $_GET['lang'] ?? 'fr';

        // Set page title based on language
        $titre = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';

        // Render the view
        $view = new PartnersPage($titre, $lang);
        $view->render();
    }

    /**
     * Determines if this controller supports the requested page.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if this controller handles the partners page
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners';
    }
}

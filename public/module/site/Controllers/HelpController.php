<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\HelpPage;

/**
 * HelpController
 * 
 * Handles displaying the help or FAQ page.
 * 
 * Responsibilities:
 *  - Provide a list of frequently asked questions (FAQ)
 *  - Render the help page view
 */
class HelpController implements ControllerInterface
{
    /**
     * Main control method that prepares FAQ data and renders the help page.
     */
    public function control(): void
    {
        // Example FAQ (can be replaced with a real model)
        $faq = [
            [
                'question' => 'How to add a user?', 
                'answer' => 'You can add a user via the Settings menu > Users.'
            ],
            [
                'question' => 'How to reset a password?', 
                'answer' => 'Click on the "Reset Password" button in the user profile.'
            ]
        ];

        $view = new HelpPage($faq);
        $view->render();
    }

    /**
     * Determines if this controller supports the requested page.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if the controller handles the page
     */
    public static function support(string $page, string $method): bool
    {
        return $page === '/help' || $page === 'help';
    }
}

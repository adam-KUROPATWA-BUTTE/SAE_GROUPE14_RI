<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use View\Partners\PartnersPageAdmin;
use Database;

/**
 * Class PartnersControllerAdmin
 *
 * Controller responsible for managing partner universities
 * in the administrator interface.
 *
 * Handles:
 * - Displaying the partners administration page
 * - Processing the partner creation form
 */
class PartnersControllerAdmin implements ControllerInterface
{
    /**
     * Main controller logic.
     *
     * - Starts the session if needed
     * - Processes the POST form submission
     * - Inserts a new partner into the database
     * - Redirects after successful insertion
     * - Displays the administration view
     *
     * @return void
     */
    public function control(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // --- FORM PROCESSING ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Retrieve and sanitize form fields
            $continent   = trim($_POST['name'] ?? '');
            $country     = trim($_POST['country'] ?? '');
            $city        = trim($_POST['city'] ?? '');
            $institution = trim($_POST['institution'] ?? '');

            // Check that all required fields are provided
            if ($continent && $country && $city && $institution) {
                try {
                    // Get PDO connection from the Database singleton
                    $pdo = Database::getInstance()->getConnection();

                    // Prepare and execute the INSERT query
                    $stmt = $pdo->prepare("
                        INSERT INTO Partenaires (continent, pays, ville, universite_institution)
                        VALUES (:continent, :pays, :ville, :universite)
                    ");

                    $stmt->execute([
                        'continent'  => $continent,
                        'pays'       => $country,
                        'ville'      => $city,
                        'universite' => $institution
                    ]);

                    // Redirect to prevent duplicate form submission
                    header('Location: /partners-admin?success=1&lang=' . ($_GET['lang'] ?? 'fr'));
                    exit;

                } catch (\PDOException $e) {
                    // Log the error and forward it to the view
                    error_log("Partner insertion error: " . $e->getMessage());
                    $errorMessage = $e->getMessage();
                }
            }
        }

        // --- Prepare parameters for the view ---
        $lang  = $_GET['lang'] ?? 'fr';
        $title = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';

        // --- Render the view ---
        $view = new PartnersPageAdmin($title, $lang);

        // Pass error message to the view if one exists
        if (isset($errorMessage)) {
            $view->errorMessage = $errorMessage;
        }

        $view->render();
    }

    /**
     * Checks whether this controller supports the given page and method.
     *
     * @param string $page   Requested page
     * @param string $method HTTP method
     * @return bool True if supported, false otherwise
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-admin';
    }
}

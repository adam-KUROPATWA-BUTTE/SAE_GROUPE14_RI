<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use View\Partners\PartnersPageAdmin;
use PDOException;

/**
 * Class PartnersControllerAdmin
 *
 * Controller responsible for managing partner universities in the administrator interface.
 *
 * Responsibilities:
 * - Display the list of existing partners.
 * - Handle the form submission to add new partners.
 * - Render the admin-specific view for partners.
 */
class PartnersControllerAdmin implements ControllerInterface
{
    /**
     * Determines if this controller supports the current request.
     *
     * @param string $page   The page identifier from the URL.
     * @param string $method The HTTP method (GET, POST).
     * @return bool True if the page is 'partners-admin'.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-admin';
    }

    /**
     * Main control logic.
     *
     * Steps:
     * 1. Start session.
     * 2. Process POST request (Add Partner).
     * 3. Prepare view data.
     * 4. Render the view.
     *
     * @return void
     */
    public function control(): void
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize error message
        $errorMessage = null;

        // --- 1. Handle Form Submission (POST) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and cast inputs to string for strict typing
            $continent   = trim((string)($_POST['name'] ?? ''));
            $country     = trim((string)($_POST['country'] ?? ''));
            $city        = trim((string)($_POST['city'] ?? ''));
            $institution = trim((string)($_POST['institution'] ?? ''));

            // Validate that all fields are filled
            if ($continent !== '' && $country !== '' && $city !== '' && $institution !== '') {
                try {
                    // Use global Database class
                    $pdo = \Database::getInstance()->getConnection();

                    $sql = "INSERT INTO partenaires (continent, pays, ville, universite) 
                            VALUES (:continent, :pays, :ville, :universite)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'continent'  => $continent,
                        'pays'       => $country,
                        'ville'      => $city,
                        'universite' => $institution
                    ]);

                    // Redirect to prevent duplicate form submission (Post-Redirect-Get pattern)
                    $lang = (string)($_GET['lang'] ?? 'fr');
                    header('Location: index.php?page=partners-admin&success=1&lang=' . $lang);
                    exit;

                } catch (PDOException $e) {
                    error_log("Partner insertion error: " . $e->getMessage());
                    $errorMessage = $e->getMessage();
                }
            } else {
                $errorMessage = "All fields are required.";
            }
        }

        // --- 2. Prepare View Data ---
        $lang  = (string)($_GET['lang'] ?? 'fr');
        $title = ($lang === 'en') ? 'Partner Universities' : 'Universités Partenaires';

        // --- 3. Render View ---
        $view = new PartnersPageAdmin($title, $lang);

        // Inject error message into view if it exists (assuming public property or setter)
        if ($errorMessage !== null) {
            $view->errorMessage = $errorMessage;
        }

        $view->render();
    }
}
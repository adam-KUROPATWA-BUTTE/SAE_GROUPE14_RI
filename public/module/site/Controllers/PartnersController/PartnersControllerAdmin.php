<?php

// phpcs:disable Generic.Files.LineLength

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use View\Partners\PartnersPageAdmin;
use Database;
use PDO;
use PDOException;

/**
 * Class PartnersControllerAdmin
 *
 * Controller responsible for managing partner universities
 * in the administrator interface.
 */
class PartnersControllerAdmin implements ControllerInterface
{
    /**
     * Main controller logic.
     */
    public function control(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = $_GET['lang'] ?? 'fr';
        $errorMessage = '';

        // --- FORM PROCESSING ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $continent   = trim($_POST['continent'] ?? '');
            $country     = trim($_POST['country'] ?? '');
            $city        = trim($_POST['city'] ?? '');
            $institution = trim($_POST['institution'] ?? '');

            if ($continent && $country && $city && $institution) {
                try {
                    // Get PDO connection from the Database singleton
                    $pdo = Database::getInstance()->getConnection();

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

                    // Redirect on success
                    header('Location: index.php?page=partners-admin&success=1&lang=' . $lang);
                    exit;
                } catch (PDOException $e) {
                    error_log("Partner insertion error: " . $e->getMessage());
                    $errorMessage = $e->getMessage();
                }
            } else {
                $errorMessage = $lang === 'fr'
                    ? 'Tous les champs sont requis.'
                    : 'All fields are required.';
            }
        }

        // --- Render view ---
        $title = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';
        $view  = new PartnersPageAdmin($title, $lang);

        if ($errorMessage) {
            $view->errorMessage = $errorMessage;
        }

        $view->render();
    }

    /**
     * Checks whether this controller supports the given page and method.
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-admin';
    }
}

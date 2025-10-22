<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\HomePage;
use Database; // Ajoutez cet import

class IndexController implements ControllerInterface
{
    public function control(): void
    {
        // Vérifie si la session n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isLoggedIn = isset($_SESSION['admin_id']);
        $lang = $_GET['lang'] ?? 'fr';

        $completionPercentage = 0;

        try {
            // Utiliser la classe Database au lieu de créer une connexion directe
            $pdo = Database::getInstance()->getConnection();

            $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(is_complete) as completed FROM dossiers");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row['total'] > 0) {
                $completionPercentage = ($row['completed'] / $row['total']) * 100;
            }

        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des statistiques : " . $e->getMessage());
            // Ne pas exposer le message d'erreur à l'utilisateur
            $completionPercentage = 0;
        }

        $view = new HomePage($isLoggedIn, $lang, $completionPercentage);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'home' && $method === 'GET';
    }
}
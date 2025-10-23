<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\HomePage;

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

        // Connexion DB
      
        $host  = $_ENV['DB_HOST'];
        $port  = $_ENV['DB_PORT'];;
        $dbname = $_ENV['DB_NAME'];;
        $charset  = $_ENV['DB_CHARSET'];;
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];


        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

        try {
            $pdo = new \PDO($dsn, $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(IsComplete) as completed FROM dossiers");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row['total'] > 0) {
                $completionPercentage = ($row['completed'] / $row['total']) * 100;
            }

        } catch (\PDOException $e) {
            die("Erreur connexion DB : " . $e->getMessage());
        }

        $view = new HomePage($isLoggedIn, $lang, $completionPercentage);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'home' && $method === 'GET';
    }
}

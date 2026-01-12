<?php

namespace Controllers\PartnersController;

use Controllers\ControllerInterface;
use View\Partners\PartnersPageAdmin;
use Database;

class PartnersControllerAdmin implements ControllerInterface
{
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // --- TRAITEMENT DU FORMULAIRE ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des champs
            $continent   = trim($_POST['name'] ?? '');
            $pays        = trim($_POST['country'] ?? '');
            $ville       = trim($_POST['city'] ?? '');
            $universite  = trim($_POST['institution'] ?? ''); // correspond à universite_institution en BDD

            if ($continent && $pays && $ville && $universite) {
                try {
                    // Connexion PDO via ton singleton
                    $pdo = Database::getInstance()->getConnection();

                    // Préparer et exécuter l'insertion
                    $stmt = $pdo->prepare("
                INSERT INTO Partenaires (continent, pays, ville, universite_institution)
                VALUES (:continent, :pays, :ville, :universite)
            ");

                    $stmt->execute([
                        'continent'  => $continent,
                        'pays'       => $pays,
                        'ville'      => $ville,
                        'universite' => $universite
                    ]);

                    // Redirection pour éviter le double submit
                    header('Location: /partners-admin?success=1&lang=' . ($_GET['lang'] ?? 'fr'));
                    exit;

                } catch (\PDOException $e) {
                    // Log de l'erreur et passage à la vue
                    error_log("Erreur ajout partenaire : " . $e->getMessage());
                    $errorMessage = $e->getMessage();
                }
            }
        }


        // --- Préparer les paramètres pour la vue ---
        $lang = $_GET['lang'] ?? 'fr';
        $titre = $lang === 'en' ? 'Partner Universities' : 'Universités Partenaires';

        // --- Affichage de la vue ---
        $view = new PartnersPageAdmin($titre, $lang);

        // Passer l'éventuel message d'erreur à la vue
        if (isset($errorMessage)) {
            $view->errorMessage = $errorMessage;
        }

        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'partners-admin';
    }
}
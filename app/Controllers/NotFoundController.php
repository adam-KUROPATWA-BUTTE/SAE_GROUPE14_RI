<?php

namespace Controllers;

use Controllers\ControllerInterface;
use Core\View;

class NotFoundController implements ControllerInterface
{
    /**
     * Méthode principale qui prépare les données et appelle la vue.
     */
    public function control(): void
    {
        // 1. Logique (session, vérification tritanopie)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isTritanopia = !empty($_SESSION['tritanopia']) && ((bool)$_SESSION['tritanopia'] === true);
        $titre = 'Page non trouvée';

        // 2. Appel de la vue (Template) via Core\View
        // On passe les variables 'titre' et 'isTritanopia' à la vue
        View::render('404', [
            'titre' => $titre,
            'isTritanopia' => $isTritanopia
        ]);
    }

    /**
     * Ce contrôleur est un fallback, il ne supporte aucune route spécifique.
     */
    public static function support(string $page, string $method): bool
    {
        return false;
    }
}
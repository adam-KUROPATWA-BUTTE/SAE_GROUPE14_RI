<?php
namespace Controllers\site;

use Controllers\ControllerInterface;
use View\HelpPage;

class HelpController implements ControllerInterface
{
    public function control(): void
    {
        // Exemple de FAQ (tu peux la remplacer par un vrai modèle)
        $faq = [
            ['question' => 'Comment ajouter un utilisateur ?', 'answer' => 'Vous pouvez ajouter un utilisateur via le menu Paramétrage > Utilisateurs.'],
            ['question' => 'Comment réinitialiser un mot de passe ?', 'answer' => 'Cliquez sur le bouton "Réinitialiser le mot de passe" dans le profil utilisateur.']
        ];

        $view = new HelpPage($faq);
        $view->render();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === '/help' || $page === 'help';
    }
}

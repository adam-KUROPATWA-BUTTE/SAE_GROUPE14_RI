<?php

class Help
{
    public static function getFaq()
    {
        return [
            [
                'question' => "Comment réinitialiser le mot de passe d’un utilisateur ?",
                'answer' => "Depuis la page de connexion, cliquez sur \"Mot de passe oublié\" et suivez la procédure."
            ],
            [
                'question' => "Qui contacter en cas de problème technique ?",
                'answer' => "Contactez le support informatique AMU à <a href=\"mailto:support@amu.fr\">support@amu.fr</a>."
            ]
        ];
    }
}
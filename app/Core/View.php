<?php

namespace Core; 

class View
{
    /**
     * Génère et affiche une vue
     * * @param string $template Le nom du fichier vue (ex: 'login')
     * @param array $data Les variables à envoyer à la vue
     */
    public static function render(string $template, array $data = []): void
    {
        // 1. Transforme les clés du tableau en vraies variables ($message, $isLogin, etc.)
        extract($data);

        // 2. Construit le chemin absolu vers le fichier HTML
        $file = ROOT_PATH . '/app/View/' . $template . '.php';

        // 3. Vérifie que le fichier existe avant de l'inclure
        if (file_exists($file)) {
            require $file;
        } else {
            die("Erreur : Le fichier de vue '$template.php' est introuvable.");
        }
    }
}
<?php
namespace Model;

class Dossier
{
    public static function getAll()
    {
        return self::getDossiersIncomplets(); // renvoie les mêmes données pour l’instant
    }

    public static function getDossiersIncomplets()
    {
        return [
            ['nom' => 'Dupont', 'prenom' => 'Marie', 'email' => 'marie.dupont@email.fr', 'total_pieces' => 5, 'pieces_fournies' => 3, 'date_derniere_relance' => '2025-09-25'],
            ['nom' => 'Nguyen', 'prenom' => 'Linh', 'email' => 'linh.nguyen@email.fr', 'total_pieces' => 4, 'pieces_fournies' => 2, 'date_derniere_relance' => '2025-09-29'],
            ['nom' => 'Bernard', 'prenom' => 'Sophie', 'email' => 'sophie.bernard@email.fr', 'total_pieces' => 6, 'pieces_fournies' => 4, 'date_derniere_relance' => '2025-09-20'],
        ];
    }

    public static function valider($numetu, $adminId) { return true; }
    public static function ajouterRelance($dossierId, $message, $adminId) { return true; }
    public static function ajouterEtudiant($numetu, $nom, $prenom, $email, $telephone) { return true; }
    public static function supprimerDossier($numetu) { return true; }
}

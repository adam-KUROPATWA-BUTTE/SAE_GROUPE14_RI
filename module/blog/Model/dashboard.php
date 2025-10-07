<?php
namespace Model;

class Dossiers
{
    public static function getDossiersIncomplets()
    {
        // Données fictives pour la vitrine
        return [
            [
                'nom' => 'Dupont',
                'prenom' => 'Marie',
                'email' => 'marie.dupont@email.fr',
                'total_pieces' => 5,
                'pieces_fournies' => 3,
                'date_derniere_relance' => '2025-09-25'
            ],
            [
                'nom' => 'Nguyen',
                'prenom' => 'Linh',
                'email' => 'linh.nguyen@email.fr',
                'total_pieces' => 4,
                'pieces_fournies' => 2,
                'date_derniere_relance' => '2025-09-29'
            ],
            [
                'nom' => 'Bernard',
                'prenom' => 'Sophie',
                'email' => 'sophie.bernard@email.fr',
                'total_pieces' => 6,
                'pieces_fournies' => 4,
                'date_derniere_relance' => '2025-09-20'
            ],
        ];
    }
}
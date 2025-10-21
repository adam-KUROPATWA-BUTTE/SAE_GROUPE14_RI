<?php
namespace Model;

class Folder
{
    public static function getAll()
    {
        // TODO: Remplacer par une vraie requête SQL
        // SELECT * FROM etudiants ORDER BY nom, prenom

        return [
            [
                'numetu' => 'E001',
                'nom' => 'Dupont',
                'prenom' => 'Marie',
                'email' => 'marie.dupont@email.fr',
                'telephone' => '0612345678',
                'type' => 'entrant',
                'zone' => 'europe',
                'stage' => 'stage_moins2mois',
                'etude' => 'etude_6mois',
                'photo' => null,
                'cv' => null,
                'total_pieces' => 5,
                'pieces_fournies' => 3,
                'date_derniere_relance' => '2025-09-25'
            ],
            [
                'numetu' => 'E002',
                'nom' => 'Nguyen',
                'prenom' => 'Linh',
                'email' => 'linh.nguyen@email.fr',
                'telephone' => '0623456789',
                'type' => 'sortant',
                'zone' => 'hors_europe',
                'stage' => 'stage_plus2mois',
                'etude' => 'etude_1an',
                'photo' => 'linh.jpg',
                'cv' => 'linh_cv.pdf',
                'total_pieces' => 4,
                'pieces_fournies' => 2,
                'date_derniere_relance' => '2025-09-29'
            ],
            [
                'numetu' => 'E003',
                'nom' => 'Bernard',
                'prenom' => 'Sophie',
                'email' => 'sophie.bernard@email.fr',
                'telephone' => '0634567890',
                'type' => 'entrant',
                'zone' => 'europe',
                'stage' => 'stage_moins2mois',
                'etude' => 'etude_6mois',
                'photo' => 'sophie.jpg',
                'cv' => null,
                'total_pieces' => 6,
                'pieces_fournies' => 4,
                'date_derniere_relance' => '2025-09-20'
            ],
            [
                'numetu' => 'E004',
                'nom' => 'Martin',
                'prenom' => 'Lucas',
                'email' => 'lucas.martin@email.fr',
                'telephone' => '0645678901',
                'type' => 'sortant',
                'zone' => 'europe',
                'stage' => 'stage_plus2mois',
                'etude' => 'etude_1an',
                'photo' => null,
                'cv' => 'lucas_cv.pdf',
                'total_pieces' => 6,
                'pieces_fournies' => 6,
                'date_derniere_relance' => '2025-09-18'
            ],
            [
                'numetu' => 'E005',
                'nom' => 'Kone',
                'prenom' => 'Awa',
                'email' => 'awa.kone@email.fr',
                'telephone' => '0656789012',
                'type' => 'entrant',
                'zone' => 'hors_europe',
                'stage' => 'stage_moins2mois',
                'etude' => 'etude_6mois',
                'photo' => 'awa.jpg',
                'cv' => null,
                'total_pieces' => 5,
                'pieces_fournies' => 2,
                'date_derniere_relance' => '2025-09-22'
            ],
            [
                'numetu' => 'E006',
                'nom' => 'Garcia',
                'prenom' => 'Carlos',
                'email' => 'carlos.garcia@email.fr',
                'telephone' => '0667890123',
                'type' => 'entrant',
                'zone' => 'europe',
                'stage' => 'stage_plus2mois',
                'etude' => 'etude_1an',
                'photo' => null,
                'cv' => 'carlos_cv.pdf',
                'total_pieces' => 7,
                'pieces_fournies' => 5,
                'date_derniere_relance' => '2025-10-01'
            ],
            [
                'numetu' => 'E007',
                'nom' => 'Rossi',
                'prenom' => 'Giulia',
                'email' => 'giulia.rossi@email.fr',
                'telephone' => '0678901234',
                'type' => 'sortant',
                'zone' => 'europe',
                'stage' => 'stage_moins2mois',
                'etude' => 'etude_6mois',
                'photo' => 'giulia.jpg',
                'cv' => 'giulia_cv.pdf',
                'total_pieces' => 5,
                'pieces_fournies' => 3,
                'date_derniere_relance' => '2025-10-03'
            ],
            [
                'numetu' => 'E008',
                'nom' => 'Ali',
                'prenom' => 'Youssef',
                'email' => 'youssef.ali@email.fr',
                'telephone' => '0689012345',
                'type' => 'entrant',
                'zone' => 'hors_europe',
                'stage' => 'stage_plus2mois',
                'etude' => 'etude_1an',
                'photo' => null,
                'cv' => null,
                'total_pieces' => 6,
                'pieces_fournies' => 4,
                'date_derniere_relance' => '2025-10-05'
            ],
            [
                'numetu' => 'E009',
                'nom' => 'Durand',
                'prenom' => 'Clément',
                'email' => 'clement.durand@email.fr',
                'telephone' => '0690123456',
                'type' => 'sortant',
                'zone' => 'europe',
                'stage' => 'stage_moins2mois',
                'etude' => 'etude_6mois',
                'photo' => 'clement.jpg',
                'cv' => 'clement_cv.pdf',
                'total_pieces' => 4,
                'pieces_fournies' => 4,
                'date_derniere_relance' => '2025-10-07'
            ],
            [
                'numetu' => 'E010',
                'nom' => 'Leblanc',
                'prenom' => 'Camille',
                'email' => 'camille.leblanc@email.fr',
                'telephone' => '0611223344',
                'type' => 'entrant',
                'zone' => 'europe',
                'stage' => 'stage_plus2mois',
                'etude' => 'etude_1an',
                'photo' => null,
                'cv' => null,
                'total_pieces' => 6,
                'pieces_fournies' => 2,
                'date_derniere_relance' => '2025-10-10'
            ],
            [
                'numetu' => 'E011',
                'nom' => 'Boukari',
                'prenom' => 'Fatou',
                'email' => 'fatou.boukari@email.fr',
                'telephone' => '0622334455',
                'type' => 'entrant',
                'zone' => 'hors_europe',
                'stage' => 'stage_moins2mois',
                'etude' => 'etude_6mois',
                'photo' => 'fatou.jpg',
                'cv' => null,
                'total_pieces' => 4,
                'pieces_fournies' => 3,
                'date_derniere_relance' => '2025-10-12'
            ],
            [
                'numetu' => 'E012',
                'nom' => 'Zimmermann',
                'prenom' => 'Paul',
                'email' => 'paul.zimmermann@email.fr',
                'telephone' => '0633445566',
                'type' => 'sortant',
                'zone' => 'europe',
                'stage' => 'stage_plus2mois',
                'etude' => 'etude_1an',
                'photo' => null,
                'cv' => 'paul_cv.pdf',
                'total_pieces' => 7,
                'pieces_fournies' => 5,
                'date_derniere_relance' => '2025-10-15'
            ],

        ];
    }

    public static function getDossiersIncomplets()
    {
        $all = self::getAll();
        return array_filter($all, function($etudiant) {
            return $etudiant['pieces_fournies'] < $etudiant['total_pieces'];
        });
    }

    public static function creerDossier($data)
    {
        // TODO: INSERT INTO etudiants ...
        return true;
    }

    public static function valider($numetu, $adminId) { return true; }
    public static function ajouterRelance($dossierId, $message, $adminId) { return true; }
    public static function ajouterEtudiant($numetu, $nom, $prenom, $email, $telephone) { return true; }
    public static function supprimerDossier($numetu) { return true; }

    public static function uploadPhoto($numetu, $file)
    {
        // TODO: Gérer l'upload de photo
        return true;
    }

    public static function uploadCV($numetu, $file)
    {
        // TODO: Gérer l'upload de CV
        return true;
    }
}
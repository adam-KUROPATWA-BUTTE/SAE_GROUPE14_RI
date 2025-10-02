<?php

class Universite
{
    public static function getAll()
    {
        // Données fictives
        return [
            [
                'code' => 'AMU001',
                'universite' => 'Aix-Marseille Université',
                'pays' => 'France',
                'partenaire' => 'Oui'
            ],
            [
                'code' => 'OXF002',
                'universite' => 'University of Oxford',
                'pays' => 'Royaume-Uni',
                'partenaire' => 'Oui'
            ],
            [
                'code' => 'MIT003',
                'universite' => 'MIT',
                'pays' => 'États-Unis',
                'partenaire' => 'Non'
            ],
        ];
    }
}
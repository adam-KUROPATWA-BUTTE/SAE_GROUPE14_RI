<?php
namespace Model;

class Partner
{
    public static function getAll()
    {
        return [
            [
                'Nos partenaires' => '1',
                'cadre' => 'Multidisciplinaire'
            ],
            [
                'Nos partenaires' => '2',
                'cadre' => 'CIVIS'
            ],
        ];
    }
}
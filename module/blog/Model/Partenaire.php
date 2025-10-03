<?php

class Partenaire
{
    public static function getAll()
    {
        return [
            [
                'code' => 'P001',
                'nom' => 'Erasmus+',
                'pays' => 'Europe'
            ],
            [
                'code' => 'P002',
                'nom' => 'Fulbright',
                'pays' => 'États-Unis'
            ],
        ];
    }
}
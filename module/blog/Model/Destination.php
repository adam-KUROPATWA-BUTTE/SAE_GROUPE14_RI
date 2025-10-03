<?php

class Destination
{
    public static function getAll()
    {
        return [
            [
                'code' => 'D001',
                'ville' => 'Paris',
                'pays' => 'France'
            ],
            [
                'code' => 'D002',
                'ville' => 'Tokyo',
                'pays' => 'Japon'
            ],
        ];
    }
}
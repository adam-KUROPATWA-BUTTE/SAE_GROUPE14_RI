<?php

class Campagne{
    public static function getAll(){
        return [
            ['code' => 1, 'date_ouverture' => '22/03/2023', 'date_fermeture' => '22/06/2023'],
            ['code' => 2, 'date_ouverture' => '22/03/2024', 'date_fermeture' => '22/06/2024'],
            ['code' => 3, 'date_ouverture' => '22/03/2025', 'date_fermeture' => '22/06/2025'],
        ];
    }
}
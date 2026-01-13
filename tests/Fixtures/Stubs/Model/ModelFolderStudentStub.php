<?php

namespace Model\Folder;

class FolderStudent
{
    public static ?array $studentDetailsReturn = null;
    public static ?string $getStudentDetailsCalledWith = null;

    public static function getStudentDetails(string $numetu)
    {
        self::$getStudentDetailsCalledWith = $numetu;
        return self::$studentDetailsReturn;
    }

    public static function createDossier($data, $photoData = null, $cvData = null, $conventionData = null, $lettreData = null)
    {
        return true;
    }

    public static function updateDossier($data, $photoData = null, $cvData = null, $conventionData = null, $lettreData = null)
    {
        return true;
    }
}

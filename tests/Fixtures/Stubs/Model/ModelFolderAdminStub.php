<?php

namespace Model\Folder;

class FolderAdmin
{
    public static ?array $studentDetailsReturn = null;
    public static ?string $getStudentDetailsCalledWith = null;

    public static function getStudentDetails(string $numetu)
    {
        self::$getStudentDetailsCalledWith = $numetu;
        return self::$studentDetailsReturn;
    }

    public static function getByNumetu(string $numetu) { return null; }
    public static function creerDossier($data, $photoData = null, $cvData = null, $conventionData = null, $lettreData = null) { return true; }
    public static function updateDossier($data, $photoData = null, $cvData = null, $conventionData = null, $lettreData = null) { return true; }
    public static function toggleCompleteStatus(string $numetu): bool { return true; }
}

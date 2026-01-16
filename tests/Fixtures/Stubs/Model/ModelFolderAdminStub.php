<?php

namespace Model\Folder;

class FolderAdmin
{
    /** @var array<string, mixed>|null */
    public static ?array $studentDetailsReturn = null;
    public static ?string $getStudentDetailsCalledWith = null;

    /**
     * @return array<string, mixed>|null
     */
    public static function getStudentDetails(string $numetu): ?array
    {
        self::$getStudentDetailsCalledWith = $numetu;
        return self::$studentDetailsReturn;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getByNumetu(string $numetu): ?array { return null; }
    
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $photoData
     * @param array<string, mixed>|null $cvData
     * @param array<string, mixed>|null $conventionData
     * @param array<string, mixed>|null $lettreData
     */
    public static function creerDossier(array $data, ?array $photoData = null, ?array $cvData = null, ?array $conventionData = null, ?array $lettreData = null): bool { return true; }
    
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $photoData
     * @param array<string, mixed>|null $cvData
     * @param array<string, mixed>|null $conventionData
     * @param array<string, mixed>|null $lettreData
     */
    public static function updateDossier(array $data, ?array $photoData = null, ?array $cvData = null, ?array $conventionData = null, ?array $lettreData = null): bool { return true; }
    
    public static function toggleCompleteStatus(string $numetu): bool { return true; }
}

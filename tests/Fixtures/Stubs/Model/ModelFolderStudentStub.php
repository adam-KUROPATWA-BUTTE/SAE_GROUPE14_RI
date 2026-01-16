<?php

namespace Model\Folder;

class FolderStudent
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
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $photoData
     * @param array<string, mixed>|null $cvData
     * @param array<string, mixed>|null $conventionData
     * @param array<string, mixed>|null $lettreData
     */
    public static function createDossier(array $data, ?array $photoData = null, ?array $cvData = null, ?array $conventionData = null, ?array $lettreData = null): bool
    {
        return true;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $photoData
     * @param array<string, mixed>|null $cvData
     * @param array<string, mixed>|null $conventionData
     * @param array<string, mixed>|null $lettreData
     */
    public static function updateDossier(array $data, ?array $photoData = null, ?array $cvData = null, ?array $conventionData = null, ?array $lettreData = null): bool
    {
        return true;
    }
}

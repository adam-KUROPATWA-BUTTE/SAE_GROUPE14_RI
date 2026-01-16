<?php

namespace Model\Folder;

class FolderAdmin
{
    public static bool $getDossiersIncompletsCalled = false;

    /**
     * @return array<int, mixed>
     */
    public static function getDossiersIncomplets(): array
    {
        self::$getDossiersIncompletsCalled = true;
        return [];
    }
}

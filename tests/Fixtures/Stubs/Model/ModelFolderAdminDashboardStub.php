<?php

namespace Model\Folder;

class FolderAdmin
{
    public static $getDossiersIncompletsCalled = false;

    public static function getDossiersIncomplets()
    {
        self::$getDossiersIncompletsCalled = true;
        return [];
    }
}

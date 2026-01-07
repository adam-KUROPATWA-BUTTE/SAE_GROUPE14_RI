<?php

namespace View\Folder;

class FoldersPageAdmin
{
    public static array $lastArgs = [];

    public function __construct(string $action, array $filters, int $page, int $perPage, string $message, string $lang, ?array $studentData = null)
    {
        self::$lastArgs = func_get_args();
    }

    public function render(): void
    {
        echo 'RENDER_OK';
    }
}

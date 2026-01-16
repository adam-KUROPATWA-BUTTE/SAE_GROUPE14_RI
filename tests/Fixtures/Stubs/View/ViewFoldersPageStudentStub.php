<?php

namespace View\Folder;

class FoldersPageStudent
{
    /** @var array<int, mixed> */
    public static array $lastArgs = [];

    /**
     * @param array<string, mixed>|null $studentData
     */
    public function __construct(?array $studentData, string $numetu, string $action, string $message, string $lang)
    {
        self::$lastArgs = func_get_args();
    }

    public function render(): void
    {
        echo 'RENDER_OK';
    }
}

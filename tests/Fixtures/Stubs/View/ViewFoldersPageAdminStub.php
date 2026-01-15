<?php

namespace View\Folder;

class FoldersPageAdmin
{
    /** @var array<int, mixed> */
    public static array $lastArgs = [];

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed>|null $studentData
     */
    public function __construct(string $action, array $filters, int $page, int $perPage, string $message, string $lang, ?array $studentData = null)
    {
        self::$lastArgs = func_get_args();
    }

    public function render(): void
    {
        echo 'RENDER_OK';
    }
}

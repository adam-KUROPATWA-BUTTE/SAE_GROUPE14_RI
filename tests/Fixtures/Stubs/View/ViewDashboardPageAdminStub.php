<?php

namespace View\Dashboard;

class DashboardPageAdmin
{
    /** @var array<int, mixed>|null */
    public static ?array $lastArgs = null;
    public static bool $renderCalled = false;

    /**
     * @param array<int, mixed> $dossiers
     */
    public function __construct(array $dossiers, string $lang)
    {
        self::$lastArgs = [$dossiers, $lang];
    }

    public function render(): void
    {
        self::$renderCalled = true;
        echo 'DASHBOARD';
    }
}

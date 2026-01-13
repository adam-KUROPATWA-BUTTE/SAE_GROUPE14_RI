<?php

namespace View\Dashboard;

class DashboardPageAdmin
{
    public static $lastArgs = null;
    public static $renderCalled = false;

    public function __construct($dossiers, $lang)
    {
        self::$lastArgs = [$dossiers, $lang];
    }

    public function render(): void
    {
        self::$renderCalled = true;
        echo 'DASHBOARD';
    }
}

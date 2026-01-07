<?php

namespace View\WebPlan;

class WebPlanPageAdmin
{
    public static array $lastArgs = [];

    public function __construct(array $links, string $lang)
    {
        self::$lastArgs = func_get_args();
    }

    public function render(): void
    {
        echo 'RENDER_OK';
    }
}

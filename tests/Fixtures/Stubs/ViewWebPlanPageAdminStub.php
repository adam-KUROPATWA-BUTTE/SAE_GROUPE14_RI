<?php

namespace View\WebPlan;

class WebPlanPageAdmin
{
    /** @var array<int, mixed> */
    public static array $lastArgs = [];

    /**
     * @param array<int, mixed> $links
     */
    public function __construct(array $links, string $lang)
    {
        self::$lastArgs = func_get_args();
    }

    public function render(): void
    {
        echo 'RENDER_OK';
    }
}

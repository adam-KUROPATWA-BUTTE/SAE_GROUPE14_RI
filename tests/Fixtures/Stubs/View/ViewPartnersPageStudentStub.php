<?php

namespace View\Partners;

class PartnersPageStudent
{
    public static array $lastArgs = [];

    public function __construct(string $titre, string $lang)
    {
        self::$lastArgs = func_get_args();
    }

    public function render(): void
    {
        echo 'RENDER_OK';
    }
}

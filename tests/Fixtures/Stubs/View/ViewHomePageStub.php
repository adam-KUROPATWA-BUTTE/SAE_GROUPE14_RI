<?php

namespace View;

class HomePage
{
    public static $lastArgs = null;

    public function __construct($isLoggedIn, $lang, $completion)
    {
        self::$lastArgs = [$isLoggedIn, $lang, $completion];
    }

    public function render(): void
    {
        echo 'HOME PAGE';
    }
}

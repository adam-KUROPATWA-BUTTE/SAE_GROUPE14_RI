<?php

namespace View;

class HomePage
{
    /** @var array<int, mixed>|null */
    public static ?array $lastArgs = null;

    public function __construct(bool $isLoggedIn, string $lang, int|float $completion)
    {
        self::$lastArgs = [$isLoggedIn, $lang, $completion];
    }

    public function render(): void
    {
        echo 'HOME PAGE';
    }
}

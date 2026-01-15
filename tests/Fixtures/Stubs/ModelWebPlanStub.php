<?php

namespace Model;

class WebPlan
{
    /** @var array<int, mixed>|null */
    public static ?array $linksAdminReturn = null;

    /**
     * @return array<int, mixed>
     */
    public static function getLinksAdmin(): array
    {
        return self::$linksAdminReturn ?? [];
    }
}

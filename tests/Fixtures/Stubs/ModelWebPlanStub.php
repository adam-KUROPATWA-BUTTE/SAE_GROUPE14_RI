<?php

namespace Model;

class WebPlan
{
    public static ?array $linksAdminReturn = null;

    public static function getLinksAdmin(): array
    {
        return self::$linksAdminReturn ?? [];
    }
}

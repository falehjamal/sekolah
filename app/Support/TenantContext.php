<?php

namespace App\Support;

class TenantContext
{
    protected static ?int $tenantId = null;

    public static function set(?int $tenantId): void
    {
        static::$tenantId = $tenantId;
    }

    public static function id(): ?int
    {
        return static::$tenantId;
    }

    public static function forget(): void
    {
        static::$tenantId = null;
    }
}

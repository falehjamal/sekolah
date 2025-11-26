<?php

namespace App\Support;

use App\Services\Tenant\PermissionTableConfigurator;

class TenantContext
{
    protected static ?int $tenantId = null;

    public static function set(?int $tenantId): void
    {
        static::$tenantId = $tenantId;
        app(PermissionTableConfigurator::class)->applyForTenant($tenantId);
    }

    public static function id(): ?int
    {
        return static::$tenantId;
    }

    public static function forget(): void
    {
        static::$tenantId = null;
        app(PermissionTableConfigurator::class)->applyForTenant(null);
    }
}

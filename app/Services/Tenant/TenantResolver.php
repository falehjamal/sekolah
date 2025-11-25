<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TenantResolver
{
    public function resolveActive(string $tenantKey): Tenant
    {
        $normalized = trim($tenantKey);

        $tenant = Tenant::query()
            ->active()
            ->when(is_numeric($normalized), function ($query) use ($normalized) {
                $query->where('id', (int) $normalized);
            }, function ($query) use ($normalized) {
                $query->where('name', $normalized);
            })
            ->first();

        if (! $tenant) {
            throw (new ModelNotFoundException)->setModel(Tenant::class, [$tenantKey]);
        }

        return $tenant;
    }
}

<?php

namespace App\Models\Concerns;

use App\Services\Tenant\TenantConnectionManager;
use App\Support\TenantContext;
use RuntimeException;

trait UsesTenantTableSuffix
{
    public function getTable(): string
    {
        if (isset($this->table)) {
            return $this->table;
        }

        $baseTable = $this->getBaseTable();

        if ($baseTable === null) {
            return parent::getTable();
        }

        return $this->buildTenantTableName($baseTable);
    }

    protected function resolveTenantKey(): string
    {
        $tenantId = TenantContext::id() ?? session('tenant_id');

        if ($tenantId !== null) {
            return (string) $tenantId;
        }

        throw new RuntimeException('Tenant belum ditentukan untuk operasi ini.');
    }

    public function buildTenantTableName(string $base): string
    {
        return sprintf('%s_%s', $base, $this->resolveTenantKey());
    }

    public function resolveTenantTable(string $base): string
    {
        return $this->buildTenantTableName($base);
    }

    protected function newBaseQueryBuilder()
    {
        $connection = $this->resolveTenantConnection();

        return $connection->query();
    }

    protected function resolveTenantConnection()
    {
        $connectionName = $this->getConnectionName();

        if ($connectionName === 'sekolah_tenant' && session()->has('tenant_connection')) {
            app(TenantConnectionManager::class)->connectFromSession();
        }

        return $this->resolveConnection($connectionName);
    }

    protected function getBaseTable(): ?string
    {
        return property_exists($this, 'baseTable') ? $this->baseTable : null;
    }
}

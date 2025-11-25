<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantConnectionManager
{
    public function rememberTenant(Tenant $tenant): void
    {
        session([
            'tenant_id' => $tenant->getKey(),
            'tenant_connection' => [
                'host' => $tenant->db_host,
                'port' => $tenant->port,
                'database' => $tenant->db_name,
                'username' => $tenant->db_user,
                'password' => $tenant->db_pass,
            ],
        ]);

        TenantContext::set($tenant->getKey());
    }

    public function connectFromSession(): void
    {
        $config = session('tenant_connection');

        if (! $config) {
            throw new RuntimeException('Konfigurasi koneksi tenant tidak tersedia di session.');
        }

        $this->applyConnectionConfig($config);
        TenantContext::set((int) session('tenant_id'));
    }

    public function disconnect(): void
    {
        DB::purge('sekolah_tenant');

        session()->forget(['tenant_id', 'tenant_connection']);

        TenantContext::forget();
    }

    protected function applyConnectionConfig(array $overrides): void
    {
        Config::set('database.connections.sekolah_tenant', array_merge(
            Config::get('database.connections.sekolah_tenant', []),
            $overrides,
        ));

        DB::purge('sekolah_tenant');
        DB::connection('sekolah_tenant')->getPdo();
    }
}

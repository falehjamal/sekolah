<?php

namespace App\Http\Middleware;

use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantResolver;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class EnsureTenantSession
{
    public function __construct(
        protected TenantResolver $tenantResolver,
        protected TenantConnectionManager $tenantConnection
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $connection = $request->session()->get('tenant_connection');
        $tenantId = $request->session()->get('tenant_id');

        if ($connection) {
            try {
                $this->tenantConnection->connectFromSession();
            } catch (RuntimeException) {
                return $this->flushTenantContext($request);
            }
        } elseif ($tenantId) {
            try {
                $tenant = $this->tenantResolver->resolveActive($tenantId);
                $this->tenantConnection->rememberTenant($tenant);
                $this->tenantConnection->connectFromSession();
            } catch (ModelNotFoundException) {
                return $this->flushTenantContext($request);
            }
        }

        return $next($request);
    }

    protected function flushTenantContext(Request $request)
    {
        $this->tenantConnection->disconnect();
        Auth::guard('web')->logout();

        return redirect()->route('login')->withErrors([
            'idsekolah' => 'Tenant tidak ditemukan atau sedang nonaktif.',
        ]);
    }
}

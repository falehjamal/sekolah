<?php

namespace App\View\Composers;

use App\Models\Tenant\UserAccount;
use App\Support\TenantContext;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\View\View;

class AppLayoutComposer
{
    public function __construct(
        protected CacheFactory $cacheFactory,
        protected AuthFactory $auth
    ) {}

    public function compose(View $view): void
    {
        /** @var \Illuminate\Contracts\Auth\StatefulGuard $guard */
        $guard = $this->auth->guard();

        /** @var UserAccount|null $user */
        $user = $guard->user();

        $view->with('layoutUser', $this->rememberUserMeta($user));
    }

    protected function rememberUserMeta(?UserAccount $user): ?array
    {
        if (! $user) {
            return null;
        }

        $tenantKey = TenantContext::id() ?? session('tenant_id') ?? 'central';
        $version = $user->updated_at?->timestamp ?? $user->getKey();

        return $this->cache()->remember(
            sprintf('tenant:%s:user:%d:layout-meta:%s', $tenantKey, $user->getKey(), $version),
            now()->addMinutes(30),
            function () use ($user) {
                $user->loadMissing('level:id,name');

                $initial = strtoupper(mb_substr($user->name ?? 'U', 0, 1));

                return [
                    'name' => $user->name,
                    'role' => $user->level?->name ?? 'Pengguna',
                    'email' => $user->email,
                    'initial' => $initial,
                ];
            }
        );
    }

    protected function cache(): Repository
    {
        return $this->cacheFactory->store();
    }
}

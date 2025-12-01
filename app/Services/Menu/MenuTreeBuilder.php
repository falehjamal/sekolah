<?php

namespace App\Services\Menu;

use App\Models\Tenant\Menu;
use App\Models\Tenant\UserAccount;
use App\Support\TenantContext;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;

class MenuTreeBuilder
{
    public function __construct(
        protected CacheFactory $cacheFactory
    ) {}

    public function forUser(?UserAccount $user): Collection
    {
        $tenantId = $this->tenantId();

        if (! $tenantId || ! $user) {
            return collect();
        }

        $user->loadMissing('roles:id,name');
        $userRoleNames = $user->roles->pluck('name')
            ->map(static fn ($name) => (string) $name)
            ->all();

        $tree = $this->cache()
            ->remember($this->cacheKey($tenantId), now()->addHours(6), function () {
                return $this->buildTree();
            });

        return $this->filterTree(collect($tree), $user, $userRoleNames)->values();
    }

    public function flushTenantCache(?int $tenantId = null): void
    {
        $tenantId ??= $this->tenantId();

        if (! $tenantId) {
            return;
        }

        $this->cache()->forget($this->cacheKey($tenantId));
    }

    protected function buildTree(): array
    {
        $menus = Menu::query()
            ->with('roles:id,name')
            ->select(['id', 'parent_id', 'name', 'route_name', 'icon', 'sort_order', 'permission_name'])
            ->where('is_active', true)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($menus->isEmpty()) {
            return [];
        }

        $grouped = $menus->groupBy('parent_id');

        return $this->mapChildren($grouped, null)->all();
    }

    protected function filterTree(Collection $tree, UserAccount $user, array $userRoleNames): Collection
    {
        return $tree
            ->map(function (array $item) use ($user, $userRoleNames) {
                $children = $this->filterTree(collect($item['children'] ?? []), $user, $userRoleNames);

                $hasPermission = empty($item['permission_name']) || $user->can($item['permission_name']);
                $allowedRoles = $item['role_names'] ?? [];
                $hasRoleAccess = empty($allowedRoles) || ! empty(array_intersect($allowedRoles, $userRoleNames));
                $canSee = $hasPermission && $hasRoleAccess;

                if (! $canSee && $children->isEmpty()) {
                    return null;
                }

                unset($item['role_names']);
                $item['children'] = $children->values()->all();

                return $item;
            })
            ->filter()
            ->values();
    }

    protected function mapChildren(Collection $grouped, ?int $parentId): Collection
    {
        return $grouped
            ->get($parentId, collect())
            ->sortBy(function (Menu $menu) {
                $order = str_pad((string) $menu->sort_order, 5, '0', STR_PAD_LEFT);

                return $order.'-'.$menu->name;
            })
            ->map(function (Menu $menu) use ($grouped) {
                return [
                    'id' => (int) $menu->id,
                    'name' => $menu->name,
                    'route_name' => $menu->route_name,
                    'icon' => $menu->icon ?: 'bx bx-circle',
                    'permission_name' => $menu->permission_name,
                    'role_names' => $menu->roles->pluck('name')->map(static fn ($name) => (string) $name)->all(),
                    'children' => $this->mapChildren($grouped, $menu->id)->all(),
                ];
            })
            ->values();
    }

    protected function cacheKey(int $tenantId): string
    {
        return sprintf('tenant:%d:menus:tree', $tenantId);
    }

    protected function cache(): Repository
    {
        return $this->cacheFactory->store();
    }

    protected function tenantId(): ?int
    {
        return TenantContext::id() ?? session('tenant_id');
    }
}

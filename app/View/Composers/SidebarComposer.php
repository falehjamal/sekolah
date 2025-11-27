<?php

namespace App\View\Composers;

use App\Models\Tenant\UserAccount;
use App\Services\Menu\MenuTreeBuilder;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SidebarComposer
{
    public function __construct(
        protected MenuTreeBuilder $menuTreeBuilder,
        protected Request $request,
        protected AuthFactory $auth
    ) {}

    public function compose(View $view): void
    {
        /** @var \Illuminate\Contracts\Auth\StatefulGuard $guard */
        $guard = $this->auth->guard();

        /** @var UserAccount|null $user */
        $user = $guard->user();

        $menuTree = $this->menuTreeBuilder->forUser($user);
        $menuTree = $this->applyActiveState($menuTree, $this->request->route()?->getName());
        $menuTree = $this->assignUrls($menuTree);

        $view->with('menuTree', $menuTree);
    }

    protected function applyActiveState(Collection $items, ?string $routeName): Collection
    {
        if ($items->isEmpty()) {
            return $items;
        }

        return $items->map(function (array $item) use ($routeName) {
            $children = $this->applyActiveState(collect($item['children'] ?? []), $routeName);

            $isActive = $this->isRouteActive($item['route_name'], $routeName);
            $isOpen = $children->contains(function (array $child) {
                return ($child['is_active'] ?? false) || ($child['is_open'] ?? false);
            });

            $item['children'] = $children->all();
            $item['is_active'] = $isActive;
            $item['is_open'] = $isOpen || ($isActive && ! empty($item['children']));

            return $item;
        });
    }

    protected function assignUrls(Collection $items): Collection
    {
        if ($items->isEmpty()) {
            return $items;
        }

        return $items->map(function (array $item) {
            $item['children'] = $this->assignUrls(collect($item['children'] ?? []))->all();
            $item['url'] = $this->determineUrl($item['route_name']);

            return $item;
        });
    }

    protected function isRouteActive(?string $routePattern, ?string $routeName): bool
    {
        if (! $routePattern || ! $routeName) {
            return false;
        }

        return Str::is($routePattern, $routeName);
    }

    protected function determineUrl(?string $routeName): string
    {
        if (! $routeName || ! Route::has($routeName)) {
            return '#';
        }

        return route($routeName);
    }
}

<?php

namespace App\Observers;

use App\Models\Tenant\Menu;
use App\Services\Menu\MenuTreeBuilder;

class MenuObserver
{
    public function __construct(
        protected MenuTreeBuilder $menuTreeBuilder
    ) {}

    public function created(Menu $menu): void
    {
        $this->flushCache();
    }

    public function updated(Menu $menu): void
    {
        $this->flushCache();
    }

    public function deleted(Menu $menu): void
    {
        $this->flushCache();
    }

    public function restored(Menu $menu): void
    {
        $this->flushCache();
    }

    public function forceDeleted(Menu $menu): void
    {
        $this->flushCache();
    }

    protected function flushCache(): void
    {
        $this->menuTreeBuilder->flushTenantCache();
    }
}

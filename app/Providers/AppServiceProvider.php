<?php

namespace App\Providers;

use App\Models\Tenant\Menu;
use App\Observers\MenuObserver;
use App\Services\Tenant\PermissionTableConfigurator;
use App\View\Composers\AppLayoutComposer;
use App\View\Composers\SidebarComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PermissionTableConfigurator::class, function () {
            return new PermissionTableConfigurator(config('permission.table_names', []));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Menu::observe(MenuObserver::class);

        View::composer('layouts.partials.sidebar', SidebarComposer::class);
        View::composer('layouts.app', AppLayoutComposer::class);
    }
}

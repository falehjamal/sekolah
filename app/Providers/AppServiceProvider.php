<?php

namespace App\Providers;

use App\Services\Tenant\PermissionTableConfigurator;
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
        //
    }
}

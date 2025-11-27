<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\SiswaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// Tenant routes (loaded in web middleware group by TenancyServiceProvider)
Route::middleware(['web', 'auth'])->group(function () {
    // Siswa Routes
    Route::resource('siswa', SiswaController::class);
});

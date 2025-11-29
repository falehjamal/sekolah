<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LevelUserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Tenant\JurusanController;
use App\Http\Controllers\Tenant\KelasController;
use App\Http\Controllers\Tenant\MataPelajaranController;
use App\Http\Controllers\Tenant\OrangtuaController;
use App\Http\Controllers\Tenant\SiswaController;
use App\Http\Controllers\Tenant\SppController;
use App\Http\Controllers\UserAccountController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    Route::prefix('autentikasi')->group(function () {
        Route::resource('level-user', LevelUserController::class)
            ->names('auth.levels')
            ->parameters(['level-user' => 'level'])
            ->except(['create', 'edit']);

        Route::resource('user', UserAccountController::class)
            ->names('auth.users')
            ->parameters(['user' => 'user'])
            ->except(['create', 'edit']);

        Route::resource('menu', MenuController::class)
            ->names('auth.menus')
            ->parameters(['menu' => 'menu'])
            ->except(['create', 'edit']);
    });

    // Tenant Routes - Jurusan & Siswa
    Route::resource('jurusan', JurusanController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('orangtua', OrangtuaController::class);
    Route::resource('mata-pelajaran', MataPelajaranController::class);
    Route::resource('spp', SppController::class);
    Route::get('siswa/{siswa}/detail', [SiswaController::class, 'detail'])->name('siswa.detail');
    Route::resource('siswa', SiswaController::class);
});

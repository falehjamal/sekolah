<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LevelUserController;
use App\Http\Controllers\Tenant\JurusanController;
use App\Http\Controllers\Tenant\KelasController;
use App\Http\Controllers\Tenant\SiswaController;
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
            ->except('show');

        Route::resource('user', UserAccountController::class)
            ->names('auth.users')
            ->parameters(['user' => 'user'])
            ->except('show');
    });

    // Tenant Routes - Jurusan & Siswa
    Route::resource('jurusan', JurusanController::class);
    Route::resource('kelas', KelasController::class);
    Route::get('siswa/{siswa}/detail', [SiswaController::class, 'detail'])->name('siswa.detail');
    Route::resource('siswa', SiswaController::class);
});

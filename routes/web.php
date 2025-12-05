<?php

use App\Http\Controllers\DashboardPembayaranController;
use App\Http\Controllers\DashboardSiswaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LevelUserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Tenant\GajiGuruController;
use App\Http\Controllers\Tenant\GuruController;
use App\Http\Controllers\Tenant\JurusanController;
use App\Http\Controllers\Tenant\KelasController;
use App\Http\Controllers\Tenant\ListTagihanSppController;
use App\Http\Controllers\Tenant\MataPelajaranController;
use App\Http\Controllers\Tenant\OrangtuaController;
use App\Http\Controllers\Tenant\RekeningController;
use App\Http\Controllers\Tenant\SiswaController;
use App\Http\Controllers\Tenant\SppController;
use App\Http\Controllers\Tenant\TagihanManualController;
use App\Http\Controllers\UserAccountController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('dashboard/siswa', DashboardSiswaController::class)->name('dashboard.siswa');
    Route::get('dashboard-pembayaran', [DashboardPembayaranController::class, 'index'])->name('dashboard.pembayaran');

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
    Route::get('guru/{guru}/detail', [GuruController::class, 'detail'])->name('guru.detail');
    Route::resource('guru', GuruController::class);
    Route::get('siswa/{siswa}/detail', [SiswaController::class, 'detail'])->name('siswa.detail');
    Route::resource('siswa', SiswaController::class);
    Route::get('tagihan-manual/spp-info', [TagihanManualController::class, 'getSppInfo'])->name('tagihan-manual.spp-info');
    Route::get('tagihan-manual/history', [TagihanManualController::class, 'history'])->name('tagihan-manual.history');
    Route::resource('tagihan-manual', TagihanManualController::class)->except(['create', 'edit']);
    Route::resource('rekening', RekeningController::class)->except(['create', 'edit']);
    Route::resource('gaji-guru', GajiGuruController::class)->except(['create', 'edit']);

    // List Tagihan SPP
    Route::get('list-tagihan-spp', [ListTagihanSppController::class, 'index'])->name('list-tagihan-spp.index');
});

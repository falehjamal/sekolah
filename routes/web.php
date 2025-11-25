<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');

Route::view('/login', 'auth.login')->name('login');

<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::get('/', ['App\Http\Controllers\AdminController', 'index'])->name('dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', ['App\Http\Controllers\AdminController', 'loginForm'])->name('loginForm');
    Route::post('/login', ['App\Http\Controllers\AdminController', 'login'])->name('login');
    Route::post('/logout', ['App\Http\Controllers\AdminController', 'logout'])->name('logout');
});
Route::get('locale/{localeForAdmin}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::prefix('{locale}')
    ->where(['locale' => 'uk|en'])
    ->group(function () {
        Route::get('/', ['App\Http\Controllers\HomeController', 'index'])->name('home');
    });








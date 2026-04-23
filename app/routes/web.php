<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::get('/', ['App\Http\Controllers\AdminController', 'index'])->name('dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', ['App\Http\Controllers\AdminController', 'loginForm'])->name('loginForm');
});
Route::get('locale/{localeForAdmin}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::prefix('{locale}')
    ->group(function () {
        Route::get('/', ['App\Http\Controllers\HomeController', 'index'])->name('home');
    });

Route::get('/', function () {
    $locale = session('locale', config('app.locale'));
    return redirect('/' . $locale);
});






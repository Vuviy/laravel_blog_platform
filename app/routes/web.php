<?php

use Illuminate\Support\Facades\Route;


Route::get('/', ['App\Http\Controllers\HomeController', 'index'])->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', ['App\Http\Controllers\AdminController', 'index'])->name('dashboard');
});





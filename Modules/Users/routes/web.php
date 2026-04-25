<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\AuthController;
use Modules\Users\Http\Controllers\UsersController;

Route::resource('users', UsersController::class)->names('users');

Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::resource('users', \Modules\Users\Http\Controllers\Admin\UsersController::class)->names('users');
    Route::resource('roles', \Modules\Users\Http\Controllers\Admin\RoleController::class)->names('roles');
});

Route::prefix('{locale}')
    ->where(['locale' => 'uk|en'])
    ->group(function () {

        Route::get('/login', [AuthController::class, 'loginForm'])->name('loginForm');
        Route::get('/register', [AuthController::class, 'registerForm'])->name('registerForm');


    });
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


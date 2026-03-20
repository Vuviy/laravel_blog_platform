<?php

use App\Http\Controllers\Admin\ArticleController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});


Route::get('/', ['App\Http\Controllers\HomeController', 'index'])->name('home');
//Route::get('/articles', ['App\Http\Controllers\ArticleController', 'index'])->name('articles');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', ['App\Http\Controllers\AdminController', 'index'])->name('dashboard');

//    Route::resource('articles', ArticleController::class);
//    Route::prefix('/articles')->name('.articles')->group(function () {
//
//        Route::get('/', ['App\Http\Controllers\AdminController', 'articles']);
//        Route::get('/form', ['App\Http\Controllers\AdminController', 'create'])->name('.form');
//        Route::post('/store', ['App\Http\Controllers\AdminController', 'store'])->name('.store');
//        Route::post('/edit', ['App\Http\Controllers\AdminController', 'store'])->name('.edit');
//
//    });
});





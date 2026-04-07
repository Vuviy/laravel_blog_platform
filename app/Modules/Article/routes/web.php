<?php

use Illuminate\Support\Facades\Route;
use Modules\Article\Http\Controllers\Admin\ArticleController;


Route::prefix('{locale}')
    ->where(['locale' => 'uk|en'])
    ->group(function () {
        Route::get('/articles', ['Modules\Article\Http\Controllers\ArticleController', 'index'])->name('articles');
        Route::get('/articles/{id}', ['Modules\Article\Http\Controllers\ArticleController', 'show'])->name('articles.show');
    });


Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::resource('articles', ArticleController::class)->names('articles');
});

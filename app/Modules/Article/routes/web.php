<?php

use Illuminate\Support\Facades\Route;
use Modules\Article\Http\Controllers\Admin\ArticleController;

Route::get('/articles', ['Modules\Article\Http\Controllers\ArticleController', 'index'])->name('articles');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('articles', ArticleController::class);
});


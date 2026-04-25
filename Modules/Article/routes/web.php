<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Article\Http\Controllers\Admin\ArticleController;

Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::resource('articles', ArticleController::class)->names('articles');
});

Route::prefix('{locale}')
    ->group(function () {
        Route::get('/articles', ['Modules\Article\Http\Controllers\ArticleController', 'index'])->name('articles');
        Route::get('/articles/{slug}', ['Modules\Article\Http\Controllers\ArticleController', 'show'])->name('articles.show');
    });

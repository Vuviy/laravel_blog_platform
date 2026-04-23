<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\News\Http\Controllers\Admin\NewsController;

Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::resource('news', NewsController::class)->names('news');
});

Route::prefix('{locale}')
    ->group(function () {
        Route::get('/news', ['Modules\News\Http\Controllers\NewsController', 'index'])->name('news');
        Route::get('/news/{slug}', ['Modules\News\Http\Controllers\NewsController', 'show'])->name('news.show');
    });

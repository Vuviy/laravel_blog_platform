<?php

use Illuminate\Support\Facades\Route;
use Modules\Tags\Http\Controllers\Admin\TagsController;
use Modules\Tags\Http\Controllers\TagsController as ClientTagsController;


Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::resource('tags', TagsController::class);
});


Route::prefix('{locale}')
    ->group(function () {
        Route::get('/tags/{tagName}', [ClientTagsController::class, 'index'])->name('tags.index');
    });

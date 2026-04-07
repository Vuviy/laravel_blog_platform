<?php

use Illuminate\Support\Facades\Route;
use Modules\Tags\Http\Controllers\Admin\TagsController;


Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::resource('tags', TagsController::class);
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Comments\Http\Controllers\CommentsController;

Route::post('comments', [CommentsController::class, 'store'])->name('comments.store');


Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::resource('comments', \Modules\Comments\Http\Controllers\Admin\CommentsController::class)->names('comments');
});

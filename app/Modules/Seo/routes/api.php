<?php

use Illuminate\Support\Facades\Route;
use Modules\Seo\Http\Controllers\Admin\SeoController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('seo', SeoController::class)->names('seo');
});

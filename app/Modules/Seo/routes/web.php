<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Seo\Http\Controllers\Admin\SeoController;
use Modules\Seo\Http\Controllers\RobotsController;
use Modules\Seo\Http\Controllers\SitemapController;

Route::prefix('admin')->name('admin.')->middleware(['admin.auth'])->group(function () {
    Route::get('seo/robotForm', [RobotsController::class, 'robotForm'])->name('robotForm');
    Route::post('seo/saveRobot', [RobotsController::class, 'saveRobot'])->name('saveRobot');


    Route::get('seo/sitemapForm', [SitemapController::class, 'sitemapForm'])->name('sitemapForm');
    Route::post('seo/generateSitemap', [SitemapController::class, 'generateSitemap'])->name('generateSitemap');

    Route::resource('seo', SeoController::class)->names('seo');
});


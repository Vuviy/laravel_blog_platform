<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Seo\Http\Controllers\RobotsController;
use Modules\Seo\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/robots.txt', [RobotsController::class, 'index']);



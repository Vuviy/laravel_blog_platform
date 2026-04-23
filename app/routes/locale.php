<?php

use Illuminate\Support\Facades\Route;

$locales = implode('|', config('app.available_locales'));

Route::get('/{any}', function (string $any) {
    $locale = session('locale', config('app.locale'));
    return redirect('/' . $locale . '/' . trim($any, '/'));
})->where('any', '^(?!(' . $locales . '|admin)(/|$)).*');






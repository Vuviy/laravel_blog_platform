<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {

        $localeFromUrl = $request->route('locale');

        if ($localeFromUrl && in_array($localeFromUrl, config('app.available_locales'))) {
            App::setLocale($localeFromUrl);
            session(['locale' => $localeFromUrl]);
            $request->route()->forgetParameter('locale');
            return $next($request);
        }

        $localeFromSession = session('locale', config('app.locale'));

        if (in_array($localeFromSession, config('app.available_locales'))) {
            App::setLocale($localeFromSession);
        }

        return $next($request);
    }
}

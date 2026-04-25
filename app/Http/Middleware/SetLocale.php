<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {

        $localController = 'App\Http\Controllers\LocaleController' === $request->route()->getController() ? get_class($request->route()->getController()) : '';

        if ($request->is('admin')
            || $request->is('admin/*')
            || $request->is('locale*')
            || $request->method() !== 'GET'
            || $localController
            || $request->is('*robots.txt*')
            || $request->is('*sitemap.xml*')
        ) {
            $locale = session('locale', config('app.locale'));
            if (in_array($locale, config('app.available_locales'), true)) {
                App::setLocale($locale);
            }
            return $next($request);
        }

        $availableLocales = config('app.available_locales');
        $fallback = session('locale', config('app.locale'));
        $path = trim($request->path(), '/');
        $segments = $path === '' ? [] : explode('/', $path);
        $firstSegment = $segments[0] ?? null;


        if ($firstSegment !== null && in_array($firstSegment, $availableLocales, true)) {
            App::setLocale($firstSegment);
            session(['locale' => $firstSegment]);

            if ($request->route()) {
                $request->route()->forgetParameter('locale');
            }

            return $next($request);
        }

        if ($firstSegment !== null && !in_array($firstSegment, $availableLocales, true) && strlen($firstSegment) === 2) {
            $segments[0] = $fallback;
            App::setLocale($fallback);
            session(['locale' => $fallback]);
            return redirect('/' . implode('/', $segments));
        }

        array_unshift($segments, $fallback);
        App::setLocale($fallback);
        session(['locale' => $fallback]);
        return redirect('/' . implode('/', $segments));
    }

}

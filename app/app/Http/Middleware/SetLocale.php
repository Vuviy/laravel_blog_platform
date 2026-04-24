<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
//    public function handle(Request $request, Closure $next)
//    {
//
//        $localeFromUrl = $request->route('locale');
//
//        if ($localeFromUrl && in_array($localeFromUrl, config('app.available_locales'))) {
//            App::setLocale($localeFromUrl);
//            session(['locale' => $localeFromUrl]);
//            $request->route()->forgetParameter('locale');
//            return $next($request);
//        }
//
//        $localeFromSession = session('locale', config('app.locale'));
//
//        if (in_array($localeFromSession, config('app.available_locales'))) {
//            App::setLocale($localeFromSession);
//        }
//
//        return $next($request);
//    }

//    public function handle(Request $request, Closure $next)
//    {
//        $path = trim($request->path(), '/');
//
//        if ($request->is('admin') || $request->is('admin/*')) {
//            return $next($request);
//        }
//
//        $availableLocales = config('app.available_locales');
//        $fallback = session('locale', config('app.locale'));
//
//        $segments = $path === '' ? [] : explode('/', $path);
//
//        $firstSegment = $segments[0] ?? null;
//
//        if ($firstSegment !== null && in_array($firstSegment, $availableLocales, true)) {
//
//            if (!in_array($firstSegment, $availableLocales, true)) {
//                $segments[0] = $fallback;
//
//                return redirect('/' . implode('/', $segments));
//            }
//
//            App::setLocale($firstSegment);
//            session(['locale' => $firstSegment]);
//
//            if ($request->route()) {
//                $request->route()->forgetParameter('locale');
//            }
//
//            return $next($request);
//        }
//
//        App::setLocale($fallback);
//
//        array_unshift($segments, $fallback);
//
//        return redirect('/' . implode('/', $segments));
//    }

    public function handle(Request $request, Closure $next)
    {

        if ($request->is('admin')
        || $request->is('admin/*')
        || $request->is('locale*')
        || $request->method() !== 'GET'
        || 'App\Http\Controllers\LocaleController' === $request->route()->getController() ? get_class($request->route()->getController()) : ''
            || $request->is('*robots.txt*')
            || $request->is('*sitemap.xml*')
        ) {
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

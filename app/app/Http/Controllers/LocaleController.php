<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;

class LocaleController  extends Controller
{
    public function switch(string $localeForAdmin)
    {
        if (in_array($localeForAdmin, config('app.available_locales'))) {
            session(['locale' => $localeForAdmin]);
            App::setLocale($localeForAdmin);
        }
//        dd(session('locale'), $localeForAdmin, app()->getLocale());
        return redirect()->back();
    }
}

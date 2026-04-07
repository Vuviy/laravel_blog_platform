<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class LocaleController  extends Controller
{
    public function switch(string $localeForAdmin)
    {
        if (in_array($localeForAdmin, config('app.available_locales'))) {
            session(['locale' => $localeForAdmin]);
        }
        return redirect()->back();
    }
}

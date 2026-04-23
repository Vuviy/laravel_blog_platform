<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Seo\Facades\Seo;

class HomeController extends Controller
{
    public function index()
    {
        Seo::seoForStaticPage('/');
        return view('home');
    }
}

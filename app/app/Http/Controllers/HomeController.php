<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{

    public function index()
    {

        $title = 'Home';

        return view('home', compact( 'title'));

    }


}

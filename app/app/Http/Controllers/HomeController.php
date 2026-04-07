<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{

    public function index(Request $request)
    {

        $title = 'Home';
        $user = $request->attributes->get('user');
        return view('home', compact( 'title', 'user'));

    }


}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $title = 'Admin';
        return view('admin.index', compact('title'));
    }

    public function loginForm()
    {
        $title = 'Login';
        return view('admin.login_form', compact('title'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    public function index()
    {
        $title = 'Admin';
        return view('admin.index', compact('title'));
    }

}

<?php

namespace Modules\Seo\Http\Controllers;


use Illuminate\Http\Request;

class RobotsController
{

    public function robotForm()
    {
        $content = file_get_contents(config('seo.robots_path'));
        return view('seo::admin.robot-form', compact('content'));
    }

    public function saveRobot(Request $request)
    {
        file_put_contents(config('seo.robots_path'), $request->input('content'));
        return redirect()->back();
    }

    public function index()
    {
        $content = file_get_contents(config('seo.robots_path'));
        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}

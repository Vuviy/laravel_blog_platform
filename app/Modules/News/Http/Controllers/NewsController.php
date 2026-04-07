<?php

namespace Modules\News\Http\Controllers;

use Illuminate\Http\Request;

class NewsController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('news::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('news::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('news::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('news::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}

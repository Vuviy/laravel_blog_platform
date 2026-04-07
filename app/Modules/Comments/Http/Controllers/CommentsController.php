<?php

namespace Modules\Comments\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Comments\Http\Requests\CommentCreateRequest;
use Modules\Comments\Services\CommentService;

class CommentsController
{

    public function __construct(private CommentService $commentService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('comments::form');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('comments::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommentCreateRequest $request)
    {

        $data = $request->validated();

        $this->commentService->create($data);


        return redirect()->back()->with('success', 'Comment created.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('comments::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('comments::edit');
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

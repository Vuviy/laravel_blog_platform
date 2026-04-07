<?php

namespace Modules\Tags\Http\Controllers\Admin;

use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Tags\Services\TagService;

class TagsController
{

    public function __construct(
        private TagService $service
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = $this->service->getAll();
        $title = 'Tags';

        return view('tags::admin.index', compact( 'tags','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Create a new article';
        return view('tags::admin.form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $id = $this->service->create($request->all());
        return redirect(route('admin.tags.edit', ['tag' => $id]))->with('success', 'tag created successfully');

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('tags::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tag = $this->service->getById(new Id($id));
        $title = 'Edit a new article';

        return view('tags::admin.form', compact('title', 'tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.tags.edit', ['tag' => $id]))->with('success', 'tags edited successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.tags.index'))->with('success', 'tags deleted successfully');
    }
}

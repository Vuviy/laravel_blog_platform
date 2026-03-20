<?php

namespace App\Http\Controllers\Admin;

use App\Services\ArticleService;
use App\ValueObjects\ArticleId;
use Illuminate\Http\Request;

class ArticleController
{

    public function __construct(
        private ArticleService $service
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {


        $articles = $this->service->getAll();
        $title = 'Articles';

        return view('admin.articles.index', compact( 'articles','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Create a new article';

        return view('admin.articles.form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $article = $this->service->getArticleById(new ArticleId($id));

        $title = 'Edit a new article';

        return view('admin.articles.form', compact('title', 'article'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace Modules\Article\Http\Controllers\Admin;


use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\FilterDTO\Filter;
use Modules\Article\Http\Requests\ArticleCreateRequest;
use Modules\Article\Http\Requests\ArticleUpdateRequest;
use Modules\Article\Repositories\ArticleRepository;
use Modules\Article\Services\ArticleService;
use Modules\Article\ValueObjects\ArticleId;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Repositories\TagRepository;
use Modules\Tags\ValueObjects\TagId;
use Modules\Tags\ValueObjects\TagTitle;

class ArticleController
{

    public function __construct(
        private ArticleService $service,
        private TagRepository  $tagRepository
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $filter = ArticleFilter::fromRequest($request);
        $articles = $this->service->getAll($filter);

        $title = 'Articles';

        return view('article::admin.index', compact('articles', 'title', 'filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = __('common.create');
        $tags = $this->tagRepository->getAllList();

        return view('article::admin.form', compact('title', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
//    public function store(Request $request)
    public function store(ArticleCreateRequest $request)
    {
        $id = $this->service->create($request->all());
        return redirect(route('admin.articles.edit', ['article' => $id]))->with('success', 'Article created successfully');
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
        $article = $this->service->getArticleById(new Id($id));

//        dd($article->translate(app()->getLocale())->seoTitle);
        $title = __('common.edit');
        $tags = $this->tagRepository->getAllList();

        $selectedTagIds = array_map(
            fn(Tag $tag) => $tag->id->getValue(),
            $article->tags
        );

        return view('article::admin.form', compact('title', 'article', 'tags', 'selectedTagIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleUpdateRequest $request, string $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.articles.edit', ['article' => $id]))->with('success', 'Article edited successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.articles.index'))->with('success', 'Article deleted successfully');
    }
}

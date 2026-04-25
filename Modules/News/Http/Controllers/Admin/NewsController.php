<?php
declare(strict_types=1);

namespace Modules\News\Http\Controllers\Admin;

use App\ValueObjects\Id;
use Illuminate\Http\Request;
use Modules\News\Filter\NewsFilter;
use Modules\News\Http\Requests\NewsCreateRequest;
use Modules\News\Http\Requests\NewsUpdateRequest;
use Modules\News\Services\NewsService;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Repositories\TagRepository;


class NewsController
{
    public function __construct(
        private NewsService $service,
        private TagRepository $tagRepository
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = NewsFilter::fromRequest($request);
        $news = $this->service->getAll($filter);
        $title = __('common.news');

        return view('news::admin.index', compact( 'news','title', 'filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = __('common.create');
        $tags = $this->tagRepository->getAllList();

        return view('news::admin.form', compact('title', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewsCreateRequest $request)
    {
        $id = $this->service->create($request->all());
        return redirect(route('admin.news.edit', ['news' => $id]))->with('success', 'news created successfully');
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
        $news = $this->service->getNewById(new Id($id));
        $title = __('common.edit');
        $tags = $this->tagRepository->getAllList();

        $selectedTagIds = array_map(
            fn(Tag $tag) => $tag->id->getValue(),
            $news->tags
        );

        return view('news::admin.form', compact('title', 'news', 'tags', 'selectedTagIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NewsUpdateRequest $request, string $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.news.edit', ['news' => $id]))->with('success', 'new edited successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.news.index'))->with('success', 'new deleted successfully');
    }
}

<?php

namespace Modules\Seo\Http\Controllers\Admin;

use App\ValueObjects\Id;
use Modules\Seo\Http\Requests\SeoPageCreateRequest;
use Modules\Seo\Http\Requests\SeoPageUpdateRequest;
use Modules\Seo\Services\SeoPageService;

class SeoController
{
    public function __construct(
        private SeoPageService $service,
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $seoPages = $this->service->getAll();

        $title = 'SEO';

        return view('seo::admin.index', compact( 'seoPages','title'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = __('common.create');

        return view('seo::admin.form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SeoPageCreateRequest $request)
    {
        $id = $this->service->create($request->all());
        return redirect(route('admin.seo.edit', ['seo' => $id]))->with('success', 'seo created successfully');
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
        $seoPage = $this->service->getSeoPageById(new Id($id));

        $title = __('common.edit');

        return view('seo::admin.form', compact('title', 'seoPage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeoPageUpdateRequest $request, string $id)
    {
        $this->service->update(new Id($id), $request->all());
        return redirect(route('admin.seo.edit', ['seo' => $id]))->with('success', 'seo edited successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->delete(new Id($id));
        return redirect(route('admin.seo.index'))->with('success', 'seo deleted successfully');
    }
}

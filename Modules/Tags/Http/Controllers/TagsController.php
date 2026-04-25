<?php

declare(strict_types=1);

namespace Modules\Tags\Http\Controllers;

use Modules\Tags\Services\TagService;

class TagsController
{
    public function __construct(private TagService $service)
    {
    }

    public function index(string $tagName)
    {
        $tag = $this->service->getByTagName($tagName);

        if (null === $tag) {
            abort(404);
        }

        $entities = $this->service->getEntitiesByTag($tag);

        return view('tags::index', compact('tag', 'entities'));
    }
}

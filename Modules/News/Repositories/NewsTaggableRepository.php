<?php

namespace Modules\News\Repositories;

use Modules\News\Entities\News;
use Modules\News\Repositories\Contracts\NewsRepositoryInterface;
use Modules\Tags\DTO\TaggedEntityDTO;
use Modules\Tags\Repositories\Contracts\TaggableRepositoryInterface;

class NewsTaggableRepository implements TaggableRepositoryInterface
{
    private const ENTITY_TYPE = 'Modules\News\Entities\News';
    public function __construct(private NewsRepositoryInterface $repository)
    {
    }

    public function getEntityType(): string
    {
        return self::ENTITY_TYPE;
    }

    public function getByIds(array $ids): array
    {
        $news = $this->repository->getByIds($ids)->toArray();

        if(empty($news)) {
            return [];
        }
        $locale = app()->currentLocale();

        return array_map(function (News $new) use ($locale) {
            $translation = $new->translate($locale);

            return new TaggedEntityDTO(
                id: $new->id->getValue(),
                type: 'news',
                title: $translation->title->getValue(),
                text: mb_substr($translation->text->getValue(), 0, 100),
                url: route('news.show', [
                    'locale' => $locale,
                    'slug' => $new->slug
                ]),

                createdAt: $new->created_at,
            );
        }, $news);
    }

}

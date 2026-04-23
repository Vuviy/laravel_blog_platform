<?php

namespace Modules\Article\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Article\Entities\Article;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\Tags\DTO\TaggedEntityDTO;
use Modules\Tags\Repositories\Contracts\TaggableRepositoryInterface;

class ArticleTaggableRepository implements TaggableRepositoryInterface
{
    public function __construct(private ArticleRepositoryInterface $repository)
    {
    }

    public function getEntityType(): string
    {
       return 'Modules\Article\Entities\Article';
    }

    public function getByIds(array $ids): array
    {
        $articles = $this->repository->getByIds($ids)->toArray();
        $locale = app()->currentLocale();

        return array_map(function (Article $article) use ($locale) {
            $translation = $article->translate($locale);

            return new TaggedEntityDTO(
                id:        $article->id->getValue(),
                type:      'article',
                title:     $translation->title->getValue(),
                text:     mb_substr($translation->text->getValue(), 0, 100),
                url:       route('articles.show', [
                    'locale' => $locale,
                    'slug'     => $article->slug
                ]),

                createdAt: $article->created_at,
            );
        }, $articles);
    }
}

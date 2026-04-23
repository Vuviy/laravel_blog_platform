<?php

declare(strict_types=1);

namespace Modules\Article\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\Article\ValueObjects\ArticleTitle;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Comments\Entities\Comment;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\ValueObjects\CommentText;
use Modules\Tags\Entities\Tag;
use Modules\Tags\ValueObjects\TagTitle;
use Symfony\Component\Uid\UuidV7;

class ArticleRepository implements ArticleRepositoryInterface
{
    private const TABLE_NAME = 'articles';
    private const PIVOT_TABLE = 'taggables';
    private const COMMENT_TABLE = 'nested_comments';
    private const TRANSLATIONS_TABLE = 'article_translations';
    private const ENTITY_TYPE = 'Modules\Article\Entities\Article';

    public function syncTags(Id $articleId, array $tagIds): void
    {
        DB::table(self::PIVOT_TABLE)
            ->where('entity_id', $articleId->getValue())
            ->delete();

        if (empty($tagIds)) {
            return;
        }

        $rows = array_map(fn($tagId) => [
            'entity_id' => $articleId->getValue(),
            'entity_type' => self::ENTITY_TYPE,
            'tag_id' => $tagId,
            'created_at' => new \DateTimeImmutable(),
            'updated_at' => new \DateTimeImmutable(),
        ], $tagIds);

        DB::table(self::PIVOT_TABLE)->insert($rows);
    }

    private function getTagsForArticle(string $articleId): array
    {
        return $this->getTagsForArticles([$articleId])[$articleId] ?? [];
    }

    private function getTagsForArticles(array $articleIds): array
    {
        if (empty($articleIds)) {
            return [];
        }

        $rows = DB::table('tags')
            ->join(self::PIVOT_TABLE, 'tags.id', '=', self::PIVOT_TABLE . '.tag_id')
            ->whereIn(self::PIVOT_TABLE . '.entity_id', $articleIds)
            ->select('tags.*', self::PIVOT_TABLE . '.entity_id')
            ->get();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->entity_id][] = new Tag(
                new Id($row->id),
                new TagTitle($row->title),
                new \DateTimeImmutable($row->created_at),
                new \DateTimeImmutable($row->updated_at),
            );
        }

        return $grouped;
    }

    public function get(Id $articleId): ?Article
    {
        $article = DB::table(self::TABLE_NAME)->find($articleId);
        if (null === $article) {
            return null;
        }
        $tags = $this->getTagsForArticle($article->id);

        return new Article(
            id: new Id($article->id),
            slug: $article->slug,
            status: (bool)$article->status,
            created_at: new \DateTimeImmutable($article->created_at),
            updated_at: new \DateTimeImmutable($article->updated_at),
            tags: $tags,
            translations: $this->getTranslationsForArticles($article->id),
            comments: $this->getCommentsForArticle(new Id($article->id))
        );

    }

    public function getBySlug(string $slug): ?Article
    {
        $article = DB::table(self::TABLE_NAME)->where('slug', $slug)->first();
        if (null === $article) {
            return null;
        }
        $tags = $this->getTagsForArticle($article->id);

        return new Article(
            id: new Id($article->id),
            slug: $article->slug,
            status: (bool)$article->status,
            created_at: new \DateTimeImmutable($article->created_at),
            updated_at: new \DateTimeImmutable($article->updated_at),
            tags: $tags,
            translations: $this->getTranslationsForArticles($article->id),
            comments: $this->getCommentsForArticle(new Id($article->id))
        );
    }

    public function save(Article $article): string
    {
        if ($article->id === null) {
            $id = $this->nextId();
            DB::table(self::TABLE_NAME)->insert([
                'id' => $id,
                'status' => $article->status,
                'slug' => $article->slug,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return (string)$id;
        }

        DB::table(self::TABLE_NAME)->where('id', $article->id->getValue())->update([
            'status' => $article->status,
            'slug' => $article->slug,
            'updated_at' => new \DateTimeImmutable(),
        ]);
        return $article->id->getValue();

    }

    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function getAll(ArticleFilter $filter): LengthAwarePaginator
    {
        $query = DB::table(self::TABLE_NAME . ' as a');

        if ($filter->search) {
            $query->join(self::TRANSLATIONS_TABLE . ' as at', 'a.id', '=', 'at.article_id')
                ->where('at.title', 'LIKE', "%{$filter->search}%")
                ->distinct();
        }

        if (!is_null($filter->status)) {
            $query->where('a.status', $filter->status);
        }

        if ($filter->dateFrom) {
            $query->where('a.created_at', '>=', $filter->dateFrom);
        }

        if ($filter->dateTo) {
            $query->where('a.created_at', '<=', $filter->dateTo . ' 23:59:59');
        }

        $query->orderBy("a.{$filter->sortBy}", $filter->sortDir)
            ->select('a.*');

        $paginator = $query->paginate($filter->perPage);

        $articleIds = array_column($paginator->items(), 'id');
        $tagsGrouped = $this->getTagsForArticles($articleIds);
        $collection = new Collection();

        foreach ($paginator->items() as $article) {
            $articleEntity = new Article(
                id: new Id($article->id),
                slug: $article->slug,
                status: (bool)$article->status,
                created_at: new \DateTimeImmutable($article->created_at),
                updated_at: new \DateTimeImmutable($article->updated_at),
                tags: $tagsGrouped[$article->id] ?? [],
                translations: $this->getTranslationsForArticles($article->id),
                comments: $this->getCommentsForArticle(new Id($article->id))
            );
            $collection->push($articleEntity);
        }

        $paginator->setCollection($collection);
        $paginator->appends($filter->toArray());

        return $paginator;
    }


    public function getAllPublished(): Collection
    {
        $articles = DB::table(self::TABLE_NAME)->orderBy('created_at')->where('status',true)->get();

        $collection = new Collection();

        foreach ($articles as $article) {
            $articleEntity = new Article(
                id: new Id($article->id),
                slug: $article->slug,
                status: (bool)$article->status,
                created_at: new \DateTimeImmutable($article->created_at),
                updated_at: new \DateTimeImmutable($article->updated_at),
                tags:  [],
                translations: $this->getTranslationsForArticles($article->id),
                comments: []
            );
            $collection->push($articleEntity);
        }

        return $collection;
    }

    public function getByIds(array $ids): Collection
    {
        $articles = DB::table(self::TABLE_NAME)
            ->orderBy('created_at')
            ->where('status',true)
            ->whereIn('id', $ids)
            ->get();

        $collection = new Collection();

        foreach ($articles as $article) {
            $articleEntity = new Article(
                id: new Id($article->id),
                slug: $article->slug,
                status: (bool)$article->status,
                created_at: new \DateTimeImmutable($article->created_at),
                updated_at: new \DateTimeImmutable($article->updated_at),
                tags:  [],
                translations: $this->getTranslationsForArticles($article->id),
                comments: []
            );
            $collection->push($articleEntity);
        }
        return $collection;
    }


    private function getCommentsForArticle(Id $articleId): array
    {

        $row = DB::table(self::COMMENT_TABLE . ' as c')
            ->join(self::COMMENT_TABLE . ' as root', function ($join) {
                $join->on('root.lft', '<=', 'c.lft')
                    ->on('root.rgt', '>=', 'c.rgt')
                    ->on('root.entity_type', '=', 'c.entity_type')
                    ->on('root.entity_id', '=', 'c.entity_id')
                    ->whereNull('root.parent_id');
            })
            ->where('c.entity_type', self::ENTITY_TYPE)
            ->where('c.entity_id', $articleId->getValue())
            ->where('c.status', CommentStatus::APPROVED)
            ->orderByDesc('root.created_at')
            ->orderBy('c.lft')
            ->select('c.*')
            ->get();

        $comments = [];
        foreach ($row as $comment) {
            $comments[] = new Comment(
                id: new Id($comment->id),
                status: CommentStatus::from($comment->status),
                entityType: EntityType::from($comment->entity_type),
                userId: new Id($comment->user_id),
                entityId: new Id($comment->entity_id),
                parentId: $comment->parent_id,
                lft: $comment->lft,
                rgt: $comment->rgt,
                depth: $comment->depth,
                content: new CommentText($comment->content),
                created_at: new \DateTimeImmutable($comment->created_at),
                updated_at: new \DateTimeImmutable($comment->updated_at),
            );
        }


        return $comments;
    }

    public function getTranslationsForArticles(string $articleId): array
    {
        if (empty($articleId)) return [];

        $rows = DB::table(self::TRANSLATIONS_TABLE)
            ->where('article_id', $articleId)
            ->get();

        $translations = [];
        foreach ($rows as $row) {
            $translations[$row->locale] = new ArticleTranslation(
                id: new Id($row->id),
                articleId: new Id($row->article_id),
                locale: $row->locale,
                title: new ArticleTitle($row->title),
                text: new ArticleText($row->text),

                seoTitle: $row->seo_title,
                seoDescription: $row->seo_description,
                seoKeywords: $row->seo_keywords,
                seoOgImage: $row->seo_og_image,
            );
        }
        return $translations;
    }

    public function saveTranslation(ArticleTranslation $translation): void
    {
        DB::table(self::TRANSLATIONS_TABLE)
            ->updateOrInsert(
                [
                    'article_id' => $translation->articleId->getValue(),
                    'locale' => $translation->locale,
                ],
                [
                    'id' => $translation->id?->getValue() ?? (string)new UuidV7(),
                    'title' => $translation->title?->getValue(),
                    'text' => $translation->text?->getValue(),
                    'seo_title' => $translation->seoTitle,
                    'seo_description' => $translation->seoDescription,
                    'seo_keywords' => $translation->seoKeywords,
                    'seo_og_image' => $translation->seoOgImage,
                    'updated_at' => new \DateTimeImmutable(),
                    'created_at' => new \DateTimeImmutable(),
                ]
            );
    }

    public function nextId(): Id
    {
        return new Id((string)new UuidV7());
    }
}

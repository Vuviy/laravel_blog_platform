<?php

declare(strict_types=1);

namespace Modules\News\Repositories;

use App\Contracts\FilterInterface;
use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Comments\Entities\Comment;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\ValueObjects\CommentText;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsTranslation;
use Modules\News\Filter\NewsFilter;
use Modules\News\Repositories\Contracts\NewsRepositoryInterface;
use Modules\News\ValueObjects\NewsText;
use Modules\News\ValueObjects\NewsTitle;
use Modules\Tags\Entities\Tag;
use Modules\Tags\ValueObjects\TagTitle;
use Symfony\Component\Uid\UuidV7;

class NewsRepository implements NewsRepositoryInterface
{
    private const TABLE_NAME = 'news';
    private const PIVOT_TABLE = 'taggables';
    private const COMMENT_TABLE = 'comments';
    private const TRANSLATIONS_TABLE = 'news_translations';
    private const ENTITY_TYPE = 'Modules\News\Entities\News';

    public function syncTags(Id $newId, array $tagIds): void
    {
        DB::table(self::PIVOT_TABLE)
            ->where('entity_id', $newId->getValue())
            ->delete();

        if (empty($tagIds)) {
            return;
        }

        $rows = array_map(fn($tagId) => [
            'entity_id' => $newId->getValue(),
            'entity_type' => self::ENTITY_TYPE,
            'tag_id' => $tagId,
        ], $tagIds);

        DB::table(self::PIVOT_TABLE)->insert($rows);
    }

    private function getTagsForNew(string $newId): array
    {
        return $this->getTagsForNews([$newId])[$newId] ?? [];
    }

    private function getTagsForNews(array $newIds): array
    {
        if (empty($newIds)) {
            return [];
        }

        $rows = DB::table('tags')
            ->join(self::PIVOT_TABLE, 'tags.id', '=', self::PIVOT_TABLE . '.tag_id')
            ->whereIn(self::PIVOT_TABLE . '.entity_id', $newIds)
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

    public function get(Id $newId): ?News
    {
        $new = DB::table(self::TABLE_NAME)->find($newId);

        if (null === $new) {
            return null;
        }
        $tags = $this->getTagsForNew($new->id);

        return new News(
            id: new Id($new->id),
            status: (bool)$new->status,
            slug: $new->slug,
            created_at: new \DateTimeImmutable($new->created_at),
            updated_at: new \DateTimeImmutable($new->updated_at),
            tags: $tags,
            translations: $this->getTranslationsForNews($new->id),
            comments: $this->getCommentsForNew(new Id($new->id))
        );
    }

    public function getBySlug(string $slug): ?News
    {
        $new = DB::table(self::TABLE_NAME)->where('slug', $slug)->first();

        if (null === $new) {
            return null;
        }
        $tags = $this->getTagsForNew($new->id);
        return new News(
            id: new Id($new->id),
            status: (bool)$new->status,
            slug: $new->slug,
            created_at: new \DateTimeImmutable($new->created_at),
            updated_at: new \DateTimeImmutable($new->updated_at),
            tags: $tags,
            translations: $this->getTranslationsForNews($new->id),
            comments: $this->getCommentsForNew(new Id($new->id))
        );
    }

    public function save(News $new): string
    {
        if ($new->id === null) {
            $id = $this->nextId();
            DB::table(self::TABLE_NAME)->insert([
                'id' => $id,
                'status' => $new->status,
                'slug' => $new->slug,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return (string)$id;
        }

        DB::table(self::TABLE_NAME)->where('id', $new->id->getValue())->update([
            'status' => $new->status,
            'slug' => $new->slug,
            'updated_at' => new \DateTimeImmutable(),
        ]);
        return $new->id->getValue();

    }

    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function getAll(FilterInterface $filter): LengthAwarePaginator
    {
        $query = DB::table(self::TABLE_NAME . ' as n');

        if ($filter->search) {
            $query->join(self::TRANSLATIONS_TABLE .' as nt', 'n.id', '=', 'nt.news_id')
                ->where('at.title', 'LIKE', "%{$filter->search}%")
                ->distinct();
        }

        if (!is_null($filter->status)) {
            $query->where('n.status', $filter->status);
        }

        if ($filter->dateFrom) {
            $query->where('n.created_at', '>=', $filter->dateFrom);
        }

        if ($filter->dateTo) {
            $query->where('n.created_at', '<=', $filter->dateTo . ' 23:59:59');
        }

        $query->orderBy("n.{$filter->sortBy}", $filter->sortDir)
            ->select('n.*');

        $paginator = $query->paginate($filter->perPage);

        $newIds = array_column($paginator->items(), 'id');
        $tagsGrouped = $this->getTagsForNews($newIds);
        $collection = new Collection();

        foreach ($paginator->items() as $new) {
            $newEntity = new News(
                id: new Id($new->id),
                status: (bool)$new->status,
                slug: $new->slug,
                created_at: new \DateTimeImmutable($new->created_at),
                updated_at: new \DateTimeImmutable($new->updated_at),
                tags: $tagsGrouped[$new->id] ?? [],
                translations: $this->getTranslationsForNews($new->id),
                comments: $this->getCommentsForNew(new Id($new->id))
            );
            $collection->push($newEntity);
        }

        $paginator->setCollection($collection);
        $paginator->appends($filter->toArray());

        return $paginator;
    }


    public function getAllPublished(): Collection
    {
        $news = DB::table(self::TABLE_NAME)->orderBy('created_at')->where('status',true)->get();

        $collection = new Collection();

        foreach ($news as $new) {
            $newEntity = new News(
                id: new Id($new->id),
                status: (bool)$new->status,
                slug: $new->slug,
                created_at: new \DateTimeImmutable($new->created_at),
                updated_at: new \DateTimeImmutable($new->updated_at),
                tags: [],
                translations: $this->getTranslationsForNews($new->id),
                comments: []
            );
            $collection->push($newEntity);
        }

        return $collection;
    }



    public function getByIds(array $ids): Collection
    {
        $news = DB::table(self::TABLE_NAME)
            ->orderBy('created_at')
            ->where('status',true)
            ->whereIn('id', $ids)
            ->get();

        $collection = new Collection();

        foreach ($news as $new) {
            $newEntity = new News(
                id: new Id($new->id),
                status: (bool)$new->status,
                slug: $new->slug,
                created_at: new \DateTimeImmutable($new->created_at),
                updated_at: new \DateTimeImmutable($new->updated_at),
                tags: [],
                translations: $this->getTranslationsForNews($new->id),
                comments: []
            );
            $collection->push($newEntity);
        }
        return $collection;
    }


    private function getCommentsForNew(Id $newId): array
    {

        $row = DB::table(self::COMMENT_TABLE)
            ->where('entity_id', $newId->getValue())
            ->where('status', CommentStatus::APPROVED)
            ->orderBy('created_at', 'desc')
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

    public function getTranslationsForNews(string $newId): array
    {
        if (empty($newId)) return [];

        $rows = DB::table(self::TRANSLATIONS_TABLE)
            ->where('news_id', $newId)
            ->get();

        $translations = [];
        foreach ($rows as $row) {
            $translations[$row->locale] = new NewsTranslation(
                id: new Id($row->id),
                newId: new Id($row->news_id),
                locale: $row->locale,
                title: new NewsTitle($row->title),
                text: new NewsText($row->text),

                seoTitle: $row->seo_title,
                seoDescription: $row->seo_description,
                seoKeywords: $row->seo_keywords,
                seoOgImage: $row->seo_og_image,

            );
        }
        return $translations;
    }

    public function saveTranslation(NewsTranslation $translation): void
    {
        DB::table(self::TRANSLATIONS_TABLE)
            ->updateOrInsert(
                [
                    'news_id' => $translation->newId->getValue(),
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

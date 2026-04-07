<?php

declare(strict_types=1);

namespace Modules\Article\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
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
    private CONST TABLE_NAME = 'articles';
    private const PIVOT_TABLE = 'article_tag';
    private const TRANSLATIONS_TABLE = 'article_translations';
    private const ENTITY_TYPE = 'Modules\Article\Entities\Article';

    public function syncTags(Id $articleId, array $tagIds): void
    {
        DB::table(self::PIVOT_TABLE)
            ->where('article_id', $articleId->getValue())
            ->delete();

        if (empty($tagIds)) {
            return;
        }

        $rows = array_map(fn($tagId) => [
            'article_id' => $articleId->getValue(),
            'tag_id'     => $tagId,
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
            ->whereIn(self::PIVOT_TABLE . '.article_id', $articleIds)
            ->select('tags.*', self::PIVOT_TABLE . '.article_id')
            ->get();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->article_id][] = new Tag(
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
        $article =  DB::table(self::TABLE_NAME)->find($articleId);

        if(null === $article) {
           return null;
        }
        $tags = $this->getTagsForArticle($article->id);

        return new Article(
            new Id($article->id),
            $article->status,
            new \DateTimeImmutable($article->created_at),
            new \DateTimeImmutable($article->updated_at),
            $tags,
            $this->getTranslationsForArticles($article->id),
            comments: $this->getCommentsForArticle(new Id($article->id))
        );

    }
    public function save(Article $article): string
    {
        if ($article->id === null) {
            $id = $this->nextId();
            DB::table(self::TABLE_NAME)->insert([
                'id' =>  $this->nextId(),
                'status'  => $article->status,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return (string)$id;
        }

            DB::table(self::TABLE_NAME)->where('id', $article->id->getValue())->update([
                'status'  => $article->status,
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return $article->id->getValue();

    }
    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        $paginator =  DB::table(self::TABLE_NAME)->orderBy('created_at')->paginate(2);
        $articleIds = array_column($paginator->items(), 'id');
        $tagsGrouped = $this->getTagsForArticles($articleIds);
        $collection = new Collection();

        foreach ($paginator->items() as $article) {
            $articleEntity = new Article(
                new Id($article->id),
                $article->status,
                new \DateTimeImmutable($article->created_at),
                new \DateTimeImmutable($article->updated_at),
                $tagsGrouped[$article->id] ?? [],
                $this->getTranslationsForArticles($article->id),
                comments: $this->getCommentsForArticle(new Id($article->id))
            );
            $collection->push($articleEntity);
        }

        $paginator->setCollection($collection);

        return $paginator;
    }


    private function getCommentsForArticle(Id $articleId): array
    {

        $row = DB::table('comments')
            ->where('entity_id', $articleId->getValue())
            ->where('entity_type', self::ENTITY_TYPE)
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
                content: new CommentText($comment->content),
                created_at: new \DateTimeImmutable($comment->created_at),
                updated_at: new \DateTimeImmutable($comment->updated_at),
            );
        }


        return $comments;
    }
    private function getTranslationsForArticles(string $articleId): array
    {
        if (empty($articleId)) return [];

        $rows = DB::table(self::TRANSLATIONS_TABLE)
            ->where('article_id', $articleId)
            ->get();

        $translations = [];
        foreach ($rows as $row) {
            $translations[] = new ArticleTranslation(
                new Id($row->id),
                new Id($row->article_id),
                $row->locale,
                new ArticleTitle($row->title),
                new ArticleText($row->text),
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
                    'locale'     => $translation->locale,
                ],
                [
                    'id'    => $translation->id?->getValue() ?? (string) new UuidV7(),
                    'title' => $translation->title?->getValue(),
                    'text'  => $translation->text?->getValue(),
                    'updated_at' => new \DateTimeImmutable(),
                    'created_at' => new \DateTimeImmutable(),
                ]
            );
    }

    public function nextId(): Id
    {
        return new Id((string) new UuidV7());
    }
}

<?php

namespace Modules\Tags\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Repositories\Contracts\TagRepositoryInterface;
use Modules\Tags\ValueObjects\TagTitle;
use Symfony\Component\Uid\UuidV7;

class TagRepository implements TagRepositoryInterface
{
    private CONST TABLE_NAME = 'tags';


    public function get(Id $articleId): ?Tag
    {
        $article = DB::table(self::TABLE_NAME)->find($articleId);

        if(null === $article) {
           return null;
        }

        return new Tag(
            new Id($article->id),
            new TagTitle($article->title),
            new \DateTimeImmutable($article->created_at),
            new \DateTimeImmutable($article->updated_at)
        );

    }
    public function save(Tag $article): string
    {
        if ($article->id === null) {
            $id = new UuidV7();
            DB::table(self::TABLE_NAME)->insert([
                'id' =>  $id,
                'title' => $article->title?->getValue(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $id->toString();
        } else {
            DB::table(self::TABLE_NAME)->where('id', $article->id->getValue())->update([
                'title' => $article->title?->getValue(),
                'updated_at' => now(),
            ]);
            return $article->id->getValue();
        }
    }
    public function delete(Id $articleId): void
    {
        DB::table(self::TABLE_NAME)->delete($articleId);
    }

    public function getAll(): LengthAwarePaginator
    {
        $paginator = DB::table(self::TABLE_NAME)->paginate(10);

        $collection = new Collection();

        foreach ($paginator->items() as $article) {

            $articleEntity = new Tag(
                new Id($article->id),
                new TagTitle($article->title),
                new \DateTimeImmutable($article->created_at),
                new \DateTimeImmutable($article->updated_at),
            );
            $collection->push($articleEntity);
        }

        $paginator->setCollection($collection);

        return $paginator;
    }

    public function getAllList(): Collection
    {
        $collection = new Collection();

        $tags = DB::table('tags')->orderBy('title')->get();

        foreach ($tags as $tag) {
            $collection->push(new Tag(
                new Id($tag->id),
                new TagTitle($tag->title),
                new \DateTimeImmutable($tag->created_at),
                new \DateTimeImmutable($tag->updated_at),
            ));
        }

        return $collection;
    }
}

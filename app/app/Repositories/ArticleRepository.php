<?php

namespace App\Repositories;


use App\Entity\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\ValueObjects\ArticleId;
use App\ValueObjects\ArticleText;
use App\ValueObjects\ArticleTitle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\UuidV7;

class ArticleRepository implements ArticleRepositoryInterface
{
    private $db;
    private CONST TABLE_NAME = 'articles';
    public function __construct()
    {
        $this->db = DB::table(self::TABLE_NAME);
    }

    public function get(ArticleId $articleId): ?Article
    {
        $article = $this->db->find($articleId);

        if(null === $article) {
           return null;
        }

        return new Article(
            new ArticleId($article->id),
            new ArticleTitle($article->title),
            new ArticleText($article->text),
            new \DateTimeImmutable($article->created_at),
            new \DateTimeImmutable($article->updated_at)
        );

    }
    public function save(Article $article): void
    {

        if ($article->id === null) {
            $this->db->insert([
                'id' =>  new UuidV7(),
                'title' => $article->title?->getValue(),
                'text'  => $article->text?->getValue(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->db->where('id', $article->id->getValue())->update([
                'title' => $article->title?->getValue(),
                'text'  => $article->text?->getValue(),
                'updated_at' => now(),
            ]);
        }

//        $this->db->updateOrInsert(
//            ['id' => $article->id?->getValue()],
//            [
//                'title' => $article->title?->getValue(),
//                'text'  => $article->text?->getValue(),
//            ]
//        );
    }
    public function delete(ArticleId $articleId): void
    {
        $this->db->delete($articleId);
    }

    public function getAll(): Collection
    {
        $articles = $this->db->get();

        $collection = new Collection();
        foreach ($articles as $article) {

            $articleEntity = new Article(
                new ArticleId($article->id),
                new ArticleTitle($article->title),
                new ArticleText($article->text),
                new \DateTimeImmutable($article->created_at),
                new \DateTimeImmutable($article->updated_at),
            );
            $collection->push($articleEntity);
        }

        return $collection;
    }
}

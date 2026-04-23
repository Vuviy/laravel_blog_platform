<?php
declare(strict_types=1);

namespace Modules\Article\Tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Repositories\ArticleTaggableRepository;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;
use Modules\Tags\DTO\TaggedEntityDTO;
use Tests\TestCase;

class ArticleTaggableRepositoryTest extends TestCase
{
    private ArticleRepositoryInterface $articleRepository;
    private ArticleTaggableRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->repository = new ArticleTaggableRepository($this->articleRepository);
    }

    private function makeTranslation(string $locale = 'uk', string $title = 'Тестова стаття', string $text = 'Текст статті'): ArticleTranslation
    {
        return new ArticleTranslation(
            articleId: new Id('article-uuid'),
            locale: $locale,
            title: new ArticleTitle($title),
            text: new ArticleText($text),
        );
    }

    private function makeArticle(string $id = 'article-uuid', array $translations = []): Article
    {
        return new Article(
            id: new Id($id),
            slug: 'test-article',
            status: true,
            created_at: new \DateTimeImmutable('2024-01-01'),
            updated_at: new \DateTimeImmutable('2024-01-01'),
            translations: $translations ?: [$this->makeTranslation('uk')],
        );
    }

    public function testGetEntityTypeReturnsCorrectType(): void
    {
        $this->assertEquals(
            'Modules\Article\Entities\Article',
            $this->repository->getEntityType()
        );
    }

    public function testGetByIdsReturnsDtoArray(): void
    {
        $article = $this->makeArticle();

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(TaggedEntityDTO::class, $result[0]);
    }

    public function testGetByIdsReturnsEmptyArrayWhenNoArticles(): void
    {
        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection());

        $result = $this->repository->getByIds([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetByIdsMapsCorrectId(): void
    {
        $article = $this->makeArticle('article-uuid');

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertEquals('article-uuid', $result[0]->id);
    }

    public function testGetByIdsMapsCorrectType(): void
    {
        $article = $this->makeArticle();

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertEquals('article', $result[0]->type);
    }

    public function testGetByIdsMapsCorrectTitle(): void
    {
        $article = $this->makeArticle(translations: [
            $this->makeTranslation('uk', 'Моя стаття'),
        ]);

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertEquals('Моя стаття', $result[0]->title);
    }

    public function testGetByIdsTruncatesTextTo100Chars(): void
    {
        $longText = str_repeat('а', 200);
        $article = $this->makeArticle(translations: [
            $this->makeTranslation('uk', 'Заголовок', $longText),
        ]);

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertEquals(100, mb_strlen($result[0]->text));
    }

    public function testGetByIdsMapsCorrectCreatedAt(): void
    {
        $article = $this->makeArticle();

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertEquals(
            new \DateTimeImmutable('2024-01-01'),
            $result[0]->createdAt
        );
    }

    public function testGetByIdsMapsCorrectUrl(): void
    {
        $article = $this->makeArticle();

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertStringContainsString('test-article', $result[0]->url);
        $this->assertStringContainsString('uk', $result[0]->url);
    }

    public function testGetByIdsUsesFallbackLocale(): void
    {
        app()->setLocale('en');

        $article = $this->makeArticle(translations: [
            $this->makeTranslation('uk', 'Українська стаття'),
        ]);

        $this->articleRepository
            ->method('getByIds')
            ->willReturn(new Collection([$article]));

        $result = $this->repository->getByIds(['article-uuid']);

        $this->assertEquals('Українська стаття', $result[0]->title);
    }

    public function testGetByIdsPassesIdsToRepository(): void
    {
        $ids = ['uuid-1', 'uuid-2'];

        $this->articleRepository
            ->expects($this->once())
            ->method('getByIds')
            ->with($ids)
            ->willReturn(new Collection());

        $this->repository->getByIds($ids);
    }

    public function testGetByIdsMapsMultipleArticles(): void
    {
        $articles = new Collection([
            $this->makeArticle('uuid-1'),
            $this->makeArticle('uuid-2'),
        ]);

        $this->articleRepository
            ->method('getByIds')
            ->willReturn($articles);

        $result = $this->repository->getByIds(['uuid-1', 'uuid-2']);

        $this->assertCount(2, $result);
    }
}

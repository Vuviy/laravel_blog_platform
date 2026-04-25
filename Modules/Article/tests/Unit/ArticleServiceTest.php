<?php
declare(strict_types=1);

namespace Modules\Article\Tests\Unit;

use App\ValueObjects\Id;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Modules\Article\Services\ArticleService;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    private ArticleRepositoryInterface $repository;
    private ArticleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(ArticleRepositoryInterface::class);
        $this->service = new ArticleService($this->repository);
    }

    private function makeArticle(string $id = 'article-uuid'): Article
    {
        return new Article(
            id: new Id($id),
            status: true,
            slug: 'test-article',
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    private function makeTranslationData(array $override = []): array
    {
        return array_merge([
            'title'           => 'Тестова стаття',
            'text'            => 'Текст статті',
            'seo_title'       => 'SEO заголовок',
            'seo_description' => 'SEO опис',
            'seo_keywords'    => 'ключові слова',
            'seo_og_image'    => null,
        ], $override);
    }

    private function makeCreateData(array $override = []): array
    {
        return array_merge([
            'status' => true,
            'slug'   => 'test-article',
            'translations' => [
                'uk' => $this->makeTranslationData(),
                'en' => $this->makeTranslationData(['title' => 'Test article EN']),
            ],
        ], $override);
    }

    public function testGetArticleByIdReturnsArticle(): void
    {
        $id = new Id('article-uuid');
        $article = $this->makeArticle();

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($article);

        $result = $this->service->getArticleById($id);

        $this->assertSame($article, $result);
    }

    public function testGetArticleByIdReturnsNullWhenNotFound(): void
    {
        $this->repository
            ->method('get')
            ->willReturn(null);

        $result = $this->service->getArticleById(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testGetArticleBySlugReturnsArticle(): void
    {
        $article = $this->makeArticle();

        $this->repository
            ->expects($this->once())
            ->method('getBySlug')
            ->with('test-article')
            ->willReturn($article);

        $result = $this->service->getArticleBySlug('test-article');

        $this->assertSame($article, $result);
    }

    public function testGetArticleBySlugReturnsNullWhenNotFound(): void
    {
        $this->repository
            ->method('getBySlug')
            ->willReturn(null);

        $result = $this->service->getArticleBySlug('non-existent');

        $this->assertNull($result);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $filter = new ArticleFilter();
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->with($filter)
            ->willReturn($paginator);

        $result = $this->service->getAll($filter);

        $this->assertSame($paginator, $result);
    }

    public function testCreateSavesArticleAndReturnsId(): void
    {
        $this->repository
            ->method('save')
            ->willReturn('new-article-uuid');

        $this->repository
            ->method('saveTranslation');

        $result = $this->service->create($this->makeCreateData());

        $this->assertEquals('new-article-uuid', $result);
    }

    public function testCreateSavesArticleWithCorrectSlugAndStatus(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                fn(Article $a) => $a->slug === 'test-article' && $a->status === true
            ))
            ->willReturn('new-article-uuid');

        $this->repository->method('saveTranslation');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateSavesTranslationsForEachLocale(): void
    {
        $this->repository
            ->method('save')
            ->willReturn('new-article-uuid');

        $this->repository
            ->expects($this->exactly(2))
            ->method('saveTranslation');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateSyncsTagsWhenProvided(): void
    {
        $tags = ['tag-uuid-1', 'tag-uuid-2'];

        $this->repository
            ->method('save')
            ->willReturn('new-article-uuid');

        $this->repository->method('saveTranslation');

        $this->repository
            ->expects($this->once())
            ->method('syncTags')
            ->with(
                $this->isInstanceOf(Id::class),
                $tags
            );

        $this->service->create(array_merge($this->makeCreateData(), ['tags' => $tags]));
    }

    public function testCreateDoesNotSyncTagsWhenNotProvided(): void
    {
        $this->repository
            ->method('save')
            ->willReturn('new-article-uuid');

        $this->repository->method('saveTranslation');

        $this->repository
            ->expects($this->never())
            ->method('syncTags');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateStoresUploadedSeoOgImage(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('og.jpg');

        $this->repository
            ->method('save')
            ->willReturn('new-article-uuid');

        $this->repository
            ->expects($this->exactly(2))
            ->method('saveTranslation')
            ->with($this->callback(function (ArticleTranslation $translation) {
                if ($translation->locale === 'uk') {
                    return $translation->seoOgImage !== null;
                }
                return true;
            }));

        $data = $this->makeCreateData();
        $data['translations']['uk']['seo_og_image'] = $file;

        $this->service->create($data);
    }

    public function testUpdateSavesArticleWithNewData(): void
    {
        $id = new Id('article-uuid');
        $article = $this->makeArticle();

        $this->repository
            ->method('get')
            ->willReturn($article);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Article $a) => $a->slug === 'updated-slug'));

        $this->repository
            ->method('getTranslationsForArticles')
            ->willReturn([]);

        $this->repository->method('saveTranslation');

        $this->service->update($id, $this->makeCreateData(['slug' => 'updated-slug']));
    }

    public function testUpdateKeepsOldSlugWhenNotProvided(): void
    {
        $id = new Id('article-uuid');
        $article = $this->makeArticle();

        $this->repository
            ->method('get')
            ->willReturn($article);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Article $a) => $a->slug === 'test-article'));

        $this->repository
            ->method('getTranslationsForArticles')
            ->willReturn([]);

        $this->repository->method('saveTranslation');

        $data = $this->makeCreateData();
        unset($data['slug']);

        $this->service->update($id, $data);
    }

    public function testUpdateKeepsExistingSeoOgImageWhenNotUploaded(): void
    {
        $id = new Id('article-uuid');
        $article = $this->makeArticle();

        $existingTranslation = new ArticleTranslation(
            articleId: $id,
            locale: 'uk',
            title: new ArticleTitle('Стара стаття'),
            text: new ArticleText('Старий текст'),
            seoOgImage: 'articles/seo/uk/existing.jpg',
        );

        $this->repository
            ->method('get')
            ->willReturn($article);

        $this->repository->method('save');

        $this->repository
            ->method('getTranslationsForArticles')
            ->willReturn(['uk' => $existingTranslation, 'en' => null]);

        $this->repository
            ->expects($this->exactly(2))
            ->method('saveTranslation')
            ->with($this->callback(function (ArticleTranslation $translation) {
                if ($translation->locale === 'uk') {
                    return $translation->seoOgImage === 'articles/seo/uk/existing.jpg';
                }
                return true;
            }));

        $this->service->update($id, $this->makeCreateData());
    }

    public function testUpdateDeletesOldSeoOgImageWhenNewUploaded(): void
    {
        Storage::fake('public');

        $id = new Id('article-uuid');
        $article = $this->makeArticle();
        $file = UploadedFile::fake()->image('new-og.jpg');

        $existingTranslation = new ArticleTranslation(
            articleId: $id,
            locale: 'uk',
            title: new ArticleTitle('Стара стаття'),
            text: new ArticleText('Старий текст'),
            seoOgImage: 'articles/seo/uk/old.jpg',
        );

        Storage::disk('public')->put('articles/seo/uk/old.jpg', 'content');

        $this->repository
            ->method('get')
            ->willReturn($article);

        $this->repository->method('save');

        $this->repository
            ->method('getTranslationsForArticles')
            ->willReturn(['uk' => $existingTranslation, 'en' => null]);

        $this->repository->method('saveTranslation');

        $data = $this->makeCreateData();
        $data['translations']['uk']['seo_og_image'] = $file;

        $this->service->update($id, $data);

        Storage::disk('public')->assertMissing('articles/seo/uk/old.jpg');
    }

    public function testUpdateSyncsTagsWhenProvided(): void
    {
        $id = new Id('article-uuid');
        $tags = ['tag-uuid-1'];

        $this->repository
            ->method('get')
            ->willReturn($this->makeArticle());

        $this->repository->method('save');

        $this->repository
            ->method('getTranslationsForArticles')
            ->willReturn([]);

        $this->repository->method('saveTranslation');

        $this->repository
            ->expects($this->once())
            ->method('syncTags')
            ->with($this->isInstanceOf(Id::class), $tags);

        $this->service->update($id, array_merge($this->makeCreateData(), ['tags' => $tags]));
    }

    public function testUpdateDoesNotSyncTagsWhenNotProvided(): void
    {
        $id = new Id('article-uuid');

        $this->repository
            ->method('get')
            ->willReturn($this->makeArticle());

        $this->repository->method('save');

        $this->repository
            ->method('getTranslationsForArticles')
            ->willReturn([]);

        $this->repository->method('saveTranslation');

        $this->repository
            ->expects($this->never())
            ->method('syncTags');

        $this->service->update($id, $this->makeCreateData());
    }

    public function testDeleteCallsRepository(): void
    {
        $id = new Id('article-uuid');

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->service->delete($id);
    }

    public function testSyncTagsCallsRepository(): void
    {
        $id = new Id('article-uuid');
        $tags = ['tag-uuid-1', 'tag-uuid-2'];

        $this->repository
            ->expects($this->once())
            ->method('syncTags')
            ->with($id, $tags);

        $this->service->syncTags($id, $tags);
    }
}

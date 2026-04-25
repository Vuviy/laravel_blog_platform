<?php
declare(strict_types=1);

namespace Modules\Article\Tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Article\Entities\Article;
use Modules\Article\Entities\ArticleTranslation;
use Modules\Article\Services\ArticleService;
use Modules\Article\ValueObjects\ArticleText;
use Modules\Article\ValueObjects\ArticleTitle;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Repositories\TagRepository;
use Modules\Tags\ValueObjects\TagTitle;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    private ArticleService $articleService;
    private TagRepository $tagRepository;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->articleService = $this->createMock(ArticleService::class);
        $this->tagRepository = $this->createMock(TagRepository::class);

        $this->app->instance(ArticleService::class, $this->articleService);
        $this->app->instance(TagRepository::class, $this->tagRepository);

        $userService = $this->createMock(UserService::class);
        $userService->method('getById')->willReturn($this->makeAdminUser());
        $this->app->instance(UserService::class, $userService);

        $adminUser = $this->makeAdminUser();
        session([
            'user_id' => 'admin-uuid',
            'user'    => $adminUser,
        ]);
    }

    private function makeAdminUser(): User
    {
        return new User(
            id: new Id('admin-uuid'),
            username: new Username('admin'),
            email: new Email('admin@example.com'),
            password: Password::fromPlain('secret123'),
            roles: [
                new Role(
                    id: new Id('role-uuid'),
                    name: new RoleName('admin'),
                    createdAt: new \DateTimeImmutable(),
                    updatedAt: new \DateTimeImmutable(),
                )
            ],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    private function makeTranslation(string $locale = 'uk'): ArticleTranslation
    {
        return new ArticleTranslation(
            id: new Id((string) \Symfony\Component\Uid\UuidV7::generate()),
            articleId: new Id('article-uuid'),
            locale: $locale,
            title: new ArticleTitle('Тестова стаття'),
            text: new ArticleText('Текст статті'),
        );
    }

    private function makeArticle(string $id = 'article-uuid', array $tags = []): Article
    {
        return new Article(
            id: new Id($id),
            slug: 'test-article',
            status: true,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
            tags: $tags,
            translations: [$this->makeTranslation('uk')],
        );
    }

    private function makeTag(string $id = 'tag-uuid'): Tag
    {
        return new Tag(
            id: new Id($id),
            title: new TagTitle('laravel'),
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    private function validCreateData(array $override = []): array
    {
        return array_merge([
            'slug'   => 'test-article',
            'status' => true,
            'translations' => [
                'uk' => [
                    'title' => 'Тестова стаття',
                    'text'  => 'Текст статті',
                ],
                'en' => [
                    'title' => 'Test article',
                    'text'  => 'Article text',
                ],
            ],
        ], $override);
    }

    public function testIndexReturns200(): void
    {
        $this->articleService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.articles.index'));

        $response->assertStatus(200);
    }

    public function testIndexPassesArticlesFilterTitleToView(): void
    {
        $this->articleService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.articles.index'));

        $response->assertViewHas('articles');
        $response->assertViewHas('filter');
        $response->assertViewHas('title', 'Articles');
    }

    public function testCreateReturns200(): void
    {
        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.articles.create'));

        $response->assertStatus(200);
    }

    public function testCreatePassesTagsToView(): void
    {
        $tags = new Collection([$this->makeTag()]);

        $this->tagRepository
            ->method('getAllList')
            ->willReturn($tags);

        $response = $this->get(route('admin.articles.create'));

        $response->assertViewHas('tags', fn($t) => $t->count() === 1);
    }

    public function testStoreCreatesArticleAndRedirects(): void
    {
        $this->articleService
            ->expects($this->once())
            ->method('create')
            ->willReturn('new-article-uuid');

        $response = $this->post(route('admin.articles.store'), $this->validCreateData());

        $response->assertRedirect(route('admin.articles.edit', ['article' => 'new-article-uuid']));
        $response->assertSessionHas('success');
    }

    public function testStoreValidationFailsWithoutSlug(): void
    {
        $data = $this->validCreateData();
        unset($data['slug']);

        $response = $this->post(route('admin.articles.store'), $data);

        $response->assertSessionHasErrors(['slug']);
    }

    public function testStoreValidationFailsWithoutTitle(): void
    {
        $data = $this->validCreateData();
        unset($data['translations']['uk']['title']);

        $response = $this->post(route('admin.articles.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.title']);
    }

    public function testStoreValidationFailsWithoutText(): void
    {
        $data = $this->validCreateData();
        unset($data['translations']['uk']['text']);

        $response = $this->post(route('admin.articles.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.text']);
    }

    public function testStoreValidationFailsWhenSeoTitleTooLong(): void
    {
        $data = $this->validCreateData();
        $data['translations']['uk']['seo_title'] = str_repeat('a', 61);

        $response = $this->post(route('admin.articles.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.seo_title']);
    }

    public function testStoreValidationFailsWhenSeoDescriptionTooLong(): void
    {
        $data = $this->validCreateData();
        $data['translations']['uk']['seo_description'] = str_repeat('a', 161);

        $response = $this->post(route('admin.articles.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.seo_description']);
    }

    public function testEditReturns200(): void
    {
        $this->articleService
            ->method('getArticleById')
            ->willReturn($this->makeArticle());

        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.articles.edit', ['article' => 'article-uuid']));

        $response->assertStatus(200);
    }

    public function testEditPassesArticleTagsToView(): void
    {
        $this->articleService
            ->method('getArticleById')
            ->willReturn($this->makeArticle());

        $tags = new Collection([$this->makeTag()]);

        $this->tagRepository
            ->method('getAllList')
            ->willReturn($tags);

        $response = $this->get(route('admin.articles.edit', ['article' => 'article-uuid']));

        $response->assertViewHas('article', fn($a) => $a->id->getValue() === 'article-uuid');
        $response->assertViewHas('tags', fn($t) => $t->count() === 1);
    }

    public function testEditPassesCorrectSelectedTagIds(): void
    {
        $tagId = 'tag-uuid';
        $tag = $this->makeTag($tagId);
        $article = $this->makeArticle(tags: [$tag]);

        $this->articleService
            ->method('getArticleById')
            ->willReturn($article);

        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection([$tag]));

        $response = $this->get(route('admin.articles.edit', ['article' => 'article-uuid']));

        $response->assertViewHas('selectedTagIds', [$tagId]);
    }

    public function testEditPassesEmptySelectedTagIdsWhenNoTags(): void
    {
        $this->articleService
            ->method('getArticleById')
            ->willReturn($this->makeArticle(tags: []));

        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.articles.edit', ['article' => 'article-uuid']));

        $response->assertViewHas('selectedTagIds', []);
    }

    public function testUpdateCallsServiceAndRedirects(): void
    {
        $this->articleService
            ->expects($this->once())
            ->method('update');

        $response = $this->put(
            route('admin.articles.update', ['article' => 'article-uuid']),
            $this->validCreateData()
        );

        $response->assertRedirect(route('admin.articles.edit', ['article' => 'article-uuid']));
        $response->assertSessionHas('success');
    }

    public function testUpdateValidationFailsWithoutSlug(): void
    {
        $data = $this->validCreateData();
        unset($data['slug']);

        $response = $this->put(
            route('admin.articles.update', ['article' => 'article-uuid']),
            $data
        );

        $response->assertSessionHasErrors(['slug']);
    }

    public function testDestroyCallsServiceAndRedirects(): void
    {
        $this->articleService
            ->expects($this->once())
            ->method('delete');

        $response = $this->delete(route('admin.articles.destroy', ['article' => 'article-uuid']));

        $response->assertRedirect(route('admin.articles.index'));
        $response->assertSessionHas('success');
    }

    public function testDestroyPassesCorrectIdToService(): void
    {
        $this->articleService
            ->expects($this->once())
            ->method('delete')
            ->with($this->callback(fn(Id $id) => $id->getValue() === 'article-uuid'));

        $this->delete(route('admin.articles.destroy', ['article' => 'article-uuid']));
    }
}

<?php
declare(strict_types=1);

namespace Modules\News\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsTranslation;
use Modules\News\Services\NewsService;
use Modules\News\ValueObjects\NewsText;
use Modules\News\ValueObjects\NewsTitle;
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

class AdminNewsControllerTest extends TestCase
{
    use RefreshDatabase;

    private NewsService $newsService;
    private TagRepository $tagRepository;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->newsService = $this->createMock(NewsService::class);
        $this->tagRepository = $this->createMock(TagRepository::class);

        $this->app->instance(NewsService::class, $this->newsService);
        $this->app->instance(TagRepository::class, $this->tagRepository);

        $userService = $this->createMock(UserService::class);
        $userService->method('getById')->willReturn($this->makeAdminUser());
        $this->app->instance(UserService::class, $userService);

        session(['user_id' => 'admin-uuid']);
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

    private function makeTranslation(string $locale = 'uk'): NewsTranslation
    {
        return new NewsTranslation(
            id: new Id((string) \Symfony\Component\Uid\UuidV7::generate()),
            newId: new Id('news-uuid'),
            locale: $locale,
            title: new NewsTitle('Тестова новина'),
            text: new NewsText('Текст новини'),
        );
    }

    private function makeNews(string $id = 'news-uuid', array $tags = []): News
    {
        return new News(
            id: new Id($id),
            slug: 'test-news',
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
            'slug'   => 'test-news',
            'status' => true,
            'translations' => [
                'uk' => [
                    'title' => 'Тестова новина',
                    'text'  => 'Текст новини',
                ],
                'en' => [
                    'title' => 'Test news',
                    'text'  => 'News text',
                ],
            ],
        ], $override);
    }

    public function testIndexReturns200(): void
    {
        $this->newsService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.news.index'));

        $response->assertStatus(200);
    }

    public function testIndexPassesNewsFilterTitleToView(): void
    {
        $this->newsService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.news.index'));

        $response->assertViewHas('news');
        $response->assertViewHas('filter');
        $response->assertViewHas('title');
    }

    public function testCreateReturns200(): void
    {
        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.news.create'));

        $response->assertStatus(200);
    }

    public function testCreatePassesTagsToView(): void
    {
        $tags = new Collection([$this->makeTag()]);

        $this->tagRepository
            ->method('getAllList')
            ->willReturn($tags);

        $response = $this->get(route('admin.news.create'));

        $response->assertViewHas('tags', fn($t) => $t->count() === 1);
    }

    public function testStoreCreatesNewsAndRedirects(): void
    {
        $this->newsService
            ->expects($this->once())
            ->method('create')
            ->willReturn('new-news-uuid');

        $response = $this->post(route('admin.news.store'), $this->validCreateData());

        $response->assertRedirect(route('admin.news.edit', ['news' => 'new-news-uuid']));
        $response->assertSessionHas('success');
    }

    public function testStoreValidationFailsWithoutSlug(): void
    {
        $data = $this->validCreateData();
        unset($data['slug']);

        $response = $this->post(route('admin.news.store'), $data);

        $response->assertSessionHasErrors(['slug']);
    }

    public function testStoreValidationFailsWithoutTitle(): void
    {
        $data = $this->validCreateData();
        unset($data['translations']['uk']['title']);

        $response = $this->post(route('admin.news.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.title']);
    }

    public function testStoreValidationFailsWithoutText(): void
    {
        $data = $this->validCreateData();
        unset($data['translations']['uk']['text']);

        $response = $this->post(route('admin.news.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.text']);
    }

    public function testStoreValidationFailsWhenSeoTitleTooLong(): void
    {
        $data = $this->validCreateData();
        $data['translations']['uk']['seo_title'] = str_repeat('a', 61);

        $response = $this->post(route('admin.news.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.seo_title']);
    }

    public function testStoreValidationFailsWhenSeoDescriptionTooLong(): void
    {
        $data = $this->validCreateData();
        $data['translations']['uk']['seo_description'] = str_repeat('a', 161);

        $response = $this->post(route('admin.news.store'), $data);

        $response->assertSessionHasErrors(['translations.uk.seo_description']);
    }

    public function testEditReturns200(): void
    {
        $this->newsService
            ->method('getNewById')
            ->willReturn($this->makeNews());

        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.news.edit', ['news' => 'news-uuid']));

        $response->assertStatus(200);
    }

    public function testEditPassesNewsTagsToView(): void
    {
        $this->newsService
            ->method('getNewById')
            ->willReturn($this->makeNews());

        $tags = new Collection([$this->makeTag()]);

        $this->tagRepository
            ->method('getAllList')
            ->willReturn($tags);

        $response = $this->get(route('admin.news.edit', ['news' => 'news-uuid']));

        $response->assertViewHas('news', fn($n) => $n->id->getValue() === 'news-uuid');
        $response->assertViewHas('tags', fn($t) => $t->count() === 1);
    }

    public function testEditPassesCorrectSelectedTagIds(): void
    {
        $tagId = 'tag-uuid';
        $tag = $this->makeTag($tagId);
        $news = $this->makeNews(tags: [$tag]);

        $this->newsService
            ->method('getNewById')
            ->willReturn($news);

        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection([$tag]));

        $response = $this->get(route('admin.news.edit', ['news' => 'news-uuid']));

        $response->assertViewHas('selectedTagIds', [$tagId]);
    }

    public function testEditPassesEmptySelectedTagIdsWhenNoTags(): void
    {
        $this->newsService
            ->method('getNewById')
            ->willReturn($this->makeNews(tags: []));

        $this->tagRepository
            ->method('getAllList')
            ->willReturn(new Collection());

        $response = $this->get(route('admin.news.edit', ['news' => 'news-uuid']));

        $response->assertViewHas('selectedTagIds', []);
    }


    public function testUpdateCallsServiceAndRedirects(): void
    {
        $this->newsService
            ->expects($this->once())
            ->method('update');

        $response = $this->put(
            route('admin.news.update', ['news' => 'news-uuid']),
            $this->validCreateData()
        );

        $response->assertRedirect(route('admin.news.edit', ['news' => 'news-uuid']));
        $response->assertSessionHas('success');
    }

    public function testUpdateValidationFailsWithoutSlug(): void
    {
        $data = $this->validCreateData();
        unset($data['slug']);

        $response = $this->put(
            route('admin.news.update', ['news' => 'news-uuid']),
            $data
        );

        $response->assertSessionHasErrors(['slug']);
    }

    public function testDestroyCallsServiceAndRedirects(): void
    {
        $this->newsService
            ->expects($this->once())
            ->method('delete');

        $response = $this->delete(route('admin.news.destroy', ['news' => 'news-uuid']));

        $response->assertRedirect(route('admin.news.index'));
        $response->assertSessionHas('success');
    }

}

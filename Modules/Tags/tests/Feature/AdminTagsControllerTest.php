<?php

declare(strict_types=1);

namespace Modules\Tags\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Tags\Entities\Tag;
use Modules\Tags\Services\TagService;
use Modules\Tags\ValueObjects\TagTitle;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminTagsControllerTest extends TestCase
{
    use RefreshDatabase;

    private TagService $tagService;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->tagService = $this->createMock(TagService::class);
        $this->app->instance(TagService::class, $this->tagService);

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

    private function makeTag(string $id = 'tag-uuid', string $title = 'laravel'): Tag
    {
        return new Tag(
            id: new Id($id),
            title: new TagTitle($title),
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }

    public function testIndexReturns200(): void
    {
        $this->tagService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.tags.index'));

        $response->assertStatus(200);
    }

    public function testIndexPassesTagsAndTitleToView(): void
    {
        $paginator = new LengthAwarePaginator([$this->makeTag()], 1, 10);

        $this->tagService
            ->method('getAll')
            ->willReturn($paginator);

        $response = $this->get(route('admin.tags.index'));

        $response->assertViewHas('tags');
        $response->assertViewHas('title', 'Tags');
    }

    public function testCreateReturns200(): void
    {
        $response = $this->get(route('admin.tags.create'));

        $response->assertStatus(200);
    }

    public function testCreatePassesTitleToView(): void
    {
        $response = $this->get(route('admin.tags.create'));

        $response->assertViewHas('title', 'Create a new article');
    }

    public function testStoreCreatesTagAndRedirects(): void
    {
        $this->tagService
            ->expects($this->once())
            ->method('create')
            ->willReturn('new-tag-uuid');

        $response = $this->post(route('admin.tags.store'), [
            'title' => 'laravel',
        ]);

        $response->assertRedirect(route('admin.tags.edit', ['tag' => 'new-tag-uuid']));
        $response->assertSessionHas('success');
    }

    public function testEditReturns200(): void
    {
        $this->tagService
            ->method('getById')
            ->willReturn($this->makeTag());

        $response = $this->get(route('admin.tags.edit', ['tag' => 'tag-uuid']));

        $response->assertStatus(200);
    }

    public function testEditPassesTagAndTitleToView(): void
    {
        $tag = $this->makeTag();

        $this->tagService
            ->method('getById')
            ->willReturn($tag);

        $response = $this->get(route('admin.tags.edit', ['tag' => 'tag-uuid']));

        $response->assertViewHas('tag', fn($t) => $t->id->getValue() === 'tag-uuid');
        $response->assertViewHas('title', 'Edit a new article');
    }

    public function testUpdateCallsServiceAndRedirects(): void
    {
        $this->tagService
            ->expects($this->once())
            ->method('update');

        $response = $this->put(route('admin.tags.update', ['tag' => 'tag-uuid']), [
            'title' => 'updated-tag',
        ]);

        $response->assertRedirect(route('admin.tags.edit', ['tag' => 'tag-uuid']));
        $response->assertSessionHas('success');
    }

    public function testDestroyCallsServiceAndRedirects(): void
    {
        $this->tagService
            ->expects($this->once())
            ->method('delete');

        $response = $this->delete(route('admin.tags.destroy', ['tag' => 'tag-uuid']));

        $response->assertRedirect(route('admin.tags.index'));
        $response->assertSessionHas('success');
    }
}

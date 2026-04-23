<?php
declare(strict_types=1);

namespace Modules\Comments\tests\Feature;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Comments\Entities\Comment;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\Services\CommentService;
use Modules\Comments\ValueObjects\CommentText;
use Modules\Users\Entities\Role;
use Modules\Users\Entities\User;
use Modules\Users\Enums\Permission;
use Modules\Users\Services\UserService;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;
use Tests\TestCase;

class AdminCommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private CommentService $commentService;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('uk');

        $this->commentService = $this->createMock(CommentService::class);
        $this->app->instance(CommentService::class, $this->commentService);

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
                    permissions: [Permission::COMMENT_DELETE],
                    createdAt: new \DateTimeImmutable(),
                    updatedAt: new \DateTimeImmutable(),
                )
            ],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    private function makeComment(string $id = 'comment-uuid'): Comment
    {
        return new Comment(
            id: new Id($id),
            content: new CommentText('Тестовий коментар'),
            userId: new Id('user-uuid'),
            entityId: new Id('entity-uuid'),
            parentId: null,
            lft: 1,
            rgt: 2,
            depth: 0,
            entityType: EntityType::ARTICLE,
            status: CommentStatus::PENDING,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
    }


    public function testIndexReturns200(): void
    {
        $this->commentService
            ->method('getAll')
            ->willReturn(new LengthAwarePaginator([], 0, 10));

        $response = $this->get(route('admin.comments.index'));

        $response->assertStatus(200);
    }

    public function testIndexPassesCommentsToView(): void
    {
        $paginator = new LengthAwarePaginator([$this->makeComment()], 1, 10);

        $this->commentService
            ->method('getAll')
            ->willReturn($paginator);

        $response = $this->get(route('admin.comments.index'));

        $response->assertViewHas('comments');
        $response->assertViewHas('title');
    }

    public function testCreateReturns200(): void
    {
        $response = $this->get(route('admin.comments.create'));

        $response->assertStatus(200);
    }

    public function testCreatePassesTitleToView(): void
    {
        $response = $this->get(route('admin.comments.create'));

        $response->assertViewHas('title');
    }

    public function testEditReturns200(): void
    {
        $this->commentService
            ->method('getCommentById')
            ->willReturn($this->makeComment());

        $response = $this->get(route('admin.comments.edit', ['comment' => 'comment-uuid']));

        $response->assertStatus(200);
    }

    public function testEditPassesCommentToView(): void
    {
        $comment = $this->makeComment();

        $this->commentService
            ->method('getCommentById')
            ->willReturn($comment);

        $response = $this->get(route('admin.comments.edit', ['comment' => 'comment-uuid']));

        $response->assertViewHas('comment', fn($c) => $c->id->getValue() === 'comment-uuid');
        $response->assertViewHas('title');
    }

    public function testEditCallsServiceWithCorrectId(): void
    {
        $this->commentService
            ->expects($this->once())
            ->method('getCommentById')
            ->with($this->callback(fn(Id $id) => $id->getValue() === 'comment-uuid'))
            ->willReturn($this->makeComment());

        $this->get(route('admin.comments.edit', ['comment' => 'comment-uuid']));
    }

    public function testUpdateCallsServiceAndRedirects(): void
    {
        $this->commentService
            ->expects($this->once())
            ->method('update');

        $response = $this->put(
            route('admin.comments.update', ['comment' => 'comment-uuid']),
            ['content' => 'Оновлений коментар']
        );

        $response->assertRedirect(route('admin.comments.edit', ['comment' => 'comment-uuid']));
        $response->assertSessionHas('success');
    }

    public function testUpdatePassesCorrectIdToService(): void
    {
        $this->commentService
            ->expects($this->once())
            ->method('update')
            ->with(
                $this->callback(fn(Id $id) => $id->getValue() === 'comment-uuid'),
                $this->anything()
            );

        $this->put(
            route('admin.comments.update', ['comment' => 'comment-uuid']),
            ['content' => 'Оновлений коментар']
        );
    }


    public function testDestroyCallsServiceAndRedirects(): void
    {
        $this->commentService
            ->expects($this->once())
            ->method('delete');

        $response = $this->delete(route('admin.comments.destroy', ['comment' => 'comment-uuid']));

        $response->assertRedirect(route('admin.comments.index'));
        $response->assertSessionHas('success');
    }

    public function testDestroyPassesCorrectIdToService(): void
    {
        $this->commentService
            ->expects($this->once())
            ->method('delete')
            ->with($this->callback(fn(Id $id) => $id->getValue() === 'comment-uuid'));

        $this->delete(route('admin.comments.destroy', ['comment' => 'comment-uuid']));
    }
}

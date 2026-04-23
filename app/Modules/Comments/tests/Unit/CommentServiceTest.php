<?php
declare(strict_types=1);

namespace Modules\Comments\tests\Unit;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Comments\Entities\Comment;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\Repositories\Contracts\CommentRepositoryInterface;
use Modules\Comments\Services\CommentService;

use Modules\Comments\ValueObjects\CommentText;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    private CommentRepositoryInterface $repository;
    private CommentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(CommentRepositoryInterface::class);
        $this->service = new CommentService($this->repository);
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

    private function makeCreateData(array $override = []): array
    {
        return array_merge([
            'content'     => 'Тестовий коментар',
            'user_id'     => 'user-uuid',
            'entity_id'   => 'entity-uuid',
            'entity_type' => EntityType::ARTICLE->value,
            'parent_id'   => null,
        ], $override);
    }

    public function testGetCommentByIdReturnsComment(): void
    {
        $id = new Id('comment-uuid');
        $comment = $this->makeComment();

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($comment);

        $result = $this->service->getCommentById($id);

        $this->assertSame($comment, $result);
    }

    public function testGetCommentByIdReturnsNullWhenNotFound(): void
    {
        $this->repository
            ->method('get')
            ->willReturn(null);

        $result = $this->service->getCommentById(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($paginator);

        $result = $this->service->getAll();

        $this->assertSame($paginator, $result);
    }

    public function testCreateRootCommentWhenNoParentId(): void
    {
        $this->repository
            ->method('getMaxRgt')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Comment $comment) {
                return $comment->lft === 1
                    && $comment->rgt === 2
                    && $comment->depth === 0
                    && $comment->parentId === null;
            }))
            ->willReturn('new-comment-uuid');

        $result = $this->service->create($this->makeCreateData());

        $this->assertEquals('new-comment-uuid', $result);
    }

    public function testCreateRootCommentCalculatesLftRgtFromMaxRgt(): void
    {
        $this->repository
            ->method('getMaxRgt')
            ->willReturn(4);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Comment $comment) {
                return $comment->lft === 5 && $comment->rgt === 6;
            }))
            ->willReturn('new-comment-uuid');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateRootCommentSetsLftOneAndRgtTwoWhenNoExistingComments(): void
    {
        $this->repository
            ->method('getMaxRgt')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Comment $c) => $c->lft === 1 && $c->rgt === 2))
            ->willReturn('new-comment-uuid');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateRootCommentSetsCorrectEntityType(): void
    {
        $this->repository
            ->method('getMaxRgt')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                fn(Comment $c) => $c->entityType === EntityType::ARTICLE
            ))
            ->willReturn('new-comment-uuid');

        $this->service->create($this->makeCreateData());
    }

    public function testCreateChildCommentCallsCreateChildComment(): void
    {
        $data = $this->makeCreateData(['parent_id' => 'parent-uuid']);

        $this->repository
            ->expects($this->once())
            ->method('createChildComment')
            ->with($data)
            ->willReturn('child-comment-uuid');

        $this->repository
            ->expects($this->never())
            ->method('save');

        $result = $this->service->create($data);

        $this->assertEquals('child-comment-uuid', $result);
    }

    public function testCreateDoesNotCallGetMaxRgtForChildComment(): void
    {
        $data = $this->makeCreateData(['parent_id' => 'parent-uuid']);

        $this->repository
            ->method('createChildComment')
            ->willReturn('child-uuid');

        $this->repository
            ->expects($this->never())
            ->method('getMaxRgt');

        $this->service->create($data);
    }

    public function testUpdateSavesCommentWithNewContent(): void
    {
        $id = new Id('comment-uuid');
        $comment = $this->makeComment();

        $this->repository
            ->method('get')
            ->willReturn($comment);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                fn(Comment $c) => $c->content->getValue() === 'Оновлений коментар'
            ));

        $this->service->update($id, ['content' => 'Оновлений коментар']);
    }

    public function testUpdateSavesCommentWithNewStatus(): void
    {
        $id = new Id('comment-uuid');
        $comment = $this->makeComment();

        $this->repository
            ->method('get')
            ->willReturn($comment);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                fn(Comment $c) => $c->status === CommentStatus::APPROVED
            ));

        $this->service->update($id, [
            'content' => 'Коментар',
            'status'  => CommentStatus::APPROVED->value,
        ]);
    }

    public function testUpdateKeepsOriginalId(): void
    {
        $id = new Id('comment-uuid');
        $comment = $this->makeComment('comment-uuid');

        $this->repository
            ->method('get')
            ->willReturn($comment);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                fn(Comment $c) => $c->id->getValue() === 'comment-uuid'
            ));

        $this->service->update($id, ['content' => 'Оновлений коментар']);
    }

    public function testUpdateKeepsOriginalLftRgtDepth(): void
    {
        $id = new Id('comment-uuid');
        $comment = $this->makeComment();

        $this->repository
            ->method('get')
            ->willReturn($comment);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                fn(Comment $c) => $c->lft === 1 && $c->rgt === 2 && $c->depth === 0
            ));

        $this->service->update($id, ['content' => 'Оновлений коментар']);
    }

    public function testDeleteCallsRepository(): void
    {
        $id = new Id('comment-uuid');

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->service->delete($id);
    }
}

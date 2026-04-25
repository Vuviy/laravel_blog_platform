<?php
declare(strict_types=1);

namespace Modules\Comments\tests\Integration;

use App\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Comments\Entities\Comment;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\Repositories\CommentRepository;
use Modules\Comments\ValueObjects\CommentText;
use Symfony\Component\Uid\UuidV7;
use Tests\TestCase;

class CommentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CommentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(CommentRepository::class);
    }

    private function insertComment(
        string $id,
        int $lft = 1,
        int $rgt = 2,
        int $depth = 0,
        ?string $parentId = null,
        string $entityId = 'entity-uuid',
        string $status = 'pending',
    ): void {
        DB::table('comments')->insert([
            'id'          => $id,
            'user_id'     => 'user-uuid',
            'entity_id'   => $entityId,
            'entity_type' => EntityType::ARTICLE->value,
            'parent_id'   => $parentId,
            'lft'         => $lft,
            'rgt'         => $rgt,
            'depth'       => $depth,
            'content'     => 'Тестовий коментар',
            'status'      => $status,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function makeComment(?Id $id = null): Comment
    {
        return new Comment(
            id: $id,
            content: new CommentText('Тестовий коментар'),
            userId: new Id('user-uuid'),
            entityId: new Id('entity-uuid'),
            parentId: null,
            lft: 1,
            rgt: 2,
            depth: 0,
            entityType: EntityType::ARTICLE,
            status: CommentStatus::PENDING,
        );
    }

    public function testGetReturnsComment(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertComment($uuid);

        $result = $this->repository->get(new Id($uuid));

        $this->assertInstanceOf(Comment::class, $result);
        $this->assertEquals($uuid, $result->id->getValue());
    }

    public function testGetReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->get(new Id('non-existent'));

        $this->assertNull($result);
    }

    public function testGetReturnsCorrectStatus(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertComment($uuid, status: CommentStatus::APPROVED->value);

        $result = $this->repository->get(new Id($uuid));

        $this->assertEquals(CommentStatus::APPROVED, $result->status);
    }

    public function testSaveCreatesCommentAndReturnsId(): void
    {
        $comment = $this->makeComment();
        $id = $this->repository->save($comment);

        $this->assertNotEmpty($id);
        $this->assertDatabaseHas('comments', ['id' => $id]);
    }

    public function testSaveStoresCorrectContent(): void
    {
        $comment = $this->makeComment();
        $id = $this->repository->save($comment);

        $this->assertDatabaseHas('comments', [
            'id'      => $id,
            'content' => 'Тестовий коментар',
        ]);
    }

    public function testSaveStoresCorrectLftRgtDepth(): void
    {
        $comment = $this->makeComment();
        $id = $this->repository->save($comment);

        $this->assertDatabaseHas('comments', [
            'id'    => $id,
            'lft'   => 1,
            'rgt'   => 2,
            'depth' => 0,
        ]);
    }

    public function testSaveUpdatesExistingComment(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertComment($uuid);

        $comment = new Comment(
            id: new Id($uuid),
            content: new CommentText('Оновлений коментар'),
            userId: new Id('user-uuid'),
            entityId: new Id('entity-uuid'),
            parentId: null,
            lft: 1,
            rgt: 2,
            depth: 0,
            entityType: EntityType::ARTICLE,
            status: CommentStatus::APPROVED,
        );

        $this->repository->save($comment);

        $this->assertDatabaseHas('comments', [
            'id'      => $uuid,
            'content' => 'Оновлений коментар',
            'status'  => CommentStatus::APPROVED->value,
        ]);
    }

    public function testDeleteRemovesComment(): void
    {
        $uuid = (string) new UuidV7();
        $this->insertComment($uuid);

        $this->repository->delete(new Id($uuid));

        $this->assertDatabaseMissing('comments', ['id' => $uuid]);
    }

    public function testGetAllReturnsPaginator(): void
    {
        $this->insertComment((string) new UuidV7(), lft: 1, rgt: 2);
        $this->insertComment((string) new UuidV7(), lft: 3, rgt: 4);

        $paginator = $this->repository->getAll();

        $this->assertEquals(2, $paginator->total());
        $this->assertInstanceOf(Comment::class, $paginator->items()[0]);
    }

    public function testGetAllReturnsEmptyWhenNoComments(): void
    {
        $paginator = $this->repository->getAll();

        $this->assertEquals(0, $paginator->total());
        $this->assertEmpty($paginator->items());
    }

    public function testGetMaxRgtReturnsMaxValue(): void
    {
        $entityId = 'entity-uuid';
        $this->insertComment((string) new UuidV7(), lft: 1, rgt: 2, entityId: $entityId);
        $this->insertComment((string) new UuidV7(), lft: 3, rgt: 4, entityId: $entityId);

        $result = $this->repository->getMaxRgt(new Id($entityId));

        $this->assertEquals(4, $result);
    }

    public function testGetMaxRgtReturnsNullWhenNoComments(): void
    {
        $result = $this->repository->getMaxRgt(new Id('entity-uuid'));

        $this->assertNull($result);
    }

    public function testGetMaxRgtReturnsOnlyForSpecificEntity(): void
    {
        $this->insertComment((string) new UuidV7(), lft: 1, rgt: 10, entityId: 'entity-1');
        $this->insertComment((string) new UuidV7(), lft: 1, rgt: 4, entityId: 'entity-2');

        $result = $this->repository->getMaxRgt(new Id('entity-2'));

        $this->assertEquals(4, $result);
    }

    public function testCreateChildCommentReturnsId(): void
    {
        $parentId = (string) new UuidV7();
        $this->insertComment($parentId, lft: 1, rgt: 2);

        $result = $this->repository->createChildComment([
            'parent_id'   => $parentId,
            'user_id'     => 'user-uuid',
            'content'     => 'Дочірній коментар',
        ]);

        $this->assertNotEmpty($result);
    }

    public function testCreateChildCommentInsertsWithCorrectDepth(): void
    {
        $parentId = (string) new UuidV7();
        $this->insertComment($parentId, lft: 1, rgt: 2, depth: 0);

        $childId = $this->repository->createChildComment([
            'parent_id' => $parentId,
            'user_id'   => 'user-uuid',
            'content'   => 'Дочірній коментар',
        ]);

        $this->assertDatabaseHas('comments', [
            'id'    => $childId,
            'depth' => 1,
        ]);
    }

    public function testCreateChildCommentUpdatesParentRgt(): void
    {
        $parentId = (string) new UuidV7();
        $this->insertComment($parentId, lft: 1, rgt: 2);

        $this->repository->createChildComment([
            'parent_id' => $parentId,
            'user_id'   => 'user-uuid',
            'content'   => 'Дочірній коментар',
        ]);

        $parent = DB::table('comments')->find($parentId);
        $this->assertEquals(4, $parent->rgt);
    }

    public function testCreateChildCommentSetsCorrectLftRgt(): void
    {
        $parentId = (string) new UuidV7();
        $this->insertComment($parentId, lft: 1, rgt: 2);

        $childId = $this->repository->createChildComment([
            'parent_id' => $parentId,
            'user_id'   => 'user-uuid',
            'content'   => 'Дочірній коментар',
        ]);

        $this->assertDatabaseHas('comments', [
            'id'  => $childId,
            'lft' => 2,
            'rgt' => 3,
        ]);
    }

    public function testCreateChildCommentSetsParentId(): void
    {
        $parentId = (string) new UuidV7();
        $this->insertComment($parentId, lft: 1, rgt: 2);

        $childId = $this->repository->createChildComment([
            'parent_id' => $parentId,
            'user_id'   => 'user-uuid',
            'content'   => 'Дочірній коментар',
        ]);

        $this->assertDatabaseHas('comments', [
            'id'        => $childId,
            'parent_id' => $parentId,
        ]);
    }

    public function testCreateChildCommentShiftsRightSiblings(): void
    {
        $entityId = 'entity-uuid';
        $parentId = (string) new UuidV7();
        $siblingId = (string) new UuidV7();

        $this->insertComment($parentId, lft: 1, rgt: 2, entityId: $entityId);
        $this->insertComment($siblingId, lft: 3, rgt: 4, entityId: $entityId);

        $this->repository->createChildComment([
            'parent_id' => $parentId,
            'user_id'   => 'user-uuid',
            'content'   => 'Дочірній коментар',
        ]);

        $sibling = DB::table('comments')->find($siblingId);
        $this->assertEquals(5, $sibling->lft);
        $this->assertEquals(6, $sibling->rgt);
    }
}

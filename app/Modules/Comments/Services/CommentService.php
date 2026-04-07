<?php

namespace Modules\Comments\Services;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Comments\Entities\Comment;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\ValueObjects\CommentText;
use Modules\Comments\Repositories\Contracts\CommentRepositoryInterface;

class CommentService
{
    public function __construct(
        private CommentRepositoryInterface $repository
    )
    {
    }


    public function getCommentById(Id $id): ?Comment
    {
        return $this->repository->get($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->repository->getAll();
    }

    public function create(array $data): string
    {
        $comment = new Comment(
            content: new CommentText($data['content']),
            userId: new Id($data['user_id']),
            entityId: new Id($data['entity_id']),
            entityType:  EntityType::from($data['entity_type'])
        );

        $commentId = $this->repository->save($comment);

        return $commentId;
    }

//    public function syncTags(Id $commentId, array $tagIds): void
//    {
//        $this->repository->syncTags(
//            new Id($articleId),
//            $tagIds
//        );
//    }

    public function update(Id $id, array $data): void
    {
        $comment = $this->repository->get($id);

        $comment = new Comment(
            id: $comment->id,
            userId: $comment->userId,
            entityId: $comment->entityId,
            entityType: $comment->entityType,
            content: array_key_exists('content', $data) ? new CommentText($data['content']) : $article->content,
            status: array_key_exists('status', $data) ? CommentStatus::from($data['status']) : $comment->status,
            created_at: $comment->created_at,
        );

        $this->repository->save($comment);
    }

    public function delete(Id $id): void
    {
        $this->repository->delete($id);
    }
}

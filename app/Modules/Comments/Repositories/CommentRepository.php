<?php

namespace Modules\Comments\Repositories;

use App\ValueObjects\Id;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Comments\Entities\Comment;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\Repositories\Contracts\CommentRepositoryInterface;
use Modules\Comments\ValueObjects\CommentText;

use Symfony\Component\Uid\UuidV7;

class CommentRepository implements CommentRepositoryInterface
{
    private CONST TABLE_NAME = 'comments';

    public function get(Id $commentId): ?Comment
    {
        $comment =  DB::table(self::TABLE_NAME)->find($commentId);

        if(null === $comment) {
           return null;
        }

        return new Comment(
            id: new Id($comment->id),
            status: CommentStatus::from($comment->status),
            entityType: EntityType::from($comment->entity_type),
            userId: new Id($comment->user_id),
            entityId: new Id($comment->entity_id),
            content: new CommentText($comment->content),
            created_at: new \DateTimeImmutable($comment->created_at),
            updated_at: new \DateTimeImmutable($comment->updated_at),
        );

    }
    public function save(Comment $comment): string
    {
        if ($comment->id === null) {
            $id = new UuidV7();
            DB::table(self::TABLE_NAME)->insert([
                'id' =>  $id,
                'status'  => $comment->status->value,
                'content'  => $comment->content->getValue(),
                'entity_type'  => $comment->entityType->value,
                'user_id'  => $comment->userId->getValue(),
                'entity_id'  => $comment->entityId->getValue(),
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return $id->toString();
        } else {
            DB::table(self::TABLE_NAME)->where('id', $comment->id->getValue())->update([
                'status'  => $comment->status->value,
                'content'  => $comment->content->getValue(),
                'updated_at' => new \DateTimeImmutable(),
            ]);
            return $comment->id->getValue();
        }
    }
    public function delete(Id $id): void
    {
        DB::table(self::TABLE_NAME)->delete($id);
    }

    public function getAll(): LengthAwarePaginator
    {
        $paginator =  DB::table(self::TABLE_NAME)->orderBy('created_at')->paginate(10);
//        $commentIds = array_column($paginator->items(), 'id');

        $collection = new Collection();

        foreach ($paginator->items() as $comment) {
            $commentEntity = new Comment(
                id: new Id($comment->id),
                status: CommentStatus::from($comment->status),
                entityType: EntityType::from($comment->entity_type),
                userId: new Id($comment->user_id),
                entityId: new Id($comment->entity_id),
                content: new CommentText($comment->content),
                created_at: new \DateTimeImmutable($comment->created_at),
                updated_at: new \DateTimeImmutable($comment->updated_at),

            );
            $collection->push($commentEntity);
        }

        $paginator->setCollection($collection);

        return $paginator;
    }
}

<?php

namespace Modules\Comments\Repositories\Contracts;

use App\ValueObjects\Id;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Comments\Entities\Comment;

interface CommentRepositoryInterface
{
    public function getAll(): LengthAwarePaginator;
    public function get(Id $commentId): ?Comment;
    public function save(Comment $comment): string;
    public function delete(Id $id): void;

    public function createChildComment(array $data): string;

    public function getMaxRgt(Id $entityId): ?int;
}

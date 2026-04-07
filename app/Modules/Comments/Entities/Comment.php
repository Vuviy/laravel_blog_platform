<?php

namespace Modules\Comments\Entities;

use App\ValueObjects\Id;
use Modules\Comments\Enums\CommentStatus;
use Modules\Comments\Enums\EntityType;
use Modules\Comments\ValueObjects\CommentText;
use Modules\Users\Entities\User;
use Modules\Users\Repositories\UserRepository;
use Modules\Users\Services\UserService;

final class Comment
{
    public function __construct(
        public Id                  $userId,
        public CommentText         $content,
        public EntityType          $entityType,
        public Id                  $entityId,
        public CommentStatus       $status = CommentStatus::PENDING,
        public ?Id                 $id = null,
        public ?\DateTimeImmutable $created_at = null,
        public ?\DateTimeImmutable $updated_at = null,
    )
    {
    }


    public function getUser(): ?User
    {
        $userRepo = new UserRepository();
        return $userRepo->getById($this->userId);
    }
}
